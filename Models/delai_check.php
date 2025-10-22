<?php
// Fonction de Gestion des délais de justification (48h)

function creerCookieDelai($id_absence, $date_retour){
    $date_limite = strtotime($date_retour.' +48 hours');
    
    setcookie(
        'delai_absence_'.$id_absence,
        $date_limite,
        $date_limite,
        '/'
    );
}

function verifieDelai($id_absence){
    $nom_cookie = 'delai_absence_'.$id_absence;
    
    if(!isset($_COOKIE[$nom_cookie])){
        return verifierEnBDD($id_absence);
    }
    
    $date_limite = (int) $_COOKIE[$nom_cookie];
    $maintenant = time();
    $temps_restant = $date_limite - $maintenant;
    
    if($temps_restant <= 0){
        setcookie($nom_cookie, "", time() - 3600, '/');
        
        return [
            'valide' => false,
            'heures' => 0,
            'minutes' => 0,
            'message' => 'Le délai de 48h est dépassé'
        ];
    }
    
    $heures = floor($temps_restant / 3600);
    $minutes = floor(($temps_restant % 3600) / 60);
    
    return [
        'valide' => true,
        'heures' => $heures,
        'minutes' => $minutes,
        'message' => "Il vous reste $heures h et $minutes min pour justifier votre absence"
    ];
}

function verifierEnBDD($id_absence){
    require_once __DIR__.'/Database.php';
    
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Vérifier si la colonne verrouille existe
    $checkColumn = $pdo->query("SELECT column_name 
                               FROM information_schema.columns 
                               WHERE table_name='absence' 
                               AND column_name='verrouille'");
    
    if ($checkColumn->fetch()) {
        $stmt = $pdo->prepare("SELECT date_fin, verrouille FROM Absence WHERE idAbsence = ?");
    } else {
        $stmt = $pdo->prepare("SELECT date_fin FROM Absence WHERE idAbsence = ?");
    }
    
    $stmt->execute([$id_absence]);
    $absence = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$absence){
        return [
            'valide' => false,
            'heures' => 0,
            'minutes' => 0,
            'message' => 'Absence introuvable'
        ];
    }
    
    if (isset($absence['verrouille']) && $absence['verrouille'] == true){
        return [
            'valide' => false,
            'heures' => 0,
            'minutes' => 0,
            'message' => 'Cette absence est verrouillée'
        ];
    }
    
    $date_limite = strtotime($absence['date_fin'].' +48 hours');
    $maintenant = time();
    $temps_restant = $date_limite - $maintenant;
    
    if ($temps_restant <= 0){
        return [
            'valide' => false,
            'heures' => 0,
            'minutes' => 0,
            'message' => 'Le délai de 48h est dépassé.'
        ];
    }
    
    creerCookieDelai($id_absence, $absence['date_fin']);
    
    $heures = floor($temps_restant / 3600);
    $minutes = floor(($temps_restant % 3600) / 60);
    
    return [
        'valide' => true,
        'heures' => $heures,
        'minutes' => $minutes,
        'message' => "Il vous reste $heures h et $minutes min pour justifier votre absence"
    ];
}

// Les couleurs peuvent etre changer si nécessaire
function afficherAlerte($resultat) {
    if ($resultat['valide']) {
        $couleur = 'background: #fff3cd; border: 2px solid #ffc107; color: #856404;';
    } else {
        $couleur = 'background: #f8d7da; border: 2px solid #dc3545; color: #721c24;';
    }
    
    echo '<div style="' . $couleur . ' padding: 15px; border-radius: 8px; margin: 20px auto; text-align: center; font-weight: bold; max-width: 800px;">';
    echo htmlspecialchars($resultat['message']);
    echo '</div>';
}

function verrouillerAbsences() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (isset($_SESSION['derniere_verif']) && $_SESSION['derniere_verif'] > time() - 3600) {
        return;
    }
    
    require_once __DIR__ . '/Database.php';
    
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Vérifier si la colonne verrouille existe
    $checkColumn = $pdo->query("SELECT column_name FROM information_schema.columns 
                               WHERE table_name='absence' AND column_name='verrouille'");
    
    if ($checkColumn->fetch()) {
        $sql = "UPDATE Absence 
                SET verrouille = TRUE, date_verrouillage = NOW()
                WHERE date_fin < NOW() - INTERVAL '48 hours'
                AND justifie = FALSE
                AND (verrouille = FALSE OR verrouille IS NULL)";
    } else {
        $sql = "UPDATE Absence 
                SET justifie = FALSE
                WHERE date_fin < NOW() - INTERVAL '48 hours'
                AND justifie = FALSE";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $_SESSION['derniere_verif'] = time();
}

function envoyerRappels(){
    if(!isset($_SESSION)){
        session_start();
    }
    
    if (isset($_SESSION['dernier_rappel']) && $_SESSION['dernier_rappel'] > time() - 21600){
        return;
    }
    
    require_once __DIR__ .'/Database.php';
    
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Vérifier si la colonne verrouille existe
    $checkColumn = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name='absence' AND column_name='verrouille'");
    
    if ($checkColumn->fetch()) {
        $sql = "SELECT a.idAbsence, c.nom, c.prenom, a.idEtudiantFROM Absence a 
        JOIN Etudiant e ON a.idEtudiant = e.idEtudiant
        JOIN Compte c ON e.idEtudiant = c.idCompte
        WHERE a.date_fin BETWEEN NOW() - INTERVAL '25 hours' AND NOW() - INTERVAL '23 hours' AND a.justifie = FALSE AND (a.verrouille = FALSE OR a.verrouille IS NULL)";
    } else {
        $sql = "SELECT a.idAbsence, c.nom, c.prenom, a.idEtudiant FROM Absence a
                JOIN Etudiant e ON a.idEtudiant = e.idEtudiant
                JOIN Compte c ON e.idEtudiant = c.idCompte
                WHERE a.date_fin BETWEEN NOW() - INTERVAL '25 hours' AND NOW() - INTERVAL '23 hours' AND a.justifie = FALSE";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $absences = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($absences as $absence){
        $email = strtolower($absence['prenom']).'.'.strtolower($absence['nom']).'@uphf.fr';
        
        $sujet = "[GESTION-ABS] Rappel - Plus que 24h pour justifier votre absence";
        $message = "Bonjour " . $absence['prenom'] . " " . $absence['nom'] . ",\n\n";
        $message .= "Il vous reste moins de 24h pour justifier votre absence.\n";
        $message .= "Passé ce délai, votre absence sera considérée comme non justifiée.\n\n";
        $message .= "Cordialement,\nLe service de scolarité";
        
        // Pour l'instant on log juste (pour éviter d'envoyer des vrais mails)
        error_log("Rappel à envoyer à $email pour l'absence ID: " . $absence['idAbsence']);
        
        // Décommenter pour envoyer vraiment les mails
        // mail($email, $sujet, $message);
    }
    
    $_SESSION['dernier_rappel'] = time();
}
}
?>