import matplotlib.pyplot as plt
import numpy as np

# BURNDOWN CHART
# pourra être modifié en fonction de notre avancement

if __name__ == '__main__':

    ideal = [113 * (1 - i / 22) for i in range(23)] # 22 jours dans un sprint de 4 semaines, du 10 septembre au 10 octobre
    real = [113, 113, 110, 98, 75, 62, 54, 52, 50, 48, 48, 45, 44, 43, 42, 42, 41, 41, 41, 40, 27, 27, 25]
    jours = [i for i in range(23)]

    plt.plot(jours, ideal, label='Avancement idéal', color='red', linestyle='--')
    plt.plot(jours, real, label='Avancement réel', color='blue', linestyle='--')

    plt.xlabel('Jours')
    plt.ylabel('Story Points')
    plt.legend()

    # Forcer des ticks de 1 en 1 sur l'axe X
    plt.xticks(range(min(jours), max(jours) + 1, 1))




    plt.title('Burndown chart pour le sprint 1')

    plt.show()
