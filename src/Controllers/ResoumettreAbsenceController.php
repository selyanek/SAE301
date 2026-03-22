<?php
namespace src\Controllers;

use src\Database\Database;
use src\Models\Absence;

class ResoumettreAbsenceController
{
    private $absenceModel;
    private $pdo;

    public function __construct()
    {
        session_start();
        require_once __DIR__ . '/session_timeout.php';
        
        $db = new Database();
        $this->pdo = $db->getConnection();
        $this->absenceModel = new Absence($this->pdo);
    }

    public function afficherFormulaire()
    {
        $this->verifierAccesEtudiant();

        $idAbsence = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$idAbsence) {
            header('Location: justificatif.php');
            exit();
        }

        $absence = $this->absenceModel->getById($idAbsence);
        $identifiantEtu = $_SESSION['identifiantEtu'] ?? $_SESSION['login'];

        if (!$this->validerAbsencePourRessoumission($absence, $identifiantEtu)) {
            header('Location: justificatif.php');
            exit();
        }

        $pageTitle = 'Modifier et resoumettre une absence';
        $additionalCSS = ['../../../public/asset/CSS/cssGererAbsEtu.css', '../../../public/asset/CSS/cssRessoumission.css'];
        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . '/../Views/layout/navigation.php';
        require __DIR__ . '/../Views/etudiant/resoumettre_absence_vue.php';
    }

    public function traiterFormulaire()
    {
        $this->verifierAccesEtudiant();

        $idAbsence = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if (!$idAbsence) {
            header('Location: justificatif.php');
            exit();
        }

        $absence = $this->absenceModel->getById($idAbsence);
        $identifiantEtu = $_SESSION['identifiantEtu'] ?? $_SESSION['login'];

        if (!$this->validerAbsencePourRessoumission($absence, $identifiantEtu)) {
            header('Location: justificatif.php');
            exit();
        }

        $nouveauMotif = trim($_POST['motif'] ?? '');
        $nouvelleUriJustificatif = $this->gererUploadFichier($absence['urijustificatif']);

        if (empty($nouveauMotif)) {
            $erreur = 'Le motif est obligatoire.';
        } else {
            $result = $this->absenceModel->resoumettre($idAbsence, $nouveauMotif, $nouvelleUriJustificatif);
            
            if ($result) {
                $_SESSION['success'] = 'Votre absence a été resoumise avec succès ! Elle est maintenant en attente de validation.';
                header('Location: justificatif.php');
                exit();
            } else {
                $erreur = 'Erreur lors de la ressoumission. Veuillez réessayer.';
            }
        }

        // Si erreur, ré-afficher le formulaire avec le message
        $pageTitle = 'Modifier et resoumettre une absence';
        $additionalCSS = ['../../../public/asset/CSS/cssGererAbsEtu.css', '../../../public/asset/CSS/cssRessoumission.css'];
        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . '/../Views/layout/navigation.php';
        require __DIR__ . '/../Views/etudiant/resoumettre_absence_vue.php';
    }

    private function verifierAccesEtudiant()
    {
        if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'etudiant' && $_SESSION['role'] !== 'etudiante')) {
            header('Location: /public/index.php');
            exit();
        }
    }

    private function validerAbsencePourRessoumission($absence, $identifiantEtu)
    {
        if (!$absence || $absence['identifiantetu'] !== $identifiantEtu) {
            $_SESSION['errors'] = ['Cette absence ne vous appartient pas.'];
            return false;
        }

        if ($absence['type_refus'] !== 'ressoumission' || $absence['justifie'] !== false && $absence['justifie'] !== 'f' && $absence['justifie'] !== '0') {
            $_SESSION['errors'] = ['Cette absence ne peut pas être resoumise.'];
            return false;
        }
        return true;
    }

    private function gererUploadFichier($ancienJustificatif)
    {
        if (isset($_FILES['justificatif']) && $_FILES['justificatif']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['justificatif']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['justificatif']['tmp_name'], $uploadFile)) {
                return json_encode([$fileName]);
            }
        }
        return $ancienJustificatif;
    }
}
