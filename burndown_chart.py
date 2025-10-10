import matplotlib.pyplot as plt
import numpy as np

# BURNDOWN CHART
# pourra être modifié en fonction de notre avancement

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
