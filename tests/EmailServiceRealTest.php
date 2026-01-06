<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Group;
use src\Models\EmailService;

/**
 * Tests qui envoient de VRAIS emails
 */
class EmailServiceRealTest extends TestCase
{
    #[Group('manual')]
    public function testSendRealAbsenceAlertEmail()
    {
        $emailService = new EmailService();
        
        $testEmail = 'dilara.simsek@uphf.fr';
        
        $result = $emailService->sendAbsenceAlertEmail(
            $testEmail,
            'Dilara Simsek',
            123,
            '2026-01-06 08:00:00',
            '2026-01-06 12:00:00',
            'Test PHPUnit - Mathématiques',
            false,
            48
        );
        
        $this->assertTrue($result, 'L\'email devrait être envoyé avec succès');
    }
    
    #[Group('manual')]
    public function testSendRealUrgentAbsenceAlertEmail()
    {
        $emailService = new EmailService();
        
        $testEmail = 'dilara.simsek@uphf.fr';
        
        $result = $emailService->sendAbsenceAlertEmail(
            $testEmail,
            'Dilara Simsek',
            456,
            '2026-01-06 08:00:00',
            '2026-01-06 12:00:00',
            'Test PHPUnit - EVALUATION PHP',
            true,
            24
        );
        
        $this->assertTrue($result);
    }
    
    #[Group('manual')]
    public function testSendRealPasswordResetEmail()
    {
        $emailService = new EmailService();
        
        $testEmail = 'dilara.simsek@uphf.fr';
        
        $result = $emailService->sendPasswordResetEmail(
            $testEmail,
            'Dilara Simsek',
            'MotDePasseTemp123!'
        );
        
        $this->assertTrue($result);
    }
    
    #[Group('manual')]
    public function testSendRealJustificationRequestEmail()
    {
        $emailService = new EmailService();
        
        $testEmail = 'dilara.simsek@uphf.fr';
        
        $result = $emailService->sendJustificationRequestEmail(
            $testEmail,
            'Dilara Simsek',
            'Votre justificatif médical n\'est pas complet. Merci de fournir un certificat médical original.'
        );
        
        $this->assertTrue($result);
    }
}
