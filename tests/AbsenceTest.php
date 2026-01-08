<?php

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use src\Models\Absence;
use PDO;
use PDOStatement;

/**
 * Tests unitaires pour le modèle Absence
 * Utilise des mocks PDO pour isoler les tests de la base de données
 * Tous les tests sont basés sur l'étudiante Alice Martin (idCompte: 4)
 */
class AbsenceTest extends TestCase
{
    private $mockPdo;
    private $mockStmt;
    private $absence;
    
    // Données de test pour Alice Martin
    private const ID_ETUDIANT = 4;
    private const IDENTIFIANT_ETU = 'alice.martin';
    private const NOM = 'Martin';
    private const PRENOM = 'Alice';

    /**
     * Initialisation avant chaque test
     * Crée les mocks PDO et PDOStatement nécessaires
     */
    protected function setUp(): void
    {
        $this->mockPdo = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);
        $this->absence = new Absence($this->mockPdo);
    }

    /**
     * Teste l'affectation de la date de début d'absence
     */
    public function testSetDateDebut(): void
    {
        $date = '2026-01-07 08:00:00';
        $this->absence->setDateDebut($date);
        
        $reflection = new \ReflectionClass($this->absence);
        $property = $reflection->getProperty('dateDebut');
        $property->setAccessible(true);
        
        $this->assertEquals($date, $property->getValue($this->absence));
    }

    /**
     * Teste l'affectation de la date de fin d'absence
     */
    public function testSetDateFin(): void
    {
        $date = '2026-01-07 10:00:00';
        $this->absence->setDateFin($date);
        
        $reflection = new \ReflectionClass($this->absence);
        $property = $reflection->getProperty('dateFin');
        $property->setAccessible(true);
        
        $this->assertEquals($date, $property->getValue($this->absence));
    }

    /**
     * Teste l'affectation du motif d'absence
     */
    public function testSetMotif(): void
    {
        $motif = 'Rendez-vous médical';
        $this->absence->setMotif($motif);
        
        $reflection = new \ReflectionClass($this->absence);
        $property = $reflection->getProperty('motif');
        $property->setAccessible(true);
        
        $this->assertEquals($motif, $property->getValue($this->absence));
    }

    /**
     * Teste l'affectation du statut justifié (true)
     */
    public function testSetJustifieTrue(): void
    {
        $this->absence->setJustifie(true);
        
        $reflection = new \ReflectionClass($this->absence);
        $property = $reflection->getProperty('justifie');
        $property->setAccessible(true);
        
        $this->assertTrue($property->getValue($this->absence));
    }

    /**
     * Teste l'affectation du statut justifié à null (en attente)
     */
    public function testSetJustifieNull(): void
    {
        $this->absence->setJustifie(null);
        
        $reflection = new \ReflectionClass($this->absence);
        $property = $reflection->getProperty('justifie');
        $property->setAccessible(true);
        
        $this->assertNull($property->getValue($this->absence));
    }

    /**
     * Teste l'ajout d'une nouvelle absence pour Alice Martin
     * Vérifie que l'ID de l'absence insérée est bien retourné
     */
    public function testAjouterAbsence(): void
    {
        $this->absence->setIdCours(1);
        $this->absence->setIdEtudiant(self::ID_ETUDIANT);
        $this->absence->setDateDebut('2026-01-07 08:00:00');
        $this->absence->setDateFin('2026-01-07 10:00:00');
        $this->absence->setMotif('Rendez-vous médical');
        $this->absence->setJustifie(null);
        $this->absence->setUriJustificatif('/uploads/certificat_alice.pdf');

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->exactly(7))
            ->method('bindValue');

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('101');

        $result = $this->absence->ajouterAbsence();
        
        $this->assertEquals('101', $result);
    }

    /**
     * Teste la gestion d'erreur lors de l'ajout d'une absence
     * Vérifie qu'une exception est levée en cas d'erreur PDO
     */
    public function testAjouterAbsenceAvecErreur(): void
    {
        $this->absence->setIdCours(1);
        $this->absence->setIdEtudiant(self::ID_ETUDIANT);
        $this->absence->setDateDebut('2026-01-07 08:00:00');
        $this->absence->setDateFin('2026-01-07 10:00:00');
        $this->absence->setMotif('Test erreur');
        $this->absence->setJustifie(null);
        $this->absence->setUriJustificatif(null);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->will($this->throwException(new \PDOException('Erreur de connexion')));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Erreur BDD: Erreur de connexion');

        $this->absence->ajouterAbsence();
    }

    /**
     * Teste la validation d'une absence pour Alice Martin
     * Vérifie que le statut justifié passe à true
     */
    public function testJustifierAbsence(): void
    {
        $idAbsence = 5;

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':idAbsence' => $idAbsence])
            ->willReturn(true);

        $result = $this->absence->justifierAbsence($idAbsence);
        
        $this->assertTrue($result);
    }

    /**
     * Teste l'échec de validation d'une absence inexistante
     */
    public function testJustifierAbsenceEchec(): void
    {
        $idAbsence = 999;

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->will($this->throwException(new \PDOException('Absence introuvable')));

        $result = $this->absence->justifierAbsence($idAbsence);
        
        $this->assertFalse($result);
    }

    /**
     * Teste la validation d'une absence (acceptation)
     */
    public function testUpdateJustifieValidation(): void
    {
        $idAbsence = 10;

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->exactly(2))
            ->method('bindValue');

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $result = $this->absence->updateJustifie($idAbsence, true);
        
        $this->assertTrue($result);
    }

    /**
     * Teste le refus d'une absence avec possibilité de resoumission
     * Vérifie que la raison et le type de refus sont enregistrés
     */
    public function testUpdateJustifieRefusAvecResoumission(): void
    {
        $idAbsence = 11;
        $raisonRefus = 'Le justificatif médical n\'est pas conforme. Veuillez fournir un certificat médical original.';
        $typeRefus = 'ressoumission';

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->exactly(4))
            ->method('bindValue');

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $result = $this->absence->updateJustifie($idAbsence, false, $raisonRefus, $typeRefus);
        
        $this->assertTrue($result);
    }

    /**
     * Teste la mise en révision d'une absence
     * Utilisé quand le responsable demande un justificatif supplémentaire
     */
    public function testSetEnRevision(): void
    {
        $idAbsence = 15;

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->exactly(2))
            ->method('bindValue');

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $result = $this->absence->setEnRevision($idAbsence, true);
        
        $this->assertTrue($result);
    }

    /**
     * Teste la resoumission d'une absence refusée
     * Permet à Alice Martin de modifier son motif et son justificatif
     */
    public function testResoumettreAbsence(): void
    {
        $idAbsence = 20;
        $nouveauMotif = 'Consultation spécialisée en ophtalmologie';
        $nouvelleUri = '/uploads/certificat_ophtalmologie_alice.pdf';

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->exactly(3))
            ->method('bindValue');

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $result = $this->absence->resoumettre($idAbsence, $nouveauMotif, $nouvelleUri);
        
        $this->assertTrue($result);
    }

    /**
     * Teste la récupération de toutes les absences
     * Inclut les données de jointure (étudiant, cours, ressource)
     */
    public function testGetAll(): void
    {
        $expectedData = [
            [
                'idabsence' => 1,
                'identifiantEtu' => self::IDENTIFIANT_ETU,
                'cours_type' => 'CM',
                'ressource_nom' => 'Mathématiques',
                'nomCompte' => self::NOM,
                'prenomCompte' => self::PRENOM,
                'date_debut' => '2026-01-07 08:00:00',
                'date_fin' => '2026-01-07 10:00:00',
                'motif' => 'Rendez-vous médical',
                'justifie' => null
            ]
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute');

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        $result = $this->absence->getAll();
        
        $this->assertCount(1, $result);
        $this->assertEquals(self::IDENTIFIANT_ETU, $result[0]['identifiantEtu']);
        $this->assertEquals(self::NOM, $result[0]['nomCompte']);
        $this->assertEquals(self::PRENOM, $result[0]['prenomCompte']);
    }

    /**
     * Teste la récupération de la durée d'une absence
     * Retourne les dates de début et de fin
     */
    public function testGetDuree(): void
    {
        $idAbsence = 25;
        $expectedData = [
            'date_debut' => '2026-01-09 08:00:00',
            'date_fin' => '2026-01-10 18:00:00'
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':idAbsence' => $idAbsence]);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        $result = $this->absence->getDuree($idAbsence);
        
        $this->assertEquals('2026-01-09 08:00:00', $result['date_debut']);
        $this->assertEquals('2026-01-10 18:00:00', $result['date_fin']);
    }

    /**
     * Teste la récupération d'une absence par son ID
     * Inclut toutes les données jointes
     */
    public function testGetById(): void
    {
        $idAbsence = 30;
        $expectedData = [
            'idabsence' => 30,
            'identifiantEtu' => self::IDENTIFIANT_ETU,
            'nomCompte' => self::NOM,
            'prenomCompte' => self::PRENOM,
            'identifiantCompte' => self::IDENTIFIANT_ETU,
            'cours_type' => 'CM',
            'ressource_nom' => 'Algorithmique',
            'date_debut' => '2026-01-05 14:00:00',
            'date_fin' => '2026-01-05 16:00:00',
            'motif' => 'Stage en entreprise',
            'justifie' => true,
            'urijustificatif' => '/uploads/convention_stage_alice.pdf'
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':idAbsence' => $idAbsence]);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        $result = $this->absence->getById($idAbsence);
        
        $this->assertEquals(30, $result['idabsence']);
        $this->assertEquals(self::IDENTIFIANT_ETU, $result['identifiantEtu']);
        $this->assertEquals(self::NOM, $result['nomCompte']);
        $this->assertEquals(self::PRENOM, $result['prenomCompte']);
    }

    /**
     * Teste la récupération de toutes les absences d'Alice Martin
     * Vérifie que les absences sont triées par date décroissante
     */
    public function testGetByStudentIdentifiant(): void
    {
        $expectedData = [
            [
                'idabsence' => 35,
                'identifiantEtu' => self::IDENTIFIANT_ETU,
                'nomCompte' => self::NOM,
                'prenomCompte' => self::PRENOM,
                'cours_type' => 'TD',
                'ressource_nom' => 'Base de données',
                'date_debut' => '2026-01-10 10:00:00',
                'date_fin' => '2026-01-10 12:00:00',
                'motif' => 'Grippe',
                'justifie' => null
            ],
            [
                'idabsence' => 36,
                'identifiantEtu' => self::IDENTIFIANT_ETU,
                'nomCompte' => self::NOM,
                'prenomCompte' => self::PRENOM,
                'cours_type' => 'CM',
                'ressource_nom' => 'Réseaux',
                'date_debut' => '2026-01-03 08:00:00',
                'date_fin' => '2026-01-03 10:00:00',
                'motif' => 'Rendez-vous dentiste',
                'justifie' => true
            ]
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':identifiantEtu' => self::IDENTIFIANT_ETU]);

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        $result = $this->absence->getByStudentIdentifiant(self::IDENTIFIANT_ETU);
        
        $this->assertCount(2, $result);
        $this->assertEquals(self::IDENTIFIANT_ETU, $result[0]['identifiantEtu']);
        $this->assertEquals(self::NOM, $result[0]['nomCompte']);
        $this->assertEquals(self::PRENOM, $result[0]['prenomCompte']);
    }

    /**
     * Teste le comptage des périodes d'absences en attente
     * Les absences consécutives (moins de 24h) sont regroupées
     */
    public function testCountEnAttenteAvecAbsencesConsecutives(): void
    {
        $absencesData = [
            [
                'idabsence' => 1,
                'idetudiant' => self::ID_ETUDIANT,
                'date_debut' => '2026-01-07 08:00:00',
                'date_fin' => '2026-01-07 10:00:00'
            ],
            [
                'idabsence' => 2,
                'idetudiant' => self::ID_ETUDIANT,
                'date_debut' => '2026-01-07 14:00:00',
                'date_fin' => '2026-01-07 16:00:00'
            ]
        ];

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute');

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($absencesData);

        $result = $this->absence->countEnAttente();
        
        // Les 2 absences sont dans les 24h, donc 1 seule période
        $this->assertEquals(1, $result);
    }

    /**
     * Teste le comptage avec aucune absence en attente
     */
    public function testCountEnAttenteAvecAucuneAbsence(): void
    {
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute');

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([]);

        $result = $this->absence->countEnAttente();
        
        $this->assertEquals(0, $result);
    }

    /**
     * Teste la récupération des absences quand l'étudiant n'en a aucune
     */
    public function testGetByStudentIdentifiantSansAbsence(): void
    {
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':identifiantEtu' => self::IDENTIFIANT_ETU]);

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([]);

        $result = $this->absence->getByStudentIdentifiant(self::IDENTIFIANT_ETU);
        
        $this->assertCount(0, $result);
        $this->assertIsArray($result);
    }
}
