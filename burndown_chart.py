import matplotlib.pyplot as plt
import numpy as np

# BURNDOWN CHART
# Auteur : selyanek

# Ce graphique représente l'avancement idéal et réel du projet en termes de story points au fil des sprints.
# Les valeurs initiales sont basées sur une estimation de 217 story points à répartir sur 4 sprints.
# Cela pourra être modifié en fonction de notre avancement

if __name__ == '__main__':

    ideal = [217 * (4 - i) / 4 for i in range(0, 5)]
    real = [217,217,217,217,217]
    sprint = [0,1,2,3,4]

    plt.plot(sprint, ideal, label='Avancement idéal', color='red', linestyle='--')
    plt.plot(sprint, real, label='Avancement réel', color='blue', linestyle='--')

    plt.xlabel('Sprint')
    plt.ylabel('Story Points')
    plt.legend()

    # Forcer des ticks de 1 en 1 sur l'axe X
    plt.xticks(range(min(sprint), max(sprint) + 1, 1))




    plt.title('Burndown chart pour le projet')

    plt.show()
