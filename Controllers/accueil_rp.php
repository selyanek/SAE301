<?php 
    
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Titre de la page -->
    <title>Accueil RP</title>
    
    <!-- Styles CSS intégrés pour le tableau -->
    <style>
        /* Style du tableau : suppression des espaces entre bordures */
        table {
            border-collapse: collapse;
            width: 100%;
        }
        
        /* Style des cellules : bordures, espacement et alignement */
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        /* Style de l'en-tête du tableau : fond gris clair */
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <!-- En-tête de la page -->
    <h1>Vous êtes RP</h1>

    <!-- Tableau de gestion des justificatifs d'absence -->
    <table>
        <!-- En-tête du tableau avec les colonnes -->
        <thead>
            <tr>
                <th scope='col'>Étudiant</th>
                <th scope='col'>Justification</th>
                <th scope='col'>Document</th>
            </tr>
        </thead>

        <!-- liste des justificatifs -->
        <tbody>
            <?php 
                // Définition du dossier contenant les fichiers uploadés
                $folder = "../uploads/";
                
                // Récupération de tous les fichiers avec les extensions autorisées
                $files = $this->get_files($folder, [".txt", ".pdf", ".jpg"], false);
                
                // Vérification : y a-t-il des fichiers trouvés ?
                if (count($files) > 0) {
                    // Boucle : parcours de chaque fichier trouvé
                    foreach ($files as $file) {
                        echo "<tr>";
                        
                        // Affichage du nom de l'étudiant
                        echo "<td>Étudiant " . htmlspecialchars(basename($file, pathinfo($file, PATHINFO_EXTENSION))) . "</td>";
                        
                        // Affichage du type de justification
                        echo "<td>Justification d'absence</td>";
                        
                        // Lien pour visualiser le document dans un nouvel onglet
                        echo "<td><a href='" . htmlspecialchars($file) . "' target='_blank'>Voir le document</a></td>";
                        
                        echo "</tr>";
                    }
                } else {
                    // Message si aucun document n'a été trouvé
                    echo "<tr><td colspan='3'>Aucun document trouvé</td></tr>";
                }
            ?>
        </tbody>
    </table>
</body>
</html>