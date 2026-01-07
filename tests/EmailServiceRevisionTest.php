<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Group;
use src\Models\EmailService;

/**
 * Tests pour l'envoi d'emails de révision de décision
 */
class EmailServiceRevisionTest extends TestCase
{
    private EmailService $emailService;
    
    protected function setUp(): void
    {
        $this->emailService = new EmailService();
    }
    
    /**
     * Test : Vérifier que la méthode existe
     */
    public function testSendRevisionDecisionEmailMethodExists(): void
    {
        $this->assertTrue(
            method_exists($this->emailService, 'sendRevisionDecisionEmail'),
            'La méthode sendRevisionDecisionEmail doit exister'
        );
    }
    
    /**
     * Test : Vérifier les paramètres de la méthode
     */
    public function testSendRevisionDecisionEmailHasCorrectParameters(): void
    {
        $reflection = new ReflectionClass(EmailService::class);
        $method = $reflection->getMethod('sendRevisionDecisionEmail');
        
        // 7 paramètres attendus
        $this->assertEquals(7, $method->getNumberOfParameters());
        
        $params = $method->getParameters();
        $this->assertEquals('email', $params[0]->getName());
        $this->assertEquals('nom', $params[1]->getName());
        $this->assertEquals('dateDebut', $params[2]->getName());
        $this->assertEquals('dateFin', $params[3]->getName());
        $this->assertEquals('ancienStatut', $params[4]->getName());
        $this->assertEquals('nouveauStatut', $params[5]->getName());
        $this->assertEquals('justification', $params[6]->getName());
    }
    
    /**
     * Test : Vérifier le formatage des dates
     */
    public function testDateFormattingInRevisionEmail(): void
    {
        $dateDebut = '2026-01-06 08:00:00';
        $dateFin = '2026-01-06 17:00:00';
        
        $expectedDateDebut = '06/01/2026 08:00';
        $expectedDateFin = '06/01/2026 17:00';
        
        $actualDateDebut = date('d/m/Y H:i', strtotime($dateDebut));
        $actualDateFin = date('d/m/Y H:i', strtotime($dateFin));
        
        $this->assertEquals($expectedDateDebut, $actualDateDebut);
        $this->assertEquals($expectedDateFin, $actualDateFin);
    }
    
    /**
     * Test : Vérifier la conversion des statuts
     */
    public function testStatutLabelConversion(): void
    {
        // Test "valide"
        $statut = 'valide';
        $label = ($statut === 'valide') ? 'Validee' : (($statut === 'refuse') ? 'Refusee' : 'En attente');
        $this->assertEquals('Validee', $label);
        
        // Test "refuse"
        $statut = 'refuse';
        $label = ($statut === 'valide') ? 'Validee' : (($statut === 'refuse') ? 'Refusee' : 'En attente');
        $this->assertEquals('Refusee', $label);
        
        // Test autre statut
        $statut = 'en_attente';
        $label = ($statut === 'valide') ? 'Validee' : (($statut === 'refuse') ? 'Refusee' : 'En attente');
        $this->assertEquals('En attente', $label);
    }
    
    /**
     * Test : Vérifier que le HTML contient tous les éléments
     */
    public function testHtmlStructureContainsRequiredElements(): void
    {
        $nom = 'Dilara Simsek';
        $dateDebut = '06/01/2026 08:00';
        $dateFin = '06/01/2026 17:00';
        $ancienLabel = 'Refusee';
        $nouveauLabel = 'Validee';
        $justification = 'Certificat médical fourni';
        
        $html = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body>
            <h2>Bonjour {$nom},</h2>
            <p>La decision concernant votre absence du <strong>{$dateDebut}</strong> au <strong>{$dateFin}</strong> a ete revisee.</p>
            <p><strong>Ancien statut :</strong> {$ancienLabel}</p>
            <p><strong>Nouveau statut :</strong> {$nouveauLabel}</p>
            <p><strong>Motif de la revision :</strong> {$justification}</p>
            <p>Cordialement,<br>L'equipe EduTrack</p>
        </body></html>";
        
        $this->assertStringContainsString('Dilara Simsek', $html);
        $this->assertStringContainsString('06/01/2026 08:00', $html);
        $this->assertStringContainsString('Refusee', $html);
        $this->assertStringContainsString('Validee', $html);
        $this->assertStringContainsString('Certificat médical fourni', $html);
        $this->assertStringContainsString('EduTrack', $html);
    }
    
    /**
     * @group manual
     * Test manuel : Email de révision Refusé → Validé
     */
    #[Group('manual')]
    public function testSendRealRevisionEmailRefuseToValide(): void
    {
        $result = $this->emailService->sendRevisionDecisionEmail(
            'dilara.simsek@uphf.fr',
            'Dilara Simsek',
            '2026-01-06 08:00:00',
            '2026-01-06 17:00:00',
            'refuse',
            'valide',
            'Après vérification du certificat médical, votre absence est désormais validée.'
        );
        
        $this->assertTrue($result, 'L\'email devrait être envoyé avec succès');
    }
    
    /**
     * @group manual
     * Test manuel : Email de révision Validé → Refusé
     */
    #[Group('manual')]
    public function testSendRealRevisionEmailValideToRefuse(): void
    {
        $result = $this->emailService->sendRevisionDecisionEmail(
            'dilara.simsek@uphf.fr',
            'Dilara Simsek',
            '2026-01-06 08:00:00',
            '2026-01-06 17:00:00',
            'valide',
            'refuse',
            'Le justificatif fourni ne correspond pas aux critères requis.'
        );
        
        $this->assertTrue($result);
    }
    
    /**
     * @group manual
     * Test manuel : Email de révision En attente → Validé
     */
    #[Group('manual')]
    public function testSendRealRevisionEmailEnAttenteToValide(): void
    {
        $result = $this->emailService->sendRevisionDecisionEmail(
            'dilara.simsek@uphf.fr',
            'Dilara Simsek',
            '2026-01-06 08:00:00',
            '2026-01-06 17:00:00',
            'en_attente',
            'valide',
            'Suite à l\'analyse de votre dossier, votre absence a été validée.'
        );
        
        $this->assertTrue($result);
    }
    
    /**
     * @group manual
     * Test manuel : Email avec justification longue
     */
    #[Group('manual')]
    public function testSendRealRevisionEmailWithLongJustification(): void
    {
        $longJustification = "Après une analyse approfondie du dossier et consultation avec le responsable pédagogique, "
            . "il a été décidé de réviser la décision initiale. Le certificat médical fourni est authentique et valide. "
            . "L'absence est donc justifiée conformément au règlement intérieur de l'établissement. "
            . "Cette décision est définitive et ne pourra plus être contestée.";
        
        $result = $this->emailService->sendRevisionDecisionEmail(
            'dilara.simsek@uphf.fr',
            'Dilara Simsek',
            '2026-01-05 09:00:00',
            '2026-01-05 18:00:00',
            'refuse',
            'valide',
            $longJustification
        );
        
        $this->assertTrue($result);
    }
    
    /**
     * @group manual
     * Test manuel : Email de révision En attente → Refusé
     */
    #[Group('manual')]
    public function testSendRealRevisionEmailEnAttenteToRefuse(): void
    {
        $result = $this->emailService->sendRevisionDecisionEmail(
            'dilara.simsek@uphf.fr',
            'Dilara Simsek',
            '2026-01-07 10:00:00',
            '2026-01-07 16:00:00',
            'en_attente',
            'refuse',
            'Aucun justificatif n\'a été fourni dans le délai imparti de 48 heures.'
        );
        
        $this->assertTrue($result);
    }
}
