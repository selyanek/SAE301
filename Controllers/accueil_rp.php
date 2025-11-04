<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil RP</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Vous êtes RP</h1>

    <!-- Tableau pour afficher la liste des documents uploadés -->
    <table>
        <thead>
            <tr>
                <th scope='col'>Étudiant</th>
                <th scope='col'>Justification</th>
                <th scope='col'>Document</th>
            </tr>
        </thead>

        <tbody>
            <?php
                $folder = "../uploads/";
                // Récupération des fichiers du dossier avec les extensions spécifiées (.txt, .pdf, .jpg), sans récursion
                $files = $this->get_files($folder, [".txt", ".pdf", ".jpg"], false);
                if (count($files) > 0) {
                    // si fichier trouvé boucle pour afficher chaque fichier dans une ligne du tableau
                    foreach ($files as $file) {
                        echo "<tr>";
                        // Affichage du nom de l'étudiant basé sur le nom du fichier (sans extension)
                        echo "<td>Étudiant " . htmlspecialchars(basename($file, pathinfo($file, PATHINFO_EXTENSION))) . "</td>";
                        echo "<td>Justification d'absence</td>";
                        echo "<td><a href='" . htmlspecialchars($file) . "' target='_blank'>Voir le document</a></td>";
                        echo "</tr>";
                    }
                } else {
                    // Message si aucun document n'est trouvé
                    echo "<tr><td colspan='3'>Aucun document trouvé</td></tr>";
                }
            ?>
        </tbody>
    </table>
</body>
</html>
