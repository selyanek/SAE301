<?php

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use src\Models\Login;
use PDO;
use PDOStatement;

/**
 * Tests unitaires pour le modèle Login
 * Utilise des mocks PDO pour isoler les tests de la base de données
 */
class LoginTest extends TestCase
{
    private $mockPdo;
    private $mockStmt;
    
    /**
     * Initialisation avant chaque test
     * Crée les mocks PDO et PDOStatement nécessaires
     */
    protected function setUp(): void
    {
        $this->mockPdo = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);
    }
    
    // ========== TESTS POUR verifierConnexion() ==========
    
    /**
     * Test : Connexion réussie avec identifiants valides
     */
    public function testVerifierConnexionAvecIdentifiantsValides()
    {
        $login = new Login('alice.martin', 'securepass456');
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
        
        $this->mockStmt->expects($this->once())
            ->method('execute');
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(['identifiantcompte' => 'alice.martin']);
        
        $result = $login->verifierConnexion($this->mockPdo);
        
        $this->assertTrue($result);
    }
    
    /**
     * Test : Connexion échouée avec mauvais identifiants
     */
    public function testVerifierConnexionAvecMauvaisIdentifiants()
    {
        $login = new Login('alice.martin', 'mauvais_password');
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
        
        $this->mockStmt->expects($this->once())
            ->method('execute');
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false);
        
        $result = $login->verifierConnexion($this->mockPdo);
        
        $this->assertFalse($result);
    }
    
    // ========== TESTS POUR verifRole() ==========
    
    /**
     * Test : Récupération du rôle étudiant
     */
    public function testVerifRolePourEtudiant()
    {
        $login = new Login('alice.martin', 'securepass456');
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
        
        $this->mockStmt->expects($this->once())
            ->method('execute');
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(['fonction' => 'etudiant']);
        
        $role = $login->verifRole($this->mockPdo);
        
        $this->assertEquals('etudiant', $role);
    }
    
    /**
     * Test : verifRole retourne false avec mauvais identifiants
     */
    public function testVerifRoleAvecMauvaisIdentifiants()
    {
        $login = new Login('alice.martin', 'mauvais_password');
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
        
        $this->mockStmt->expects($this->once())
            ->method('execute');
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false);
        
        $role = $login->verifRole($this->mockPdo);
        
        $this->assertFalse($role);
    }
    
    // ========== TESTS POUR getName() ==========
    
    /**
     * Test : Récupération du nom complet avec identifiants valides
     */
    public function testGetNameAvecIdentifiantsValides()
    {
        $login = new Login('alice.martin', 'securepass456');
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
        
        $this->mockStmt->expects($this->once())
            ->method('execute');
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(['nom' => 'Martin', 'prenom' => 'Alice']);
        
        $name = $login->getName($this->mockPdo);
        
        $this->assertEquals('Alice Martin', $name);
    }
    
    /**
     * Test : getName retourne false avec mauvais identifiants
     */
    public function testGetNameAvecMauvaisIdentifiants()
    {
        $login = new Login('alice.martin', 'mauvais_password');
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
        
        $this->mockStmt->expects($this->once())
            ->method('execute');
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false);
        
        $name = $login->getName($this->mockPdo);
        
        $this->assertFalse($name);
    }
    
    // ========== TESTS POUR getIdUtilisateur() ==========
    
    /**
     * Test : Récupération de l'ID utilisateur
     */
    public function testGetIdUtilisateurAvecIdentifiantValide()
    {
        $login = new Login('alice.martin', 'securepass456');
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
        
        $this->mockStmt->expects($this->once())
            ->method('execute');
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(['idcompte' => 1]);
        
        $user = $login->getIdUtilisateur($this->mockPdo);
        
        $this->assertIsArray($user);
        $this->assertEquals(1, $user['idcompte']);
    }
    
    /**
     * Test : getIdUtilisateur retourne null avec identifiant inexistant
     */
    public function testGetIdUtilisateurAvecIdentifiantInexistant()
    {
        $login = new Login('utilisateur.inconnu', 'password');
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
        
        $this->mockStmt->expects($this->once())
            ->method('execute');
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false);
        
        $user = $login->getIdUtilisateur($this->mockPdo);
        
        $this->assertNull($user);
    }
    
    // ========== TESTS POUR getIdentifiantEtu() ==========
    
    /**
     * Test : Récupération de l'identifiant étudiant
     */
    public function testGetIdentifiantEtuPourEtudiant()
    {
        $login = new Login('alice.martin', 'securepass456');
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
        
        $this->mockStmt->expects($this->once())
            ->method('execute');
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(['identifiantetu' => 'ETU2024001']);
        
        $identifiant = $login->getIdentifiantEtu($this->mockPdo);
        
        $this->assertEquals('ETU2024001', $identifiant);
    }
    
    /**
     * Test : getIdentifiantEtu retourne null pour un non-étudiant
     */
    public function testGetIdentifiantEtuPourNonEtudiant()
    {
        $login = new Login('john.doe', 'x');
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
        
        $this->mockStmt->expects($this->once())
            ->method('execute');
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false);
        
        $identifiant = $login->getIdentifiantEtu($this->mockPdo);
        
        $this->assertNull($identifiant);
    }
    
    // ========== TESTS POUR getAllUserData() ==========
    
    /**
     * Test : Récupération de toutes les données utilisateur pour un étudiant
     */
    public function testGetAllUserDataPourEtudiant()
    {
        $login = new Login('alice.martin', 'securepass456');
        
        $expectedData = [
            'idcompte' => 1,
            'identifiantcompte' => 'alice.martin',
            'nom' => 'Martin',
            'prenom' => 'Alice',
            'fonction' => 'etudiant',
            'mot_de_passe' => 'securepass456',
            'identifiantetu' => 'ETU2024001'
        ];
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
        
        $this->mockStmt->expects($this->once())
            ->method('execute');
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);
        
        $data = $login->getAllUserData($this->mockPdo);
        
        $this->assertIsArray($data);
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
        
        $expectedData = [
            'idcompte' => 3,
            'identifiantcompte' => 'john.doe',
            'nom' => 'Doe',
            'prenom' => 'John',
            'fonction' => 'professeur',
            'mot_de_passe' => 'x',
            'identifiantetu' => null
        ];
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
        
        $this->mockStmt->expects($this->once())
            ->method('execute');
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);
        
        $data = $login->getAllUserData($this->mockPdo);
        
        $this->assertIsArray($data);
        $this->assertEquals('john.doe', $data['identifiantcompte']);
        $this->assertEquals('professeur', $data['fonction']);
        $this->assertNull($data['identifiantetu']);
    }
    
    /**
     * Test : getAllUserData retourne false avec mauvais identifiants
     */
    public function testGetAllUserDataAvecMauvaisIdentifiants()
    {
        $login = new Login('alice.martin', 'mauvais_password');
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
        
        $this->mockStmt->expects($this->once())
            ->method('execute');
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false);
        
        $data = $login->getAllUserData($this->mockPdo);
        
        $this->assertFalse($data);
    }
}