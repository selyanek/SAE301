import matplotlib.pyplot as plt
import psycopg
from datetime import datetime, date, timedelta
import os

# Créer le dossier de sortie s'il n'existe pas
output_dir = os.path.join(os.path.dirname(__file__), 'public', 'asset', 'stats')
os.makedirs(output_dir, exist_ok=True)


def get_connection():
    """Retourne une connexion psycopg configurée ou lève une exception."""
    conn_string = "host='node2.liruz.fr' dbname='sae' user='sae' password='1zevkN&49b&&a*Pi97C'"
    return psycopg.connect(conn_string)


def collect_data_absences_par_cours() -> list[int]:
    """Retourne la liste des comptes d'absences pour les types [CM, TD, TP, DS, BEN]."""
    types = ['CM', 'TD', 'TP', 'DS', 'BEN']
    counts = {t: 0 for t in types}
    try:
        with get_connection() as conn:
            with conn.cursor() as cur:
                cur.execute(
                    """
                    SELECT C.type, COUNT(*)
                    FROM Absence A
                    JOIN Cours C ON A.idCours = C.idCours
                    GROUP BY C.type
                    """
                )
                for row in cur.fetchall():
                    t, c = row[0], row[1]
                    if t in counts:
                        counts[t] = int(c)
    except Exception as e:
        print('Erreur collect_data_absences_par_cours:', e)

    return [counts[t] for t in types]


def collect_data_absences_par_heure() -> list[int]:
    """Retourne le nombre d'absences par tranche horaire définie.

    Tranches (minutes depuis minuit):
    8:00-9:30   -> 480-570
    9:30-11:00  -> 570-660
    11:00-12:30 -> 660-750
    14:00-15:30 -> 840-930
    15:30-17:00 -> 930-1020
    17:00-18:30 -> 1020-1110
    """
    slots = [(480, 570), (570, 660), (660, 750), (840, 930), (930, 1020), (1020, 1110)]
    counts = [0] * len(slots)
    try:
        with get_connection() as conn:
            with conn.cursor() as cur:
                cur.execute("SELECT date_debut FROM Absence WHERE date_debut IS NOT NULL")
                for (dt,) in cur.fetchall():
                    # dt peut être un datetime ou une string
                    if isinstance(dt, str):
                        try:
                            dt = datetime.fromisoformat(dt)
                        except Exception:
                            continue
                    minutes = dt.hour * 60 + dt.minute
                    for i, (start, end) in enumerate(slots):
                        if start <= minutes < end:
                            counts[i] += 1
                            break
    except Exception as e:
        print('Erreur collect_data_absences_par_heure:', e)

    return counts


def collect_data_absences_14_derniers_jours() -> list[int]:
    """Retourne un tableau de 14 entiers correspondant aux 14 derniers jours (de -13 à 0).
    L'ordre renvoyé est du plus ancien au plus récent.
    """
    today = date.today()
    days = [(today - timedelta(days=i)) for i in range(13, -1, -1)]
    counts_map = {d: 0 for d in days}
    try:
        with get_connection() as conn:
            with conn.cursor() as cur:
                cur.execute(
                    """
                    SELECT DATE(date_debut) AS d, COUNT(*)
                    FROM Absence
                    WHERE date_debut >= %s
                    GROUP BY d
                    """,
                    (days[0],),
                )
                for d, c in cur.fetchall():
                    if isinstance(d, date):
                        if d in counts_map:
                            counts_map[d] = int(c)
    except Exception as e:
        print('Erreur collect_data_absences_14_derniers_jours:', e)

    return [counts_map[d] for d in days]


def collect_top3_etudiants() -> tuple[list[str], list[int]]:
    """Retourne (noms, comptes) des 3 étudiants ayant le plus d'absences."""
    names = []
    counts = []
    try:
        with get_connection() as conn:
            with conn.cursor() as cur:
                cur.execute(
                    """
                    SELECT co.nom || ' ' || co.prenom AS name, COUNT(*) AS c
                    FROM Absence a
                    JOIN Etudiant e ON a.idEtudiant = e.idEtudiant
                    JOIN Compte co ON e.idEtudiant = co.idCompte
                    GROUP BY name
                    ORDER BY c DESC
                    LIMIT 3
                    """
                )
                for name, c in cur.fetchall():
                    names.append(name)
                    counts.append(int(c))
    except Exception as e:
        print('Erreur collect_top3_etudiants:', e)

    # Si moins de 3 résultats, compléter avec valeurs vides/0
    while len(names) < 3:
        names.append('')
        counts.append(0)

    return names, counts


def get_nearest_friday(ref: date | None = None) -> date:
    """Retourne la date du vendredi la plus proche de la date `ref`.

    Si `ref` est None, utilise la date d'aujourd'hui. En cas d'égalité
    (même distance avant/après), choisit le vendredi le plus proche dans le passé.
    """
    if ref is None:
        ref = date.today()
    wd = ref.weekday()  # lundi=0 .. dimanche=6 ; vendredi=4
    # distance vers le vendredi précédent et suivant
    days_back = (wd - 4) % 7
    days_forward = (4 - wd) % 7
    # choisir le plus proche (préférer le passé si égal)
    if days_back <= days_forward:
        return ref - timedelta(days=days_back)
    return ref + timedelta(days=days_forward)


def collect_data_absences_par_mois(num_weeks: int = 4, ref: date | None = None) -> list[int]:
    """Retourne une liste de `num_weeks` entiers : nombre d'absences par semaine.

    Les semaines sont définies comme des intervalles de 7 jours se terminant le vendredi
    le plus proche de `ref` (inclus). La liste est ordonnée du plus ancien au plus récent.
    """
    if num_weeks < 1:
        return []
    end_friday = get_nearest_friday(ref)
    weeks = []
    # construire intervalles [start, end] pour chaque semaine
    intervals = []
    for i in range(num_weeks - 1, -1, -1):
        week_end = end_friday - timedelta(weeks=i)
        week_start = week_end - timedelta(days=6)
        intervals.append((week_start, week_end))

    counts = [0] * len(intervals)
    try:
        with get_connection() as conn:
            with conn.cursor() as cur:
                for idx, (start, end) in enumerate(intervals):
                    # compter les absences dont la date_debut se situe entre start 00:00:00 et end 23:59:59
                    cur.execute(
                        """
                        SELECT COUNT(*) FROM Absence
                        WHERE date_debut >= %s AND date_debut <= %s
                        """,
                        (datetime.combine(start, datetime.min.time()),
                         datetime.combine(end, datetime.max.time())),
                    )
                    row = cur.fetchone()
                    counts[idx] = int(row[0]) if row and row[0] is not None else 0
    except Exception as e:
        print('Erreur collect_data_absences_par_semaine:', e)

    return counts

def collect_data_absences_par_semaine(last_day : date) -> list[int]:
    assert(date.weekday() == 5, "La date choisie n'est pas un vendredi : veuillez choisir un vendredi")


    """Retourne un tableau de 5 entiers correspondant aux jours de la semaine, de lundi à vendredi
    L'ordre renvoyé est du plus ancien au plus récent.
    """
    today = date.today()
    days = [(today - timedelta(days=i)) for i in range(4, -1, -1)]
    counts_map = {d: 0 for d in days}
    try:
        with get_connection() as conn:
            with conn.cursor() as cur:
                cur.execute(
                    """
                    SELECT DATE(date_debut) AS d, COUNT(*)
                    FROM Absence
                    WHERE date_debut >= %s
                    GROUP BY d
                    """,
                    (days[0],),
                )
                for d, c in cur.fetchall():
                    if isinstance(d, date):
                        if d in counts_map:
                            counts_map[d] = int(c)
    except Exception as e:
        print('Erreur collect_data_absences_par_semaine:', e)

    return [counts_map[d] for d in days]


def repartition_absences_par_cours(nb : list[int]):
    
    # la liste nb est censée contenir 5 éléments : le nombre d'absences aux CM, TD, TP, DS et SAE respectivement
    
    cours = ['CM', 'TD', 'TP', 'DS', 'BEN']
    couleurs = ["#52BB7E", "#5EE9E0", "#5834DA", "#DD5A0E", "#9B9217"]

    plt.bar(cours, nb, color=couleurs)

    plt.title('Répartition des absences en fonction du type de cours')
    plt.xlabel('Type de cours', fontweight ='bold', fontsize = 15)
    plt.ylabel("Nombre d'absences", fontweight ='bold', fontsize = 15)

    # Sauvegarder l'image sous format png pour pouvoir l'afficher ultérieurement sur le site

    plt.savefig(os.path.join(output_dir, "absences.png"))
    plt.close()


def repartition_absences_par_heure(nb : list[int]):
    
    # la liste nb est censée contenir 6 éléments : le nombre d'absences aux heures correspondantes
    
    cours = ['8h-9h30', '9h30-11h', '11h-12h30', '14h-15h30', '15h30-17h', '17h-18h30']
    couleurs = ["#001BB4", "#006DB6", "#0090D3", "#00CAEE","#00F0E4", "#00FFB3"]

    plt.bar(cours, nb, color=couleurs)

    plt.title("Répartition des absences en fonction de l'heure")
    plt.xlabel('Heure de la journée', fontweight ='bold', fontsize = 15)
    plt.ylabel("Nombre d'absences", fontweight ='bold', fontsize = 15)

    # Sauvegarder l'image sous format png pour pouvoir l'afficher ultérieurement sur le site
    
    plt.savefig(os.path.join(output_dir, "absences2.png"))
    plt.close()

def absences_14_derniers_jours(nb : list[int]):
    
    # la liste nb est censée contenir 14 éléments : le nombre d'absences / jour les 14 derniers jours

    plt.plot(nb, color = 'black', linestyle = '-')

    plt.title("Récapitulatif d'absences sur les 14 derniers jours")
    plt.xlabel('Jour', fontweight ='bold', fontsize = 15)
    plt.ylabel("Nombre d'absences", fontweight ='bold', fontsize = 15)

    # Sauvegarder l'image sous format png pour pouvoir l'afficher ultérieurement sur le site

    
    plt.grid(True)
    
    plt.savefig(os.path.join(output_dir, "absences3.png"))
    plt.close()


def top_3(etu : str, nb : list[int]):
    
    # la liste str est censée contenir 3 éléments : les noms des 3 élèves ayant le plus d'absences (3ème, 2ème, 1er)
    # la liste nb est censée contenir 3 éléments : le nombre d'absences de ces 3 étudiants

    couleurs = ["#996147", "#8A9796", "#E2B128"]

    plt.bar(etu, nb, color=couleurs)

    plt.title('Top 3 des absents')
    plt.xlabel("Nom de l'étudiant", fontweight ='bold', fontsize = 15)
    plt.ylabel("Nombre d'absences", fontweight ='bold', fontsize = 15)

    # Sauvegarder l'image sous format png pour pouvoir l'afficher ultérieurement sur le site

    plt.savefig(os.path.join(output_dir, "absences4.png"))
    plt.close()

def absences_mois(nb : list[int]):
    
    # la liste nb est censée contenir 4 éléments : le nombre d'absences / jour les 4 dernieres semaines

    plt.plot(nb, color = 'purple', linestyle = '-')

    plt.title("Récapitulatif d'absences sur le mois")
    plt.xlabel('Semaine', fontweight ='bold', fontsize = 15)
    plt.ylabel("Nombre d'absences", fontweight ='bold', fontsize = 15)

    # Sauvegarder l'image sous format png pour pouvoir l'afficher ultérieurement sur le site

    
    plt.grid(True)
    
    plt.savefig(os.path.join(output_dir, "absences5.png"))
    plt.close()
    
if __name__ == "__main__":
    # Collecter les données depuis la base et générer les graphiques
    try:
        data_cours = collect_data_absences_par_cours()
        repartition_absences_par_cours(data_cours)

        data_heures = collect_data_absences_par_heure()
        repartition_absences_par_heure(data_heures)

        data_14j = collect_data_absences_14_derniers_jours()
        absences_14_derniers_jours(data_14j)

        friday : date = get_nearest_friday()

        data_semaine = collect_data_absences_par_mois(friday)
        absences_mois(friday)


        names, counts = collect_top3_etudiants()
        top_3(names, counts)
    except Exception as e:
        print('Erreur lors de la génération des graphiques :', e)
