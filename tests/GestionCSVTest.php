<?php

use PHPUnit\Framework\TestCase;
use src\Models\GestionCSV;

class GestionCSVTest extends TestCase
{
    private GestionCSV $gestionCSV;
    private string $testCsvFile;

    /**
     * Initialisation avant chaque test
     * Crée une instance de GestionCSV et un fichier CSV temporaire valide
     */
    protected function setUp(): void
    {
        $this->gestionCSV = new GestionCSV();

        // Création d'un fichier CSV temporaire avec extension .csv
        $this->testCsvFile = sys_get_temp_dir() . '/test_' . uniqid() . '.csv';
        
        // Contenu CSV avec séparateur point-virgule et deux lignes de données
        file_put_contents($this->testCsvFile, "nom;prenom\nDoe;John\nMartin;Alice");
    }

    /**
     * Nettoyage après chaque test
     * Supprime le fichier CSV temporaire s'il existe
     */
    protected function tearDown(): void
    {
        if (file_exists($this->testCsvFile)) {
            unlink($this->testCsvFile);
        }
    }

    /**
     * Teste que getAllData retourne un tableau non vide
     * Vérifie la lecture et le parsing du fichier CSV
     */
    public function testGetAllDataReturnsArray(): void
    {
        $result = $this->gestionCSV->getAllData($this->testCsvFile);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    /**
     * Teste la validation d'un fichier CSV valide
     * Vérifie que check_if_valid_csv accepte un fichier avec extension .csv
     */
    public function testCheckIfValidCsvReturnsTrue(): void
    {
        $result = $this->gestionCSV->check_if_valid_csv($this->testCsvFile);
        
        // Affichage de debug en cas d'échec
        if (!$result) {
            echo "\nFichier testé : " . $this->testCsvFile;
            echo "\nFichier existe : " . (file_exists($this->testCsvFile) ? 'OUI' : 'NON');
            echo "\nContenu : " . file_get_contents($this->testCsvFile);
        }
        
        $this->assertTrue($result, "Le fichier CSV devrait être reconnu comme valide");
    }

    /**
     * Teste la validation avec un fichier inexistant
     * Vérifie que check_if_valid_csv retourne false pour un fichier absent
     */
    public function testCheckIfValidCsvWithInvalidFile(): void
    {
        $result = $this->gestionCSV->check_if_valid_csv('fichier_inexistant.csv');
        $this->assertFalse($result);
    }

    /**
     * Teste la validation avec un fichier sans extension .csv
     * Vérifie que check_if_valid_csv rejette les fichiers .txt
     */
    public function testCheckIfValidCsvWithNonCsvExtension(): void
    {
        // Création d'un fichier temporaire avec extension .txt
        $nonCsvFile = sys_get_temp_dir() . '/test_' . uniqid() . '.txt';
        file_put_contents($nonCsvFile, "nom;prenom\nDoe;John");
        
        $result = $this->gestionCSV->check_if_valid_csv($nonCsvFile);
        
        // Nettoyage du fichier temporaire
        unlink($nonCsvFile);
        
        $this->assertFalse($result, "Un fichier .txt ne devrait pas être considéré comme CSV valide");
    }
}
