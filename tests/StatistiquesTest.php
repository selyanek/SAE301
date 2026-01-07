<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use src\Models\Statistiques;

/**
 * Tests unitaires pour le modèle Statistiques
 * 
 * Pour lancer les tests : ./vendor/bin/phpunit
 */
class StatistiquesTest extends TestCase
{
    private Statistiques $stats;

    /**
     * Données de test réutilisables
     */
    private array $absencesTest = [];

    protected function setUp(): void
    {
        // Créer instance sans PDO (on injecte les données manuellement)
        $this->stats = new Statistiques(null);
        
        // Données de test variées
        $this->absencesTest = [
            [
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'identifiant' => 'ETU001',
                'diplome' => 'BUT1 Informatique',
                'date' => '15/01/2024',
                'heure' => '8H00',
                'duree' => '2h00',
                'type' => 'CM',
                'matiere' => 'Mathématiques',
                'justification' => 'Absence justifiée',
                'motif' => 'Maladie',
                'groupe' => 'G1',
                'prof' => 'Martin Pierre',
                'controle' => 'Non',
                'annee_but' => 'BUT1'
            ],
            [
                'nom' => 'Martin',
                'prenom' => 'Sophie',
                'identifiant' => 'ETU002',
                'diplome' => 'BUT2 Informatique',
                'date' => '16/01/2024',
                'heure' => '10H00',
                'duree' => '1h30',
                'type' => 'TD',
                'matiere' => 'Programmation',
                'justification' => 'Non justifié',
                'motif' => '',
                'groupe' => 'G2',
                'prof' => 'Durand Marie',
                'controle' => 'Non',
                'annee_but' => 'BUT2'
            ],
            [
                'nom' => 'Bernard',
                'prenom' => 'Luc',
                'identifiant' => 'ETU003',
                'diplome' => 'BUT1 Informatique',
                'date' => '17/01/2024',
                'heure' => '14H30',
                'duree' => '2h00',
                'type' => 'DS',
                'matiere' => 'Mathématiques',
                'justification' => 'En attente',
                'motif' => '',
                'groupe' => 'G1',
                'prof' => 'Martin Pierre',
                'controle' => 'Oui',
                'annee_but' => 'BUT1'
            ],
            [
                'nom' => 'Petit',
                'prenom' => 'Emma',
                'identifiant' => 'ETU004',
                'diplome' => 'BUT3 Informatique',
                'date' => '18/02/2024',
                'heure' => '16H00',
                'duree' => '1h30',
                'type' => 'TP',
                'matiere' => 'Base de données',
                'justification' => 'Absence justifiée',
                'motif' => 'RDV médical',
                'groupe' => 'G3',
                'prof' => 'Leroy Paul',
                'controle' => 'Oui',
                'annee_but' => 'BUT3'
            ],
            [
                'nom' => 'Durand',
                'prenom' => 'Alex',
                'identifiant' => 'ETU005',
                'diplome' => 'BUT2 Informatique',
                'date' => '20/02/2024',
                'heure' => '11H30',
                'duree' => '1h00',
                'type' => 'CM',
                'matiere' => 'Programmation',
                'justification' => 'Non justifié',
                'motif' => '',
                'groupe' => 'G2',
                'prof' => 'Durand Marie',
                'controle' => 'Non',
                'annee_but' => 'BUT2'
            ]
        ];
    }

    // ==========================================
    // TESTS : calculerStatistiquesGlobales()
    // ==========================================

    public function testCalculerStatistiquesGlobalesAvecDonnees(): void
    {
        $this->stats->setAbsences($this->absencesTest);
        $result = $this->stats->calculerStatistiquesGlobales();

        $this->assertEquals(5, $result['total'], 'Total devrait être 5');
        $this->assertEquals(2, $result['justifiees'], 'Justifiées devrait être 2');
        $this->assertEquals(3, $result['non_justifiees'], 'Non justifiées devrait être 3');
        $this->assertEquals(2, $result['evaluations'], 'Évaluations devrait être 2 (1 DS + 1 contrôle)');
    }

    public function testCalculerStatistiquesGlobalesSansDonnees(): void
    {
        // Pas de setAbsences, donc tableau vide
        $result = $this->stats->calculerStatistiquesGlobales();

        $this->assertEquals(0, $result['total']);
        $this->assertEquals(0, $result['justifiees']);
        $this->assertEquals(0, $result['non_justifiees']);
        $this->assertEquals(0, $result['evaluations']);
    }

    public function testCalculerStatistiquesGlobalesAvecTableauVide(): void
    {
        $this->stats->setAbsences([]);
        $result = $this->stats->calculerStatistiquesGlobales();

        $this->assertIsArray($result);
        $this->assertEquals(0, $result['total']);
    }

    // ==========================================
    // TESTS : getRepartitionParType()
    // ==========================================

    public function testGetRepartitionParType(): void
    {
        $this->stats->setAbsences($this->absencesTest);
        $result = $this->stats->getRepartitionParType();

        $this->assertIsArray($result);
        $this->assertEquals(2, $result['CM'], 'CM devrait avoir 2 absences');
        $this->assertEquals(1, $result['TD'], 'TD devrait avoir 1 absence');
        $this->assertEquals(1, $result['DS'], 'DS devrait avoir 1 absence');
        $this->assertEquals(1, $result['TP'], 'TP devrait avoir 1 absence');
    }

    public function testGetRepartitionParTypeSansDonnees(): void
    {
        $result = $this->stats->getRepartitionParType();
        $this->assertEmpty($result);
    }

    public function testGetRepartitionParTypeUnSeulType(): void
    {
        $absences = [
            ['type' => 'CM', 'justification' => '', 'controle' => 'Non'],
            ['type' => 'CM', 'justification' => '', 'controle' => 'Non'],
            ['type' => 'CM', 'justification' => '', 'controle' => 'Non']
        ];
        $this->stats->setAbsences($absences);
        $result = $this->stats->getRepartitionParType();

        $this->assertCount(1, $result);
        $this->assertEquals(3, $result['CM']);
    }

    // ==========================================
    // TESTS : getRepartitionParMatiere()
    // ==========================================

    public function testGetRepartitionParMatiere(): void
    {
        $this->stats->setAbsences($this->absencesTest);
        $result = $this->stats->getRepartitionParMatiere();

        $this->assertIsArray($result);
        // Mathématiques: 2, Programmation: 2, Base de données: 1
        $this->assertEquals(2, $result['Mathématiques']);
        $this->assertEquals(2, $result['Programmation']);
        $this->assertEquals(1, $result['Base de données']);
    }

    public function testGetRepartitionParMatiereTriDecroissant(): void
    {
        $this->stats->setAbsences($this->absencesTest);
        $result = $this->stats->getRepartitionParMatiere();

        // Vérifier que le tri est décroissant
        $values = array_values($result);
        for ($i = 0; $i < count($values) - 1; $i++) {
            $this->assertGreaterThanOrEqual($values[$i + 1], $values[$i], 
                'Le tableau devrait être trié par ordre décroissant');
        }
    }

    public function testGetRepartitionParMatiereTop10(): void
    {
        // Créer plus de 10 matières différentes
        $absences = [];
        for ($i = 1; $i <= 15; $i++) {
            $absences[] = [
                'matiere' => "Matière $i",
                'type' => 'CM',
                'justification' => '',
                'controle' => 'Non'
            ];
        }
        $this->stats->setAbsences($absences);
        $result = $this->stats->getRepartitionParMatiere();

        $this->assertCount(10, $result, 'Devrait retourner max 10 matières');
    }

    // ==========================================
    // TESTS : getRepartitionParHeure()
    // ==========================================

    public function testGetRepartitionParHeure(): void
    {
        $this->stats->setAbsences($this->absencesTest);
        $result = $this->stats->getRepartitionParHeure();

        $this->assertIsArray($result);
        // Vérifier que toutes les tranches existent
        $this->assertArrayHasKey('8h-9h30', $result);
        $this->assertArrayHasKey('9h30-11h', $result);
        $this->assertArrayHasKey('11h-12h30', $result);
        $this->assertArrayHasKey('14h-15h30', $result);
        $this->assertArrayHasKey('15h30-17h', $result);
        $this->assertArrayHasKey('17h-18h30', $result);
    }

    public function testGetRepartitionParHeureValeurs(): void
    {
        $this->stats->setAbsences($this->absencesTest);
        $result = $this->stats->getRepartitionParHeure();

        // 8H00 -> 8h-9h30 (1 absence)
        $this->assertEquals(1, $result['8h-9h30']);
        // 10H00 -> 9h30-11h (1 absence)
        $this->assertEquals(1, $result['9h30-11h']);
        // 11H30 -> 11h-12h30 (1 absence)
        $this->assertEquals(1, $result['11h-12h30']);
        // 14H30 -> 14h-15h30 (1 absence)
        $this->assertEquals(1, $result['14h-15h30']);
        // 16H00 -> 15h30-17h (1 absence)
        $this->assertEquals(1, $result['15h30-17h']);
    }

    public function testGetRepartitionParHeureSansDonnees(): void
    {
        $result = $this->stats->getRepartitionParHeure();

        // Toutes les tranches à 0
        foreach ($result as $tranche => $count) {
            $this->assertEquals(0, $count, "Tranche $tranche devrait être à 0");
        }
    }

    // ==========================================
    // TESTS : getTendances()
    // ==========================================

    public function testGetTendancesParMois(): void
    {
        $this->stats->setAbsences($this->absencesTest);
        $result = $this->stats->getTendances('mois');

        $this->assertIsArray($result);
        // Janvier 2024: 3 absences, Février 2024: 2 absences
        $this->assertArrayHasKey('Janvier 2024', $result);
        $this->assertArrayHasKey('Février 2024', $result);
        $this->assertEquals(3, $result['Janvier 2024']);
        $this->assertEquals(2, $result['Février 2024']);
    }

    public function testGetTendancesOrdreChronologique(): void
    {
        $this->stats->setAbsences($this->absencesTest);
        $result = $this->stats->getTendances('mois');

        $keys = array_keys($result);
        // Janvier devrait être avant Février
        $posJanvier = array_search('Janvier 2024', $keys);
        $posFevrier = array_search('Février 2024', $keys);
        
        $this->assertLessThan($posFevrier, $posJanvier, 
            'Janvier devrait apparaître avant Février');
    }

    public function testGetTendancesSansDonnees(): void
    {
        $result = $this->stats->getTendances();
        $this->assertEmpty($result);
    }

    // ==========================================
    // TESTS : getListeMatieres()
    // ==========================================

    public function testGetListeMatieres(): void
    {
        $this->stats->setAbsences($this->absencesTest);
        $result = $this->stats->getListeMatieres();

        $this->assertIsArray($result);
        $this->assertContains('Mathématiques', $result);
        $this->assertContains('Programmation', $result);
        $this->assertContains('Base de données', $result);
        $this->assertCount(3, $result, 'Devrait avoir 3 matières uniques');
    }

    public function testGetListeMatieresUniques(): void
    {
        // Matières en double dans les données
        $this->stats->setAbsences($this->absencesTest);
        $result = $this->stats->getListeMatieres();

        // Pas de doublons
        $this->assertEquals(count($result), count(array_unique($result)));
    }

    // ==========================================
    // TESTS : getListeGroupes()
    // ==========================================

    public function testGetListeGroupes(): void
    {
        $this->stats->setAbsences($this->absencesTest);
        $result = $this->stats->getListeGroupes();

        $this->assertIsArray($result);
        $this->assertContains('G1', $result);
        $this->assertContains('G2', $result);
        $this->assertContains('G3', $result);
    }

    public function testGetListeGroupesExclutVides(): void
    {
        $absences = [
            ['groupe' => 'G1', 'matiere' => 'Test'],
            ['groupe' => '', 'matiere' => 'Test'],  // Groupe vide
            ['groupe' => 'G2', 'matiere' => 'Test']
        ];
        $this->stats->setAbsences($absences);
        $result = $this->stats->getListeGroupes();

        $this->assertNotContains('', $result);
        $this->assertCount(2, $result);
    }

    // ==========================================
    // TESTS : getDonneesAPI()
    // ==========================================

    public function testGetDonneesAPI(): void
    {
        $this->stats->setAbsences($this->absencesTest);
        $result = $this->stats->getDonneesAPI();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('tendances', $result);
        $this->assertArrayHasKey('matieres', $result);
        $this->assertArrayHasKey('types', $result);
        $this->assertArrayHasKey('heures', $result);
    }

    public function testGetDonneesAPIStructure(): void
    {
        $this->stats->setAbsences($this->absencesTest);
        $result = $this->stats->getDonneesAPI();

        // Vérifier que chaque clé contient des données
        $this->assertNotEmpty($result['tendances']);
        $this->assertNotEmpty($result['matieres']);
        $this->assertNotEmpty($result['types']);
        // heures peut avoir des valeurs à 0 mais doit exister
        $this->assertIsArray($result['heures']);
    }

    // ==========================================
    // TESTS : chargerAbsences() sans PDO
    // ==========================================

    public function testChargerAbsencesSansPDO(): void
    {
        // Instance sans PDO
        $stats = new Statistiques(null);
        $result = $stats->chargerAbsences([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // ==========================================
    // TESTS : Cas limites et edge cases
    // ==========================================

    public function testStatistiquesAvecUneSeuleAbsence(): void
    {
        $absences = [
            [
                'nom' => 'Test',
                'prenom' => 'User',
                'identifiant' => 'ETU999',
                'diplome' => 'BUT1',
                'date' => '01/03/2024',
                'heure' => '9H00',
                'duree' => '1h00',
                'type' => 'CM',
                'matiere' => 'Test',
                'justification' => 'Absence justifiée',
                'motif' => 'Test',
                'groupe' => 'G1',
                'prof' => 'Prof Test',
                'controle' => 'Non',
                'annee_but' => 'BUT1'
            ]
        ];
        
        $this->stats->setAbsences($absences);
        $globales = $this->stats->calculerStatistiquesGlobales();

        $this->assertEquals(1, $globales['total']);
        $this->assertEquals(1, $globales['justifiees']);
        $this->assertEquals(0, $globales['non_justifiees']);
    }

    public function testEvaluationAvecTypeDS(): void
    {
        $absences = [
            [
                'type' => 'DS',
                'justification' => 'Non justifié',
                'controle' => 'Non'  // Même si controle = Non, DS compte comme évaluation
            ]
        ];
        
        $this->stats->setAbsences($absences);
        $result = $this->stats->calculerStatistiquesGlobales();

        $this->assertEquals(1, $result['evaluations'], 
            'DS devrait compter comme évaluation même si controle = Non');
    }

    public function testEvaluationAvecControleOui(): void
    {
        $absences = [
            [
                'type' => 'TP',  // Pas un DS
                'justification' => 'Non justifié',
                'controle' => 'Oui'  // Mais controle = Oui
            ]
        ];
        
        $this->stats->setAbsences($absences);
        $result = $this->stats->calculerStatistiquesGlobales();

        $this->assertEquals(1, $result['evaluations'], 
            'controle = Oui devrait compter comme évaluation');
    }

    public function testJustificationEnAttente(): void
    {
        $absences = [
            [
                'type' => 'CM',
                'justification' => 'En attente',
                'controle' => 'Non'
            ]
        ];
        
        $this->stats->setAbsences($absences);
        $result = $this->stats->calculerStatistiquesGlobales();

        // "En attente" ne contient pas "justifiée", donc compte comme non justifiée
        $this->assertEquals(0, $result['justifiees']);
        $this->assertEquals(1, $result['non_justifiees']);
    }
}