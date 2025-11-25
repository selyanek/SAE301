import matplotlib.pyplot as plt
import psycopg
import datetime



# TODO importer données bdd avec psycopg

def repartition_absences_par_cours(nb : list[int]):
    
    # la liste nb est censée contenir 4 éléments : le nombre d'absences aux CM, TD, TP et DS respectivement
    
    cours = ['CM', 'TD', 'TP', 'DS']
    couleurs = ["#52BB7E", "#5EE9E0", "#5834DA", "#DD5A0E"]

    plt.bar(cours, nb, color=couleurs)

    plt.title('Répartition des absences en fonction du type de cours')
    plt.xlabel('Type de cours', fontweight ='bold', fontsize = 15)
    plt.ylabel("Nombre d'absences", fontweight ='bold', fontsize = 15)

    # Sauvegarder l'image sous format png pour pouvoir l'afficher ultérieurement sur le site

    plt.savefig("absences.png")
    plt.show()


def repartition_absences_par_heure(nb : list[int]):
    
    # la liste nb est censée contenir 6 éléments : le nombre d'absences aux heures correspondantes
    
    cours = ['8h-9h30', '9h30-11h', '11h-12h30', '14h-15h30', '15h30-17h', '17h-18h30']
    couleurs = ["#001BB4", "#006DB6", "#0090D3", "#00CAEE","#00F0E4", "#00FFB3"]

    plt.bar(cours, nb, color=couleurs)

    plt.title("Répartition des absences en fonction de l'heure")
    plt.xlabel('Heure de la journée', fontweight ='bold', fontsize = 15)
    plt.ylabel("Nombre d'absences", fontweight ='bold', fontsize = 15)

    # Sauvegarder l'image sous format png pour pouvoir l'afficher ultérieurement sur le site
    
    plt.savefig("absences2.png")
    plt.show()

def absences_14_derniers_jours(nb : list[int]):
    
    # la liste nb est censée contenir 6 éléments : le nombre d'absences / jour les 14 derniers jours

    plt.plot(nb, color = 'black', linestyle = '-')

    plt.title("Récapitulatif d'absences sur les 14 derniers jours")
    plt.xlabel('Jour', fontweight ='bold', fontsize = 15)
    plt.ylabel("Nombre d'absences", fontweight ='bold', fontsize = 15)

    # Sauvegarder l'image sous format png pour pouvoir l'afficher ultérieurement sur le site

    
    plt.grid(True)
    
    plt.savefig("absences3.png")
    plt.show()
    
if __name__ == "__main__":
    repartition_absences_par_cours([200,300,400,500])
    repartition_absences_par_heure([200,300,400,500,600,700])
    absences_14_derniers_jours([1,2,5,3,6,15,1,5,1,4,1,3,10,11])


