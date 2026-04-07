<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use src\Services\ResponsableJustificationDecisionService;

final class ResponsableTraitementContext implements Context
{
    private array $waitingList;
    private ?array $currentJustification;
    private string $absenceStatus;
    private bool $rejectionRecorded;
    private array $rejectionReasons;
    private string $errorMessage;
    private array $sentEmails;

    private ResponsableJustificationDecisionService $decisionService;

    public function __construct()
    {
        $this->decisionService = new ResponsableJustificationDecisionService();
        $this->resetState();
    }

    #[BeforeScenario]
    public function beforeScenario(): void
    {
        $this->resetState();
    }

    #[Given('/^qu\'un justificatif complet et valide est en attente de decision$/')]
    public function justificatifCompletValideEnAttente(): void
    {
        $this->waitingList = [101];
        $this->currentJustification = [
            'id' => 101,
            'is_complete' => true,
            'is_valid' => true,
            'student_email' => 'etu@example.com',
        ];
        $this->absenceStatus = 'En attente';
    }

    #[When('/^le responsable clique sur Accepter$/')]
    public function responsableCliqueAccepter(): void
    {
        if ($this->currentJustification === null) {
            throw new RuntimeException('Aucun justificatif a traiter.');
        }

        $result = $this->decisionService->accept($this->currentJustification, $this->waitingList);
        $this->absenceStatus = $result['status'];
        $this->waitingList = $result['waiting_list'];
        $this->sendEmail($this->currentJustification['student_email'], $result['email_subject']);
    }

    #[Then('/^l\'absence passe au statut Excusee et l\'etudiant recoit un email de confirmation et le justificatif disparait de la liste d\'attente$/')]
    public function absenceExcuseeEmailEnvoyeEtDisparaitListe(): void
    {
        if ($this->absenceStatus !== 'Excusee') {
            throw new RuntimeException('Le statut attendu est Excusee.');
        }

        if (count($this->sentEmails) === 0) {
            throw new RuntimeException('Lemail de confirmation na pas ete envoye.');
        }

        if (in_array(101, $this->waitingList, true)) {
            throw new RuntimeException('Le justificatif est encore present dans la liste dattente.');
        }
    }

    #[Given('/^qu\'un justificatif est en attente de decision$/')]
    public function justificatifEnAttenteDecision(): void
    {
        $this->waitingList = [202];
        $this->currentJustification = [
            'id' => 202,
            'is_complete' => true,
            'is_valid' => true,
            'student_email' => 'etu2@example.com',
        ];
        $this->absenceStatus = 'En attente';
        $this->rejectionRecorded = false;
        $this->errorMessage = '';
    }

    #[When('/^le responsable selectionne Autre motif, saisit (.+) et valide le rejet$/')]
    public function responsableSelectionneAutreMotifEtValideRejet(string $customReason): void
    {
        $this->rejectJustification($customReason);
    }

    #[Then('/^le motif est ajoute a la liste reutilisable et le rejet est enregistre$/')]
    public function motifAjouteEtRejetEnregistre(): void
    {
        if (!in_array('Motif non liste', $this->rejectionReasons, true)) {
            throw new RuntimeException('Le nouveau motif na pas ete ajoute a la liste reutilisable.');
        }

        if (!$this->rejectionRecorded) {
            throw new RuntimeException('Le rejet devait etre enregistre.');
        }
    }

    #[When('/^le responsable tente de valider un rejet sans motif$/')]
    public function responsableValideRejetSansMotif(): void
    {
        $this->rejectJustification('');
    }

    #[Then('/^le message Le motif de rejet est obligatoire s\'affiche et le rejet n\'est pas enregistre$/')]
    public function messageMotifObligatoireEtRejetNonEnregistre(): void
    {
        if ($this->errorMessage !== 'Le motif de rejet est obligatoire') {
            throw new RuntimeException('Message derreur inattendu: ' . $this->errorMessage);
        }

        if ($this->rejectionRecorded) {
            throw new RuntimeException('Le rejet ne devait pas etre enregistre sans motif.');
        }
    }

    private function resetState(): void
    {
        $this->waitingList = [];
        $this->currentJustification = null;
        $this->absenceStatus = 'En attente';
        $this->rejectionRecorded = false;
        $this->rejectionReasons = [
            'Document illisible',
            'Periode non couverte',
        ];
        $this->errorMessage = '';
        $this->sentEmails = [];
    }

    private function rejectJustification(string $reason): void
    {
        $result = $this->decisionService->reject($reason, $this->rejectionReasons);
        $this->rejectionRecorded = $result['recorded'];
        $this->errorMessage = $result['error'];
        $this->absenceStatus = $result['status'];
        $this->rejectionReasons = $result['reasons'];
    }

    private function sendEmail(string $recipient, string $subject): void
    {
        $this->sentEmails[] = [
            'to' => $recipient,
            'subject' => $subject,
        ];
    }
}
