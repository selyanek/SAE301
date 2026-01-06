<?php
namespace src\Utils;

use src\Models\EmailService;
use PDO;

class PseudoCron
{
    private $pdo;
    private $emailService;
    private $lockFile;
    
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->emailService = new EmailService();
        
        // Chemin du fichier de verrouillage
        $this->lockFile = __DIR__ . '/../../cache/cron.lock';
    }
    
    /**
     * Vérifie si le cron doit s'exécuter (toutes les heures)
     */
    public function shouldRun()
    {
        if (!file_exists($this->lockFile)) {
            return true;
        }
        
        $lastRun = filemtime($this->lockFile);
        $oneHourAgo = time() - 3600; // 1 heure = 3600 secondes
        
        return $lastRun < $oneHourAgo;
    }
    
    /**
     * Marque que le cron vient de s'exécuter
     */
    private function markAsRun()
    {
        $dir = dirname($this->lockFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        touch($this->lockFile);
    }
    
    /**
     * Exécute les tâches de notification si nécessaire
     */
    public function execute()
    {
        if (!$this->shouldRun()) {
            return false;
        }
        
        // Exécuter en arrière-plan pour ne pas ralentir l'utilisateur
        ignore_user_abort(true);
        set_time_limit(0);
        
        try {
            $this->sendInitialAlerts();
            $this->sendReminderAlerts();
            $this->lockExpiredAbsences();
            
            $this->markAsRun();
            return true;
        } catch (\Exception $e) {
            error_log("Erreur PseudoCron: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Envoie les alertes initiales aux étudiants de retour
     */
    private function sendInitialAlerts()
    {
        $query = "
            SELECT 
                a.idabsence,
                a.date_debut,
                a.date_fin,
                e.identifiantu as student_id,
                CONCAT(comp.prenom, ' ', comp.nom) as student_name,
                comp.identifiantcompte as student_email,
                c.nom as cours_nom,
                c.type as cours_type
            FROM absence a
            INNER JOIN etudiant e ON a.idetudiant = e.idetudiant
            INNER JOIN compte comp ON e.identifiantu = comp.identifiantcompte
            INNER JOIN cours c ON a.idcours = c.idcours
            LEFT JOIN rattrapage rp ON a.idabsence = rp.idabsence
            WHERE a.justifie = 0
                AND a.date_fin < NOW()
                AND rp.idrattrapage IS NULL
                AND a.revision = 0
                AND NOT EXISTS (
                    SELECT 1 FROM databasechangelog dcl
                    WHERE dcl.contexts = CONCAT('alert_initial_', a.idabsence)
                )
        ";
        
        $stmt = $this->pdo->query($query);
        $absences = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($absences as $absence) {
            $isUrgent = stripos($absence['cours_type'], 'evaluation') !== false;
            
            $sent = $this->emailService->sendAbsenceAlertEmail(
                $absence['student_email'],
                $absence['student_name'],
                $absence['idabsence'],
                $absence['date_debut'],
                $absence['date_fin'],
                $absence['cours_nom'],
                $isUrgent,
                48
            );
            
            if ($sent) {
                $this->logNotification($absence['idabsence'], 'alert_initial');
            }
        }
    }
    
    /**
     * Envoie les rappels 24h après l'alerte initiale
     */
    private function sendReminderAlerts()
    {
        $query = "
            SELECT 
                a.idabsence,
                a.date_debut,
                a.date_fin,
                CONCAT(comp.prenom, ' ', comp.nom) as student_name,
                comp.identifiantcompte as student_email,
                c.nom as cours_nom,
                c.type as cours_type
            FROM absence a
            INNER JOIN etudiant e ON a.idetudiant = e.idetudiant
            INNER JOIN compte comp ON e.identifiantu = comp.identifiantcompte
            INNER JOIN cours c ON a.idcours = c.idcours
            LEFT JOIN rattrapage rp ON a.idabsence = rp.idabsence
            INNER JOIN databasechangelog dcl ON dcl.contexts = CONCAT('alert_initial_', a.idabsence)
            WHERE a.justifie = 0
                AND rp.idrattrapage IS NULL
                AND a.revision = 0
                AND TIMESTAMPDIFF(HOUR, dcl.datesexecuted, NOW()) >= 24
                AND NOT EXISTS (
                    SELECT 1 FROM databasechangelog dcl2
                    WHERE dcl2.contexts = CONCAT('alert_reminder_', a.idabsence)
                )
        ";
        
        $stmt = $this->pdo->query($query);
        $absences = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($absences as $absence) {
            $isUrgent = stripos($absence['cours_type'], 'evaluation') !== false;
            
            $sent = $this->emailService->sendAbsenceAlertEmail(
                $absence['student_email'],
                $absence['student_name'],
                $absence['idabsence'],
                $absence['date_debut'],
                $absence['date_fin'],
                $absence['cours_nom'],
                $isUrgent,
                24
            );
            
            if ($sent) {
                $this->logNotification($absence['idabsence'], 'alert_reminder');
            }
        }
    }
    
    /**
     * Verrouille les absences expirées après 48h
     */
    private function lockExpiredAbsences()
    {
        $query = "
            SELECT a.idabsence
            FROM absence a
            INNER JOIN databasechangelog dcl ON dcl.contexts = CONCAT('alert_initial_', a.idabsence)
            LEFT JOIN rattrapage rp ON a.idabsence = rp.idabsence
            WHERE a.justifie = 0
                AND rp.idrattrapage IS NULL
                AND a.revision = 0
                AND TIMESTAMPDIFF(HOUR, dcl.datesexecuted, NOW()) >= 48
        ";
        
        $stmt = $this->pdo->query($query);
        $absences = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($absences as $absence) {
            $updateQuery = "UPDATE absence SET revision = 1 WHERE idabsence = ?";
            $updateStmt = $this->pdo->prepare($updateQuery);
            $updateStmt->execute([$absence['idabsence']]);
            
            $this->logNotification($absence['idabsence'], 'locked');
        }
    }
    
    /**
     * Enregistre la notification dans databasechangelog
     */
    private function logNotification($idAbsence, $type)
    {
        $query = "
            INSERT INTO databasechangelog (id, author, description, contexts, datesexecuted, exectype)
            VALUES (?, 'system', ?, ?, NOW(), 'email_notification')
        ";
        
        $id = uniqid('notif_', true);
        $context = "{$type}_{$idAbsence}";
        $description = "Email {$type} pour l'absence {$idAbsence}";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$id, $description, $context]);
    }
}
