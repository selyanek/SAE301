<?php
// Paramètres de connexion à la base de données
$host = 'node2.liruz.fr';
$port = '5435';
$dbname = 'sae';
$username = 'sae';
$password = '1zevkN&49b&&a*Pi97C';

try {
    // Création de la connexion PDO
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "Connexion à la base de données réussie !";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupération des données du formulaire
        $etudiant_id = $_POST['etudiant_id'];
        $date_absence = $_POST['date_absence'];
        $justificatif = $_POST['justificatif']; // téléchargement de fichiers à gérer

        // Appel de la fonction pour ajouter l'absence
        $absence_id = ajouterAbsence($pdo, $etudiant_id, $date_absence, $justificatif);
        echo "Absence ajoutée avec l'ID : " . $absence_id;
    } else {
        echo "Méthode de requête non prise en charge.";
    }
    
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

function ajouterAbsence($pdo, $etudiant_id, $date_absence, $justificatif) {
    $sql = "INSERT INTO absences (etudiant_id, date_absence, justificatif) VALUES (:etudiant_id, :date_absence, :justificatif)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':etudiant_id' => $etudiant_id,
        ':date_absence' => $date_absence,
        ':justificatif' => $justificatif
    ]);
    return $pdo->lastInsertId();
}



$connections = "host=localhost port=5435 dbname=sae user=sae password=1zevkN&49b&&a*Pi97C";
$connection = pg_connect($connections);
if (!$connection) {
    echo "Une erreur s'est produite.\n";
    exit;
} else {
    echo "Connexion réussie.\n";
}
// Fermeture de la connexion
$pdo = null;
?>