<?php
/* tests de 3 fonctionnalités basiques de notre application avec PHPUnit (dont une fonctionnalité principale) */

use PHPUnit\Framework\TestCase;
use src\Models\GestionCSV;

require_once __DIR__ . '/../src/Models/Absence.php';
require_once __DIR__ . '/../src/Database/Database.php';
require_once __DIR__ . '/../src/Models/GestionCSV.php';


// Classe de test pour la gestion des fichiers CSV

class GestionCSVTest extends TestCase
{
    private GestionCSV $gestionCSV;

    protected function setUp(): void
    {
        $this->gestionCSV = new GestionCSV();
    }

    public function testGetAllDataReturnsArray(): void
    {
        $filePath = __DIR__ . '/../data/CSV/BUT2-251204-251217_nouveau.CSV';
        $result = $this->gestionCSV->getAllData($filePath);
        $this->assertIsArray($result, 'getAllData devrait retourner un tableau');
    }

    public function testCheckIfValidCsvReturnsBoolean(): void
    {
        $filePath = __DIR__ . '/../data/CSV/BUT2-251204-251217_nouveau.CSV';
        $result = $this->gestionCSV->check_if_valid_csv($filePath);
        $this->assertIsBool($result, 'check_if_valid_csv devrait retourner un booléen');
    }

    public function testCheckIfValidCsvWithInvalidFile(): void
    {
        $invalidFilePath = __DIR__ . '/../data/CSV/invalid_file.CSV';
        $result = $this->gestionCSV->check_if_valid_csv($invalidFilePath);
        $this->assertFalse($result, 'check_if_valid_csv devrait retourner false pour un fichier invalide');
    }
}

// Classe de test pour l'ajout d'une absence dans la BDD (fonctionnalité principale)

class AbsenceManagementTest extends TestCase
{
    private \src\Models\Absence $absenceModel;
    private \PDO $pdo;

    protected function setUp(): void
    {
        require __DIR__ . '/../src/Database/Database.php';
        require __DIR__ . '/../src/Models/Absence.php';

        $db = new \src\Database\Database();
        $this->pdo = $db->getConnection();
        $this->absenceModel = new \src\Models\Absence($this->pdo);
    }

    public function testAddAbsence(): void
    {
        $data = [
            'date_debut' => '2024-07-01 09:00:00',
            'date_fin' => '2024-07-01 17:00:00',
            'motif' => 'Rendez-vous médical',
            'cours_id' => 1,
            'compte_id' => 1,
            'justifie' => 0
        ];

        $this->absenceModel->setDateDebut($data['date_debut']);
        $this->absenceModel->setDateFin($data['date_fin']);
        $this->absenceModel->setMotif($data['motif']);
        $this->absenceModel->setIdCours($data['cours_id']);
        $this->absenceModel->setIdEtudiant($data['compte_id']);
        $this->absenceModel->setJustifie($data['justifie']);

        $result = $this->absenceModel->ajouterAbsence($this->pdo);
        $this->assertTrue($result, 'Ajout d\'absence devrait réussir');
    }
}

// Classe de test pour la validation d'email (fonctionnalité sans base de données)

class EmailValidationTest extends TestCase
{
    public function testValidEmail(): void
    {
        $email = 'selyane.khentache@uphf.fr';
        $isValid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $this->assertTrue($isValid, 'L\'email valide devrait être reconnu comme tel');
    }

    public function testInvalidEmail(): void
    {
        $email = 'invalid-email';
        $isValid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $this->assertFalse($isValid, 'L\'email invalide devrait être rejeté');
    }
}

// Pour exécuter les tests, utilisez la commande : phpunit --bootstrap vendor/autoload.php tests_unit/phptest.php