import matplotlib.pyplot as plt
import psycopg
import datetime
import os

# Créer le dossier de sortie s'il n'existe pas
output_dir = os.path.join(os.path.dirname(__file__), 'public', 'asset', 'stats')
os.makedirs(output_dir, exist_ok=True)

# TODO importer données bdd avec psycopg

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
    
    # la liste str est censée contenir 3 éléments : les noms des 3 élèves ayant le plus d'absences
    # la liste nb est censée contenir 3 éléments : le nombre d'absences de ces 3 étudiant

    couleurs = ["#996147", "#8A9796", "#E2B128"]

    plt.bar(etu, nb, color=couleurs)

    plt.title('Top 3 des absents')
    plt.xlabel("Nom de l'étudiant", fontweight ='bold', fontsize = 15)
    plt.ylabel("Nombre d'absences", fontweight ='bold', fontsize = 15)

    # Sauvegarder l'image sous format png pour pouvoir l'afficher ultérieurement sur le site

    plt.savefig(os.path.join(output_dir, "absences4.png"))
    plt.close()
    
if __name__ == "__main__":
    repartition_absences_par_cours([200,300,400,500,1000])
    repartition_absences_par_heure([200,300,400,500,600,700])
    absences_14_derniers_jours([1,2,5,3,6,15,1,5,1,4,1,3,10,11])
    top_3(["Baby Rigger", "Gabagoo Diggeldoo", "Booga Wakaboomboom"], [30,52,84])


