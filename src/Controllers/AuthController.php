<?php
namespace src\Controllers;

use src\Database\Database;
use src\Models\Login;

class AuthController
{
    private $pdo;
    
    public function __construct()
    {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }
    
    /**
     * Gère la connexion de l'utilisateur
     */
    public function login($identifiant, $mot_de_passe)
    {
        $login = new Login($identifiant, $mot_de_passe);
        
        // Vérifier les identifiants
        if (!$login->verifierConnexion($this->pdo)) {
            return [
                'success' => false,
                'message' => 'Identifiant ou mot de passe incorrect.'
            ];
        }
        
        // Récupérer toutes les données utilisateur
        $userData = $login->getAllUserData($this->pdo);
        
        // Initialiser la session
        $this->initializeSession($userData);
        
        // Obtenir l'URL de redirection
        $redirectUrl = $this->getRedirectUrlByRole($userData['fonction']);
        
        return [
            'success' => true,
            'redirect' => $redirectUrl
        ];
    }
    
    /**
     * Initialise la session utilisateur
     */
    private function initializeSession($userData)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['login'] = $userData['identifiantcompte'];
        $_SESSION['nom'] = trim($userData['prenom'] . ' ' . $userData['nom']);
        $_SESSION['role'] = $userData['fonction'];
        $_SESSION['last_activity'] = time();
        $_SESSION['idCompte'] = $userData['idcompte'];
        $_SESSION['idEtudiant'] = $userData['idcompte'];
        
        // Si c'est un étudiant, ajouter son identifiant étudiant
        if (in_array($userData['fonction'], ['etudiant', 'etudiante'])) {
            if (!empty($userData['identifiantetu'])) {
                $_SESSION['identifiantEtu'] = $userData['identifiantetu'];
            }
        }
    }
    
    /**
     * Retourne l'URL de redirection selon le rôle
     */
    private function getRedirectUrlByRole($role)
    {
        $routes = [
            'etudiant' => '../src/Views/etudiant/dashboard.php',
            'etudiante' => '../src/Views/etudiant/dashboard.php',
            'professeur' => '../src/Views/accueil_prof.php',
            'responsable_pedagogique' => '../src/Views/responsable/dashboard.php',
            'secretaire' => '../src/Views/secretaire/dashboard.php'
        ];
        
        return $routes[$role] ?? null;
    }
    
    /**
     * Vérifie si l'utilisateur a été déconnecté pour inactivité
     */
    public function checkTimeout()
    {
        if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
            return 'Vous avez été déconnecté pour inactivité. Veuillez vous reconnecter.';
        }
        return null;
    }
}
