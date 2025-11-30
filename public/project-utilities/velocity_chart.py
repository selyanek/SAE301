# ...existing code...
import matplotlib.pyplot as plt

if __name__ == "__main__":

    # ...existing code...
    # données et style pour reproduire exactement le graphique fourni
    sprints = list(range(1, 5))
    pred_x = [1, 2, 3]
    pred_y = [112, 115, 155]
    res_x = [2, 3]
    res_y = [14, 119]

    fig, ax = plt.subplots(figsize=(10, 8))

    # barres Prédictions (bleu) et Résultats (vert)
    bar_width = 0.6
    ax.bar(pred_x, pred_y, width=bar_width, color='#2f52ff', label='Prédictions',
           edgecolor='black', linewidth=0.8, zorder=4)
    ax.bar(res_x, res_y, width=bar_width * 0.6, color='#3aa23a', label='Résultats',
           edgecolor='black', linewidth=0.8, zorder=5)

    # axes, limites et ticks
    ax.set_xlim(0.5, 8.5)
    ax.set_xticks(sprints)
    ax.set_xticklabels(sprints, fontsize=12)
    ax.set_ylim(0, 160)
    ax.set_yticks(list(range(0, 161, 20)))
    ax.tick_params(axis='y', labelsize=12)

    # titres et labels (français)
    ax.set_title("Velocity chart pour le projet (Prédictions vs Résultats)", fontsize=20)
    ax.set_xlabel("Sprint", fontsize=14)
    ax.set_ylabel("Vélocité (Story Points)", fontsize=14)

    # grille horizontale légère derrière les barres
    ax.grid(axis='y', color='0.85', linewidth=1)
    ax.set_axisbelow(True)

    # style des spines et cadre
    for spine in ax.spines.values():
        spine.set_linewidth(1.0)
        spine.set_color('black')

    # légende dans le coin supérieur droit avec fond blanc et bord
    leg = ax.legend(loc='upper right', fontsize=12, framealpha=1.0, fancybox=True, shadow=True)
    leg.get_frame().set_facecolor('white')
    leg.get_frame().set_edgecolor('#bfbfbf')

    plt.tight_layout()
    # ...existing code...
    plt.show()