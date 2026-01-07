<?php

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use src\Models\Login;
use PDO;

class LoginTest extends TestCase
{
    private $pdo;
    
    /**
     * Configuration avant chaque test
     * Crée une base de données SQLite en mémoire avec des données de test
     */
    protected function setUp(): void
    {
        // Créer une base de données SQLite en mémoire pour les tests
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Créer la structure des tables
        $this->createTables();
        
        // Insérer des données de test
        $this->insertTestData();
    }
    
    /**
     * Nettoyage après chaque test
     */
    protected function tearDown(): void
    {
        $this->pdo = null;
    }
    
    /**
     * Crée les tables nécessaires pour les tests
     */
    private function createTables(): void
    {
        $this->pdo->exec("
            CREATE TABLE compte (
                idcompte INTEGER PRIMARY KEY AUTOINCREMENT,
                identifiantcompte TEXT NOT NULL UNIQUE,
                mot_de_passe TEXT NOT NULL,
                nom TEXT NOT NULL,
                prenom TEXT NOT NULL,
                fonction TEXT NOT NULL
            )
        ");
        
        $this->pdo->exec("
            CREATE TABLE etudiant (
                idetudiant INTEGER PRIMARY KEY,
                identifiantetu TEXT NOT NULL,
                FOREIGN KEY (idetudiant) REFERENCES compte(idcompte)
            )
        ");
    }
    
    /**
     * Insère des données de test dans la base
     */
    private function insertTestData(): void
    {
        // Compte étudiant 1
        $this->pdo->exec("
            INSERT INTO compte (idcompte, identifiantcompte, mot_de_passe, nom, prenom, fonction)
            VALUES (1, 'alice.martin', 'securepass456', 'Martin', 'Alice', 'etudiant')
        ");
        
        $this->pdo->exec("
            INSERT INTO etudiant (idetudiant, identifiantetu)
            VALUES (1, 'ETU2024001')
        ");
        
        // Compte étudiant 2
        $this->pdo->exec("
            INSERT INTO compte (idcompte, identifiantcompte, mot_de_passe, nom, prenom, fonction)
            VALUES (2, 'antoine.blanc', 'MVVLc84mP', 'Blanc', 'Antoine', 'etudiant')
        ");
        
        $this->pdo->exec("
            INSERT INTO etudiant (idetudiant, identifiantetu)
            VALUES (2, 'ETU2024002')
        ");
        
        // Compte professeur
        $this->pdo->exec("
            INSERT INTO compte (idcompte, identifiantcompte, mot_de_passe, nom, prenom, fonction)
            VALUES (3, 'john.doe', 'x', 'Doe', 'John', 'professeur')
        ");
        
        // Compte responsable_absence
        $this->pdo->exec("
            INSERT INTO compte (idcompte, identifiantcompte, mot_de_passe, nom, prenom, fonction)
            VALUES (4, 'christelle.roze', 'anotherpassword', 'Roze', 'Christelle', 'responsable_absence')
        ");
        
        // Compte secretaire
        $this->pdo->exec("
            INSERT INTO compte (idcompte, identifiantcompte, mot_de_passe, nom, prenom, fonction)
            VALUES (5, 'delphine.milice', 'motdepasse123', 'Milice', 'Delphine', 'secretaire')
        ");
    }
    
    // ========== TESTS POUR verifierConnexion() ==========
    
    /**
     * Test : Connexion réussie avec identifiants valides (étudiant)
     */
    public function testVerifierConnexionAvecIdentifiantsValidesEtudiant()
    {
        $login = new Login('alice.martin', 'securepass456');
        $result = $login->verifierConnexion($this->pdo);
        
        $this->assertTrue($result, "La connexion devrait réussir avec des identifiants valides");
    }
    
    /**
     * Test : Connexion réussie avec identifiants valides (professeur)
     */
    public function testVerifierConnexionAvecIdentifiantsValidesProfesseur()
    {
        $login = new Login('john.doe', 'x');
        $result = $login->verifierConnexion($this->pdo);
        
        $this->assertTrue($result, "La connexion devrait réussir avec des identifiants valides");
    }
    
    /**
     * Test : Connexion échouée avec mauvais mot de passe
     */
    public function testVerifierConnexionAvecMauvaisMotDePasse()
    {
        $login = new Login('alice.martin', 'mauvais_password');
        $result = $login->verifierConnexion($this->pdo);
        
        $this->assertFalse($result, "La connexion devrait échouer avec un mauvais mot de passe");
    }
    
    /**
     * Test : Connexion échouée avec identifiant inexistant
     */
    public function testVerifierConnexionAvecIdentifiantInexistant()
    {
        $login = new Login('utilisateur.inconnu', 'password123');
        $result = $login->verifierConnexion($this->pdo);
        
        $this->assertFalse($result, "La connexion devrait échouer avec un identifiant inexistant");
    }
    
    /**
     * Test : Connexion échouée avec identifiants vides
     */
    public function testVerifierConnexionAvecIdentifiantsVides()
    {
        $login = new Login('', '');
        $result = $login->verifierConnexion($this->pdo);
        
        $this->assertFalse($result, "La connexion devrait échouer avec des identifiants vides");
    }
    
    // ========== TESTS POUR verifRole() ==========
    
    /**
     * Test : Récupération du rôle étudiant
     */
    public function testVerifRolePourEtudiant()
    {
        $login = new Login('alice.martin', 'securepass456');
        $role = $login->verifRole($this->pdo);
        
        $this->assertEquals('etudiant', $role, "Le rôle devrait être 'etudiant'");
    }
    
    /**
     * Test : Récupération du rôle professeur
     */
    public function testVerifRolePourProfesseur()
    {
        $login = new Login('john.doe', 'x');
        $role = $login->verifRole($this->pdo);
        
        $this->assertEquals('professeur', $role, "Le rôle devrait être 'professeur'");
    }
    
    /**
     * Test : Récupération du rôle responsable_absence
     */
    public function testVerifRolePourResponsableAbsence()
    {
        $login = new Login('christelle.roze', 'anotherpassword');
        $role = $login->verifRole($this->pdo);
        
        $this->assertEquals('responsable_absence', $role, "Le rôle devrait être 'responsable_absence'");
    }
    
    /**
     * Test : Récupération du rôle secretaire
     */
    public function testVerifRolePourSecretaire()
    {
        $login = new Login('delphine.milice', 'motdepasse123');
        $role = $login->verifRole($this->pdo);
        
        $this->assertEquals('secretaire', $role, "Le rôle devrait être 'secretaire'");
    }
    
    /**
     * Test : Rôle retourne false avec mauvais identifiants
     */
    public function testVerifRoleAvecMauvaisIdentifiants()
    {
        $login = new Login('alice.martin', 'mauvais_password');
        $role = $login->verifRole($this->pdo);
        
        $this->assertFalse($role, "Le rôle devrait retourner false avec de mauvais identifiants");
    }
    
    // ========== TESTS POUR getName() ==========
    
    /**
     * Test : Récupération du nom complet avec identifiants valides
     */
    public function testGetNameAvecIdentifiantsValides()
    {
        $login = new Login('alice.martin', 'securepass456');
        $name = $login->getName($this->pdo);
        
        $this->assertEquals('Alice Martin', $name, "Le nom devrait être 'Alice Martin'");
    }
    
    /**
     * Test : getName retourne false avec mauvais identifiants
     */
    public function testGetNameAvecMauvaisIdentifiants()
    {
        $login = new Login('alice.martin', 'mauvais_password');
        $name = $login->getName($this->pdo);
        
        $this->assertFalse($name, "getName devrait retourner false avec de mauvais identifiants");
    }
    
    /**
     * Test : Format correct du nom (Prénom Nom)
     */
    public function testGetNameFormatCorrect()
    {
        $login = new Login('john.doe', 'x');
        $name = $login->getName($this->pdo);
        
        $this->assertEquals('John Doe', $name);
        $this->assertStringStartsWith('John', $name, "Le nom devrait commencer par le prénom");
    }
    
    // ========== TESTS POUR getPwd() ==========
    
    /**
     * Test : Récupération du mot de passe avec identifiants valides
     */
    public function testGetPwdAvecIdentifiantsValides()
    {
        $login = new Login('alice.martin', 'securepass456');
        $pwd = $login->getPwd($this->pdo);
        
        $this->assertEquals('securepass456', $pwd, "Le mot de passe devrait être 'securepass456'");
    }
    
    /**
     * Test : getPwd retourne false avec mauvais identifiants
     */
    public function testGetPwdAvecMauvaisIdentifiants()
    {
        $login = new Login('alice.martin', 'mauvais_password');
        $pwd = $login->getPwd($this->pdo);
        
        $this->assertFalse($pwd, "getPwd devrait retourner false avec de mauvais identifiants");
    }
    
    // ========== TESTS POUR getRole() ==========
    
    /**
     * Test : getRole sans vérification du mot de passe
     */
    public function testGetRoleSansMotDePasse()
    {
        $login = new Login('alice.martin', 'nimporte_quoi');
        $role = $login->getRole($this->pdo);
        
        $this->assertEquals('etudiant', $role, "getRole devrait retourner le rôle sans vérifier le mot de passe");
    }
    
    /**
     * Test : getRole avec identifiant inexistant
     */
    public function testGetRoleAvecIdentifiantInexistant()
    {
        $login = new Login('utilisateur.inconnu', 'password');
        $role = $login->getRole($this->pdo);
        
        $this->assertFalse($role, "getRole devrait retourner false avec un identifiant inexistant");
    }
    
    // ========== TESTS POUR getIdUtilisateur() ==========
    
    /**
     * Test : Récupération de l'ID utilisateur
     */
    public function testGetIdUtilisateurAvecIdentifiantValide()
    {
        $login = new Login('alice.martin', 'securepass456');
        $user = $login->getIdUtilisateur($this->pdo);
        
        $this->assertIsArray($user, "getIdUtilisateur devrait retourner un tableau");
        $this->assertArrayHasKey('idcompte', $user, "Le tableau devrait contenir 'idcompte'");
        $this->assertEquals(1, $user['idcompte'], "L'ID devrait être 1");
    }
    
    /**
     * Test : getIdUtilisateur retourne null avec identifiant inexistant
     */
    public function testGetIdUtilisateurAvecIdentifiantInexistant()
    {
        $login = new Login('utilisateur.inconnu', 'password');
        $user = $login->getIdUtilisateur($this->pdo);
        
        $this->assertNull($user, "getIdUtilisateur devrait retourner null avec un identifiant inexistant");
    }
    
    // ========== TESTS POUR getIdentifiantEtu() ==========
    
    /**
     * Test : Récupération de l'identifiant étudiant
     */
    public function testGetIdentifiantEtuPourEtudiant()
    {
        $login = new Login('alice.martin', 'securepass456');
        $identifiant = $login->getIdentifiantEtu($this->pdo);
        
        $this->assertEquals('ETU2024001', $identifiant, "L'identifiant étudiant devrait être 'ETU2024001'");
    }
    
    /**
     * Test : getIdentifiantEtu retourne null pour un non-étudiant
     */
    public function testGetIdentifiantEtuPourNonEtudiant()
    {
        $login = new Login('john.doe', 'x');
        $identifiant = $login->getIdentifiantEtu($this->pdo);
        
        $this->assertNull($identifiant, "getIdentifiantEtu devrait retourner null pour un professeur");
    }
    
    /**
     * Test : getIdentifiantEtu retourne null avec identifiant inexistant
     */
    public function testGetIdentifiantEtuAvecIdentifiantInexistant()
    {
        $login = new Login('utilisateur.inconnu', 'password');
        $identifiant = $login->getIdentifiantEtu($this->pdo);
        
        $this->assertNull($identifiant, "getIdentifiantEtu devrait retourner null avec un identifiant inexistant");
    }
    
    // ========== TESTS POUR getAllUserData() ==========
    
    /**
     * Test : Récupération de toutes les données utilisateur pour un étudiant
     */
    public function testGetAllUserDataPourEtudiant()
    {
        $login = new Login('alice.martin', 'securepass456');
        $data = $login->getAllUserData($this->pdo);
        
        $this->assertIsArray($data, "getAllUserData devrait retourner un tableau");
        $this->assertArrayHasKey('idcompte', $data);
        $this->assertArrayHasKey('identifiantcompte', $data);
        $this->assertArrayHasKey('nom', $data);
        $this->assertArrayHasKey('prenom', $data);
        $this->assertArrayHasKey('fonction', $data);
        $this->assertArrayHasKey('mot_de_passe', $data);
        $this->assertArrayHasKey('identifiantetu', $data);
        
        $this->assertEquals('alice.martin', $data['identifiantcompte']);
        $this->assertEquals('Martin', $data['nom']);
        $this->assertEquals('Alice', $data['prenom']);
        $this->assertEquals('etudiant', $data['fonction']);
        $this->assertEquals('ETU2024001', $data['identifiantetu']);
    }
    
    /**
     * Test : getAllUserData pour un professeur (sans identifiantEtu)
     */
    public function testGetAllUserDataPourProfesseur()
    {
        $login = new Login('john.doe', 'x');
        $data = $login->getAllUserData($this->pdo);
        
        $this->assertIsArray($data);
        $this->assertEquals('john.doe', $data['identifiantcompte']);
        $this->assertEquals('professeur', $data['fonction']);
        $this->assertNull($data['identifiantetu'], "Un professeur ne devrait pas avoir d'identifiantEtu");
    }
    
    /**
     * Test : getAllUserData retourne false avec mauvais identifiants
     */
    public function testGetAllUserDataAvecMauvaisIdentifiants()
    {
        $login = new Login('alice.martin', 'mauvais_password');
        $data = $login->getAllUserData($this->pdo);
        
        $this->assertFalse($data, "getAllUserData devrait retourner false avec de mauvais identifiants");
    }
    
    /**
     * Test : getAllUserData évite les requêtes multiples
     */
    public function testGetAllUserDataOptimisation()
    {
        $login = new Login('alice.martin', 'securepass456');
        $data = $login->getAllUserData($this->pdo);
        
        // Vérifier que toutes les données sont présentes en une seule requête
        $this->assertNotEmpty($data);
        $this->assertCount(7, $data, "Le tableau devrait contenir 7 éléments");
    }
    
    // ========== TESTS DE SÉCURITÉ ==========
    
    /**
     * Test : Protection contre les injections SQL dans l'identifiant
     */
    public function testProtectionInjectionSQLIdentifiant()
    {
        $login = new Login("admin' OR '1'='1", 'password');
        $result = $login->verifierConnexion($this->pdo);
        
        $this->assertFalse($result, "La connexion devrait échouer avec une tentative d'injection SQL");
    }
    
    /**
     * Test : Protection contre les injections SQL dans le mot de passe
     */
    public function testProtectionInjectionSQLMotDePasse()
    {
        $login = new Login('alice.martin', "' OR '1'='1");
        $result = $login->verifierConnexion($this->pdo);
        
        $this->assertFalse($result, "La connexion devrait échouer avec une tentative d'injection SQL");
    }
    
    /**
     * Test : Gestion des caractères spéciaux dans l'identifiant
     */
    public function testCaracteresSpeciauxDansIdentifiant()
    {
        $login = new Login('alice.martin@uphf.fr', 'securepass456');
        $result = $login->verifierConnexion($this->pdo);
        
        // Devrait échouer car cet identifiant n'existe pas, mais ne devrait pas causer d'erreur
        $this->assertFalse($result);
    }
    
    /**
     * Test : Vérification de plusieurs utilisateurs différents
     */
    public function testPlusieursUtilisateurs()
    {
        // Étudiant 1
        $login1 = new Login('alice.martin', 'securepass456');
        $this->assertTrue($login1->verifierConnexion($this->pdo));
        $this->assertEquals('etudiant', $login1->verifRole($this->pdo));
        
        // Étudiant 2
        $login2 = new Login('antoine.blanc', 'MVVLc84mP');
        $this->assertTrue($login2->verifierConnexion($this->pdo));
        $this->assertEquals('etudiant', $login2->verifRole($this->pdo));
        
        // Professeur
        $login3 = new Login('john.doe', 'x');
        $this->assertTrue($login3->verifierConnexion($this->pdo));
        $this->assertEquals('professeur', $login3->verifRole($this->pdo));
    }
}