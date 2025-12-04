<?php
session_start();

// Vérifier que l'utilisateur est connecté et a le rôle de professeur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professeur') {
    header('Location: /public/index.php');
    exit();
}

// Vérifier que la requête est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /src/Views/rattrapage_prof.php?error=' . urlencode('Méthode non autorisée'));
    exit();
}

// Connexion à la base de données
require __DIR__ . '/../../vendor/autoload.php'; // Charger l'autoloader de Composer pour PHPMailer
require __DIR__ . '/../Database/Database.php';
require __DIR__ . '/../Models/EmailService.php';

$db = new \src\Database\Database();
$pdo = $db->getConnection();

try {
    // Récupérer les données du formulaire
    $idAbsence = isset($_POST['idAbsence']) ? intval($_POST['idAbsence']) : null;
    $idRattrapage = isset($_POST['idRattrapage']) && $_POST['idRattrapage'] !== '' ? intval($_POST['idRattrapage']) : null;
    $dateRattrapage = isset($_POST['dateRattrapage']) && $_POST['dateRattrapage'] !== '' ? $_POST['dateRattrapage'] : null;
    $salle = isset($_POST['salle']) ? trim($_POST['salle']) : null;
    $remarque = isset($_POST['remarque']) ? trim($_POST['remarque']) : null;
    $statut = isset($_POST['statut']) ? $_POST['statut'] : 'a_planifier';

    // Validation
    if (!$idAbsence) {
        throw new Exception("ID d'absence manquant");
    }

    if (!$dateRattrapage) {
        throw new Exception("La date du rattrapage est obligatoire");
    }

    // Récupérer l'ID du professeur connecté
    $idProfesseur = $_SESSION['idCompte'] ?? null;
    if (!$idProfesseur) {
        throw new Exception("Professeur non identifié");
    }

    // Récupérer les informations complètes de l'absence, de l'étudiant et du cours
    $infoStmt = $pdo->prepare("
        SELECT 
            a.idabsence,
            e.identifiantetu,
            comp.nom AS etudiant_nom,
            comp.prenom AS etudiant_prenom,
            comp.identifiantcompte AS etudiant_email,
            r.nom AS ressource_nom,
            c.type AS cours_type,
            c.date_debut AS cours_date,
            prof.nom AS prof_nom,
            prof.prenom AS prof_prenom
        FROM Absence a
        JOIN Cours c ON a.idCours = c.idCours
        JOIN Etudiant e ON a.idEtudiant = e.idEtudiant
        JOIN Compte comp ON e.idEtudiant = comp.idCompte
        JOIN Ressource r ON c.idRessource = r.idRessource
        JOIN Professeur p ON c.idProfesseur = p.idProfesseur
        JOIN Compte prof ON p.idProfesseur = prof.idCompte
        WHERE a.idabsence = :idAbsence 
        AND c.idProfesseur = :idProfesseur
        AND c.evaluation = TRUE
    ");
    $infoStmt->execute([
        ':idAbsence' => $idAbsence,
        ':idProfesseur' => $idProfesseur
    ]);
    
    $info = $infoStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$info) {
        throw new Exception("Vous n'êtes pas autorisé à gérer ce rattrapage");
    }

    $isModification = false;
    
    if ($idRattrapage) {
        // Modification d'un rattrapage existant
        $isModification = true;
        $sql = "UPDATE Rattrapage 
                SET date_rattrapage = :dateRattrapage,
                    salle = :salle,
                    remarque = :remarque,
                    statut = :statut
                WHERE idrattrapage = :idRattrapage 
                AND idabsence = :idAbsence";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':dateRattrapage' => $dateRattrapage,
            ':salle' => $salle,
            ':remarque' => $remarque,
            ':statut' => $statut,
            ':idRattrapage' => $idRattrapage,
            ':idAbsence' => $idAbsence
        ]);

        if ($result) {
            $message = "Le rattrapage a été modifié avec succès";
        } else {
            throw new Exception("Erreur lors de la modification du rattrapage");
        }
    } else {
        // Création d'un nouveau rattrapage
        $checkRattrapageStmt = $pdo->prepare("SELECT idrattrapage FROM Rattrapage WHERE idabsence = :idAbsence");
        $checkRattrapageStmt->execute([':idAbsence' => $idAbsence]);
        
        if ($checkRattrapageStmt->fetch()) {
            throw new Exception("Un rattrapage existe déjà pour cette absence. Veuillez le modifier.");
        }

        $sql = "INSERT INTO Rattrapage (idabsence, date_rattrapage, salle, remarque, statut)
                VALUES (:idAbsence, :dateRattrapage, :salle, :remarque, :statut)";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':idAbsence' => $idAbsence,
            ':dateRattrapage' => $dateRattrapage,
            ':salle' => $salle,
            ':remarque' => $remarque,
            ':statut' => $statut
        ]);

        if ($result) {
            $message = "Le rattrapage a été planifié avec succès";
        } else {
            throw new Exception("Erreur lors de la planification du rattrapage");
        }
    }

    // Envoyer un email à l'étudiant
    try {
        $emailService = new \src\Models\EmailService();
        
        // Construire l'email de l'étudiant (identifiant@uphf.fr)
        $emailEtudiant = $info['etudiant_email'] . '@uphf.fr';
        
        // Formater la date du rattrapage
        $dateFormatee = date('d/m/Y à H:i', strtotime($dateRattrapage));
        
        // Déterminer le sujet et le contenu en fonction du statut
        switch($statut) {
            case 'planifie':
                $sujet = "Rattrapage planifié - " . $info['ressource_nom'];
                $action = $isModification ? "modifié" : "planifié";
                $couleur = "#17a2b8";
                break;
            case 'effectue':
                $sujet = "Rattrapage effectué - " . $info['ressource_nom'];
                $action = "marqué comme effectué";
                $couleur = "#28a745";
                break;
            default:
                $sujet = "Rattrapage à planifier - " . $info['ressource_nom'];
                $action = "créé";
                $couleur = "#ffc107";
        }
        
        // Corps de l'email en HTML
        $corpsEmail = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #29acc8 0%, #238ca3 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
                .content { background: #f7f9fb; padding: 30px; border-radius: 0 0 8px 8px; }
                .info-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid {$couleur}; }
                .label { font-weight: bold; color: #29acc8; }
                .value { margin-bottom: 10px; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
                .status { background: {$couleur}; color: white; padding: 8px 16px; border-radius: 20px; display: inline-block; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2 style='margin: 0;'>Notification de Rattrapage</h2>
                </div>
                <div class='content'>
                    <p>Bonjour {$info['etudiant_prenom']} {$info['etudiant_nom']},</p>
                    
                    <p>Votre rattrapage a été <strong>{$action}</strong> par {$info['prof_prenom']} {$info['prof_nom']}.</p>
                    
                    <div class='info-box'>
                        <p class='value'><span class='label'>Ressource :</span> {$info['ressource_nom']}</p>
                        <p class='value'><span class='label'>Type :</span> {$info['cours_type']}</p>
                        <p class='value'><span class='label'>Évaluation initiale :</span> " . date('d/m/Y', strtotime($info['cours_date'])) . "</p>
                        <p class='value'><span class='label'>Date du rattrapage :</span> {$dateFormatee}</p>
                        " . (!empty($salle) ? "<p class='value'><span class='label'>Salle :</span> {$salle}</p>" : "") . "
                        <p class='value'><span class='label'>Statut :</span> <span class='status'>" . strtoupper($statut) . "</span></p>
                        " . (!empty($remarque) ? "<p class='value'><span class='label'>Remarque :</span><br>{$remarque}</p>" : "") . "
                    </div>
                    
                    <p style='margin-top: 20px;'><strong>Veuillez vous présenter à l'heure indiquée.</strong></p>
                    
                    <div class='footer'>
                        <p>Ceci est un email automatique, merci de ne pas y répondre.</p>
                        <p>UPHF</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $emailService->envoyerEmail(
            $emailEtudiant,
            $sujet,
            $corpsEmail
        );
        
    } catch (Exception $emailError) {
        // Si l'email échoue, on log l'erreur mais on ne bloque pas le processus
        $message .= " (Email de notification non envoyé)";
    }

    // Redirection avec message de succès
    header('Location: /src/Views/rattrapage_prof.php?success=' . urlencode($message));
    exit();

} catch (Exception $e) {
    header('Location: /src/Views/rattrapage_prof.php?error=' . urlencode($e->getMessage()));
    exit();
}
