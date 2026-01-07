<?php

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use src\Models\Absence;
use PDO;
use PDOStatement;

class AbsenceTest extends TestCase
{
    private $mockPdo;
    private $mockStmt;
    private $absence;

    protected function setUp(): void
    {
        // Créer les mocks
        $this->mockPdo = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);
        
        // Initialiser l'objet Absence avec le mock PDO
        $this->absence = new Absence($this->mockPdo);
    }

    public function testSetDateDebut(): void
    {
        $date = '2026-01-07 08:00:00';
        $this->absence->setDateDebut($date);
        
        // Utiliser reflection pour vérifier la propriété privée
        $reflection = new \ReflectionClass($this->absence);
        $property = $reflection->getProperty('dateDebut');
        $property->setAccessible(true);
        
        $this->assertEquals($date, $property->getValue($this->absence));
    }

    public function testSetDateFin(): void
    {
        $date = '2026-01-07 10:00:00';
        $this->absence->setDateFin($date);
        
        $reflection = new \ReflectionClass($this->absence);
        $property = $reflection->getProperty('dateFin');
        $property->setAccessible(true);
        
        $this->assertEquals($date, $property->getValue($this->absence));
    }

    public function testSetMotif(): void
    {
        $motif = 'Maladie';
        $this->absence->setMotif($motif);
        
        $reflection = new \ReflectionClass($this->absence);
        $property = $reflection->getProperty('motif');
        $property->setAccessible(true);
        
        $this->assertEquals($motif, $property->getValue($this->absence));
    }

    public function testSetJustifie(): void
    {
        $this->absence->setJustifie(true);
        
        $reflection = new \ReflectionClass($this->absence);
        $property = $reflection->getProperty('justifie');
        $property->setAccessible(true);
        
        $this->assertTrue($property->getValue($this->absence));
    }

    public function testSetJustifieNull(): void
    {
        $this->absence->setJustifie(null);
        
        $reflection = new \ReflectionClass($this->absence);
        $property = $reflection->getProperty('justifie');
        $property->setAccessible(true);
        
        $this->assertNull($property->getValue($this->absence));
    }

    public function testAjouterAbsencePourAliceMartin(): void
    {
        // Alice Martin (idCompte: 4, etudiant)
        $this->absence->setIdCours(1);
        $this->absence->setIdEtudiant(4);
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

    public function testAjouterAbsencePourOceaneFournier(): void
    {
        // Oceane Fournier (idCompte: 20, etudiant)
        $this->absence->setIdCours(2);
        $this->absence->setIdEtudiant(20);
        $this->absence->setDateDebut('2026-01-08 14:00:00');
        $this->absence->setDateFin('2026-01-08 16:00:00');
        $this->absence->setMotif('Problème de transport');
        $this->absence->setJustifie(false);
        $this->absence->setUriJustificatif(null);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('102');

        $result = $this->absence->ajouterAbsence();
        
        $this->assertEquals('102', $result);
    }

    public function testAjouterAbsenceAvecErreur(): void
    {
        $this->absence->setIdCours(1);
        $this->absence->setIdEtudiant(12); // Maxime Garcia
        $this->absence->setDateDebut('2026-01-07 08:00:00');
        $this->absence->setDateFin('2026-01-07 10:00:00');
        $this->absence->setMotif('Test');
        $this->absence->setJustifie(null);
        $this->absence->setUriJustificatif(null);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->will($this->throwException(new \PDOException('Erreur de connexion')));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Erreur BDD: Erreur de connexion');

        $this->absence->ajouterAbsence();
    }

    public function testJustifierAbsenceDeLeaRousseau(): void
    {
        // Absence de Lea Rousseau (idCompte: 9)
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

    public function testJustifierAbsenceEchec(): void
    {
        $idAbsence = 99;

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->will($this->throwException(new \PDOException('Absence introuvable')));

        $result = $this->absence->justifierAbsence($idAbsence);
        
        $this->assertFalse($result);
    }

    public function testUpdateJustifieValidationPourChloe(): void
    {
        // Valider l'absence de Chloe Leroux (idCompte: 15)
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

    public function testUpdateJustifieRefusPourNathanGirard(): void
    {
        // Refuser l'absence de Nathan Girard (idCompte: 16) avec possibilité de resoumission
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

    public function testSetEnRevisionPourDylanLegrand(): void
    {
        // Mettre en révision l'absence de Dylan Legrand (idCompte: 21)
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

    public function testResoumettreAbsenceManonMercier(): void
    {
        // Manon Mercier (idCompte: 18) resoumets son absence refusée
        $idAbsence = 20;
        $nouveauMotif = 'Consultation spécialisée en ophtalmologie';
        $nouvelleUri = '/uploads/certificat_ophtalmologie_manon.pdf';

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

    public function testGetAllAvecDonneesEtudiants(): void
    {
        $expectedData = [
            [
                'idabsence' => 1,
                'identifiantEtu' => 'alice.martin',
                'cours_type' => 'CM',
                'ressource_nom' => 'Mathématiques',
                'nomCompte' => 'Martin',
                'prenomCompte' => 'Alice',
                'date_debut' => '2026-01-07 08:00:00',
                'date_fin' => '2026-01-07 10:00:00',
                'motif' => 'Rendez-vous médical',
                'justifie' => null
            ],
            [
                'idabsence' => 2,
                'identifiantEtu' => 'oceane.fournier',
                'cours_type' => 'TD',
                'ressource_nom' => 'Informatique',
                'nomCompte' => 'Fournier',
                'prenomCompte' => 'Oceane',
                'date_debut' => '2026-01-08 14:00:00',
                'date_fin' => '2026-01-08 16:00:00',
                'motif' => 'Problème de transport',
                'justifie' => false
            ],
            [
                'idabsence' => 3,
                'identifiantEtu' => 'maxime.garcia',
                'cours_type' => 'TP',
                'ressource_nom' => 'Physique',
                'nomCompte' => 'Garcia',
                'prenomCompte' => 'Maxime',
                'date_debut' => '2026-01-06 10:00:00',
                'date_fin' => '2026-01-06 12:00:00',
                'motif' => 'Maladie',
                'justifie' => true
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
        
        $this->assertCount(3, $result);
        $this->assertEquals('alice.martin', $result[0]['identifiantEtu']);
        $this->assertEquals('Martin', $result[0]['nomCompte']);
        $this->assertEquals('Alice', $result[0]['prenomCompte']);
    }

    public function testGetDureePourAbsenceAntoineBlanc(): void
    {
        // Antoine Blanc (idCompte: 6)
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

    public function testGetByIdPourKevinBertrand(): void
    {
        // Kevin Bertrand (idCompte: 19)
        $idAbsence = 30;
        $expectedData = [
            'idabsence' => 30,
            'identifiantEtu' => 'kevin.bertrand',
            'nomCompte' => 'Bertrand',
            'prenomCompte' => 'Kevin',
            'identifiantCompte' => 'kevin.bertrand',
            'cours_type' => 'CM',
            'ressource_nom' => 'Algorithmique',
            'date_debut' => '2026-01-05 14:00:00',
            'date_fin' => '2026-01-05 16:00:00',
            'motif' => 'Stage en entreprise',
            'justifie' => true,
            'urijustificatif' => '/uploads/convention_stage_kevin.pdf'
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
        $this->assertEquals('kevin.bertrand', $result['identifiantEtu']);
        $this->assertEquals('Bertrand', $result['nomCompte']);
        $this->assertEquals('Kevin', $result['prenomCompte']);
    }

    public function testGetByStudentIdentifiantPourLeaRousseau(): void
    {
        // Lea Rousseau (idCompte: 9)
        $identifiantEtu = 'lea.rousseau';
        $expectedData = [
            [
                'idabsence' => 35,
                'identifiantEtu' => 'lea.rousseau',
                'nomCompte' => 'Rousseau',
                'prenomCompte' => 'Lea',
                'cours_type' => 'TD',
                'ressource_nom' => 'Base de données',
                'date_debut' => '2026-01-10 10:00:00',
                'date_fin' => '2026-01-10 12:00:00',
                'motif' => 'Grippe',
                'justifie' => null
            ],
            [
                'idabsence' => 36,
                'identifiantEtu' => 'lea.rousseau',
                'nomCompte' => 'Rousseau',
                'prenomCompte' => 'Lea',
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
            ->with([':identifiantEtu' => $identifiantEtu]);

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        $result = $this->absence->getByStudentIdentifiant($identifiantEtu);
        
        $this->assertCount(2, $result);
        $this->assertEquals('lea.rousseau', $result[0]['identifiantEtu']);
        $this->assertEquals('Rousseau', $result[0]['nomCompte']);
        $this->assertEquals('Lea', $result[0]['prenomCompte']);
    }

    public function testCountEnAttenteAvecAbsencesConsecutivesPourieursEtudiants(): void
    {
        // Simuler des absences en attente pour plusieurs étudiants
        // Alice Martin: 2 absences consécutives (même journée)
        // Chloe Leroux: 1 absence isolée
        // Maxime Garcia: 2 absences non consécutives
        $absencesData = [
            // Alice Martin - 2 absences consécutives (< 24h)
            [
                'idabsence' => 1,
                'idetudiant' => 4,
                'date_debut' => '2026-01-07 08:00:00',
                'date_fin' => '2026-01-07 10:00:00'
            ],
            [
                'idabsence' => 2,
                'idetudiant' => 4,
                'date_debut' => '2026-01-07 14:00:00',
                'date_fin' => '2026-01-07 16:00:00'
            ],
            // Chloe Leroux - 1 absence isolée
            [
                'idabsence' => 3,
                'idetudiant' => 15,
                'date_debut' => '2026-01-08 10:00:00',
                'date_fin' => '2026-01-08 12:00:00'
            ],
            // Maxime Garcia - 2 absences non consécutives (> 24h)
            [
                'idabsence' => 4,
                'idetudiant' => 12,
                'date_debut' => '2026-01-05 08:00:00',
                'date_fin' => '2026-01-05 10:00:00'
            ],
            [
                'idabsence' => 5,
                'idetudiant' => 12,
                'date_debut' => '2026-01-08 14:00:00',
                'date_fin' => '2026-01-08 16:00:00'
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
        
        // Alice: 1 période (2 absences consécutives)
        // Chloe: 1 période (1 absence)
        // Maxime: 2 périodes (2 absences non consécutives)
        // Total: 4 périodes
        $this->assertEquals(4, $result);
    }

    public function testCountEnAttenteAvecAbsencesConsecutivesMemejournee(): void
    {
        // Océane Fournier avec 3 absences la même journée
        $absencesData = [
            [
                'idabsence' => 1,
                'idetudiant' => 20,
                'date_debut' => '2026-01-07 08:00:00',
                'date_fin' => '2026-01-07 10:00:00'
            ],
            [
                'idabsence' => 2,
                'idetudiant' => 20,
                'date_debut' => '2026-01-07 10:00:00',
                'date_fin' => '2026-01-07 12:00:00'
            ],
            [
                'idabsence' => 3,
                'idetudiant' => 20,
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
        
        // Les 3 absences sont dans les 24h, donc 1 seule période
        $this->assertEquals(1, $result);
    }

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

    public function testGetByStudentIdentifiantEtudiantSansAbsence(): void
    {
        // Antoine Blanc n'a aucune absence
        $identifiantEtu = 'antoine.blanc';

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':identifiantEtu' => $identifiantEtu]);

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([]);

        $result = $this->absence->getByStudentIdentifiant($identifiantEtu);
        
        $this->assertCount(0, $result);
        $this->assertIsArray($result);
    }
}