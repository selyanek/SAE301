<?php
namespace src\Controllers;

session_start();
require __DIR__ . '/session_timeout.php'; // Gestion du timeout de session

require __DIR__ . '/../Database/Database.php';

use src\Database\Database;

class ProfileController {
    public static function show()
    {
        // Vérifier connexion
        if (!isset($_SESSION['login'])) {
            header('Location: ../../public/index.php');
            exit;
        }

        $identifiant = $_SESSION['login'];
        $bd = new Database();
        $pdo = $bd->getConnection();

        $message = '';
        $messageType = '';

        // Traitement de la mise à jour du mot de passe
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_password') {
            $old = $_POST['old_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (empty($old) || empty($new) || empty($confirm)) {
                $message = 'Veuillez remplir tous les champs.';
                $messageType = 'error';
            } else {
                // Vérifier l'ancien mot de passe avec password_verify
                $stmt = $pdo->prepare('SELECT mot_de_passe FROM Compte WHERE identifiantCompte = :id');
                $stmt->execute([':id' => $identifiant]);
                $currentPassword = $stmt->fetchColumn();

                if (!password_verify($old, $currentPassword)) {
                    $message = 'L\'ancien mot de passe est incorrect.';
                    $messageType = 'error';
                } elseif ($new !== $confirm) {
                    $message = 'Les nouveaux mots de passe ne correspondent pas.';
                    $messageType = 'error';
                } else {
                    // Hacher le nouveau mot de passe avant de le stocker
                    $hashedPassword = password_hash($new, PASSWORD_DEFAULT);
                    
                    // Mise à jour avec le mot de passe haché
                    $stmt = $pdo->prepare('UPDATE Compte SET mot_de_passe = :mdp WHERE identifiantCompte = :id');
                    $updated = $stmt->execute([':mdp' => $hashedPassword, ':id' => $identifiant]);
                    if ($updated) {
                        // Ne pas stocker le mot de passe en session
                        $message = 'Mot de passe mis à jour avec succès.';
                        $messageType = 'success';
                    } else {
                        $message = 'Erreur lors de la mise à jour du mot de passe.';
                        $messageType = 'error';
                    }
                }
            }
        }

        // Récupérer les informations utilisateur depuis la table Compte
        $stmt = $pdo->prepare('SELECT idCompte, prenom, nom, identifiantCompte, fonction FROM Compte WHERE identifiantCompte = :id');
        $stmt->execute([':id' => $identifiant]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Si pas trouvé, créer un fallback minimal basé sur la session
        if (!is_array($user) || empty($user)) {
            $user = [
                'idCompte' => null,
                'identifiantCompte' => $identifiant,
                'prenom' => $_SESSION['prenom'] ?? '',
                'nom' => $_SESSION['nom'] ?? '',
                'fonction' => $_SESSION['role'] ?? null
            ];
        }

        // sécuriser les accès aux clés
        $identFromDb = $user['identifiantCompte'] ?? $identifiant;
        $idCompte = $user['idCompte'] ?? null;
        $fonction = $user['fonction'] ?? ($_SESSION['role'] ?? '');

        // Email fallback - tous les utilisateurs ont @uphf.fr
        $user['email'] = $identFromDb . '@uphf.fr';

        // Récupérer les informations spécifiques selon le rôle si possible
        $role = strtolower((string)$fonction);
        if ($role === 'etudiant' || $role === 'etudiante') {
            // Chercher la formation/groupe dans la table Etudiant si possible
            if ($idCompte !== null) {
                $stmtE = $pdo->prepare('SELECT formation FROM Etudiant WHERE idEtudiant = :idCompte');
                $stmtE->execute([':idCompte' => $idCompte]);
            } else {
                $stmtE = $pdo->prepare('SELECT formation FROM Etudiant WHERE identifiantEtu = :id');
                $stmtE->execute([':id' => $identFromDb]);
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
