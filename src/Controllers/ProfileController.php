<?php
namespace src\Controllers;

require_once __DIR__ . '/../Models/ProfileModel.php';
require_once __DIR__ . '/session_timeout.php';

use src\Models\ProfileModel;

class ProfileController {
    private $profileModel;

    public function __construct()
    {
        $this->profileModel = new ProfileModel();
    }

    public function show()
    {
        session_start();
        if (!isset($_SESSION['login'])) {
            header('Location: ../../public/index.php');
            exit;
        }

        $identifiant = $_SESSION['login'];
        $message = '';
        $messageType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_password') {
            list($message, $messageType) = $this->handlePasswordUpdate($identifiant);
        }

        $user = $this->profileModel->getUserByIdentifiant($identifiant);

        if (!$user) {
            $user = [
                'identifiantCompte' => $identifiant,
                'prenom' => $_SESSION['prenom'] ?? '',
                'nom' => $_SESSION['nom'] ?? '',
                'fonction' => $_SESSION['role'] ?? null
            ];
        }

        $user['email'] = ($user['identifiantCompte'] ?? $identifiant) . '@uphf.fr';
        $role = strtolower((string)($user['fonction'] ?? ''));

        if ($role === 'etudiant' || $role === 'etudiante') {
            $etudiantDetails = $this->profileModel->getEtudiantDetails($user['idCompte'] ?? null, $identifiant);
            if ($etudiantDetails) {
                $user = array_merge($user, $etudiantDetails);
            }
        }

        require __DIR__ . '/../Views/profile/profile_vue.php';
    }

    private function handlePasswordUpdate($identifiant)
    {
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($old) || empty($new) || empty($confirm)) {
            return ['Veuillez remplir tous les champs.', 'error'];
        }

        $user = $this->profileModel->getUserByIdentifiant($identifiant);

        if (!$user || !password_verify($old, $user['mot_de_passe'])) {
            return ['L\'ancien mot de passe est incorrect.', 'error'];
        }

        if ($new !== $confirm) {
            return ['Les nouveaux mots de passe ne correspondent pas.', 'error'];
        }

        if ($this->profileModel->updateUserPassword($identifiant, $new)) {
            return ['Mot de passe mis à jour avec succès.', 'success'];
        } else {
            return ['Erreur lors de la mise à jour du mot de passe.', 'error'];
        }
    }
}

            }
            $etu = $stmtE->fetch(\PDO::FETCH_ASSOC);
            $user['groupe'] = $etu['formation'] ?? null;
        } elseif ($role === 'professeur') {
            // Chercher les matières (Ressource.nom) enseignées par ce professeur via Professeur -> Cours -> Ressource
            if ($idCompte !== null) {
                $stmtP = $pdo->prepare('SELECT DISTINCT r.nom AS matiere FROM Professeur p JOIN Cours c ON p.idProfesseur = c.idProfesseur JOIN Ressource r ON c.idRessource = r.idRessource WHERE p.idProfesseur = :idCompte');
                $stmtP->execute([':idCompte' => $idCompte]);
            } else {
                $stmtP = $pdo->prepare('SELECT DISTINCT r.nom AS matiere FROM Professeur p JOIN Cours c ON p.idProfesseur = c.idProfesseur JOIN Ressource r ON c.idRessource = r.idRessource WHERE p.identifiantProf = :id');
                $stmtP->execute([':id' => $identFromDb]);
            }
            $matieres = $stmtP->fetchAll(\PDO::FETCH_COLUMN);
            $user['matieres'] = $matieres ?: [];
        }

        // Rendre la vue
        require __DIR__ . '/../Views/profile/monProfil.php';
    }
}
