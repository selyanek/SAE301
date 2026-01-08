<?php

use PHPUnit\Framework\TestCase;
use src\Models\Absence;
use src\Database\Database;

class AbsenceManagementTest extends TestCase
{
    private PDO $pdo;
    private Absence $absence;

    /**
     * Initialisation avant chaque test
     * Établit une connexion à la base de données et démarre une transaction
     */
    protected function setUp(): void
    {
        $db = new Database();
        $this->pdo = $db->getConnection();

        // Démarre une transaction pour isoler les tests
        $this->pdo->beginTransaction();

        $this->absence = new Absence($this->pdo);
    }

    /**
     * Nettoyage après chaque test
     * Annule toutes les modifications effectuées via rollback
     */
    protected function tearDown(): void
    {
        $this->pdo->rollBack();
    }

    /**
     * Teste l'ajout d'une nouvelle absence dans la base de données
     * Vérifie que la méthode ajouterAbsence retourne un ID valide
     */
    public function testAddAbsenceReturnsValidId(): void
    {
        // Configuration des propriétés de l'absence
        $this->absence->setDateDebut('2024-07-01 09:00:00');
        $this->absence->setDateFin('2024-07-01 17:00:00');
        $this->absence->setMotif('Rendez-vous médical');
        $this->absence->setIdCours(1);
        $this->absence->setIdEtudiant(1);
        $this->absence->setJustifie(null);
        $this->absence->setUriJustificatif('justificatif_test.pdf'); // AJOUT ICI

        // Insertion de l'absence
        $id = $this->absence->ajouterAbsence();

        // Vérification que l'ID retourné est valide
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
    }

    /**
     * Teste la récupération d'une absence après insertion
     * Vérifie que les données insérées correspondent aux valeurs définies
     */
    public function testGetAbsenceAfterInsert(): void
    {
        // Configuration des propriétés de l'absence
        $this->absence->setDateDebut('2024-07-01 09:00:00');
        $this->absence->setDateFin('2024-07-01 17:00:00');
        $this->absence->setMotif('Test motif');
        $this->absence->setIdCours(1);
        $this->absence->setIdEtudiant(1);
        $this->absence->setJustifie(null);
        $this->absence->setUriJustificatif('test.pdf'); // AJOUT ICI
        
        $id = $this->absence->ajouterAbsence();

        // Récupération de l'absence insérée
        $data = $this->absence->getById($id);

        // Vérifications des données récupérées
        $this->assertIsArray($data);
        $this->assertEquals('Test motif', $data['motif']);
    }
}
