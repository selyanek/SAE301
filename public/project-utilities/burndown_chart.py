import matplotlib.pyplot as plt
import numpy as np

# BURNDOWN CHART
# pourra être modifié en fonction de notre avancement

if __name__ == '__main__':

    ideal = [146 - (146 / 6 * i) for i in range(7)]
    real = [146, 120, 100, 80, 60, 30, 5]
    heure = [0,4,8,12,16,20,24]

    plt.plot(heure, ideal, label='Avancement idéal', color='red', linestyle='--')
    plt.plot(heure, real, label='Avancement réel', color='blue', linestyle='--')

    plt.xlabel('Heures')
    plt.ylabel('Story Points')
    plt.legend()

    # Forcer des ticks de 1 en 1 sur l'axe X
    plt.xticks(range(min(heure), max(heure) + 1, 1))




    plt.title('Burndown chart pour le projet (Sprint S4-2)')

    plt.show()
