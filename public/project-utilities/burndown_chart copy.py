import matplotlib.pyplot as plt
import numpy as np

# BURNDOWN CHART
# pourra être modifié en fonction de notre avancement

if __name__ == '__main__':

    ideal = [89 * (7 - i) / 7 for i in range(0, 7)]
    real = [88, 80, 62, 48, 40, 40, 0]
    heure = [0,4,8,12,16,20,24]

    plt.plot(heure, ideal, label='Avancement idéal', color='red', linestyle='--')
    plt.plot(heure, real, label='Avancement réel', color='blue', linestyle='--')

    plt.xlabel('Heures')
    plt.ylabel('Story Points')
    plt.legend()

    # Forcer des ticks de 1 en 1 sur l'axe X
    plt.xticks(range(min(heure), max(heure) + 1, 1))




    plt.title('Burndown chart pour le projet (Sprint 4)')

    plt.show()
