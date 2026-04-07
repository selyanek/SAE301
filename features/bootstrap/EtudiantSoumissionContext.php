<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use src\Services\JustificationSubmissionService;

final class EtudiantSoumissionContext implements Context
{
    private bool $studentConnected;
    private bool $formDisplayed;
    private bool $formFilled;
    private DateTimeImmutable $now;
    private ?DateTimeImmutable $absenceStart;
    private ?DateTimeImmutable $absenceEnd;
    private ?DateTimeImmutable $returnDate;
    private array $concernedCourses;
    private bool $submissionAccepted;
    private bool $justificationSaved;
    private string $errorMessage;
    private array $sentEmails;
    private array $courseCatalog;

    private JustificationSubmissionService $submissionService;

    public function __construct()
    {
        $this->submissionService = new JustificationSubmissionService();
        $this->resetState();
    }

    #[BeforeScenario]
    public function beforeScenario(): void
    {
        $this->resetState();
    }

    #[Given('/^que l\'etudiant est connecte a son espace personnel$/')]
    public function etudiantConnecteEspacePersonnel(): void
    {
        $this->studentConnected = true;
    }

    #[When('/^il accede a la section Soumettre un justificatif$/')]
    public function ilAccedeASectionSoumettreJustificatif(): void
    {
        if (!$this->studentConnected) {
            throw new RuntimeException('Etudiant non connecte.');
        }

        $this->formDisplayed = true;
    }

    #[Then('/^le systeme affiche un formulaire a remplir$/')]
    public function systemeAfficheFormulaireARemplir(): void
    {
        if (!$this->formDisplayed) {
            throw new RuntimeException('Le formulaire de soumission ne saffiche pas.');
        }
    }

    #[Given('/^que l\'etudiant a rempli correctement le formulaire$/')]
    public function etudiantARempliCorrectementFormulaire(): void
    {
        $this->studentConnected = true;
        $this->formFilled = true;
    }

    #[When('/^il saisit des dates d\'absence valides$/')]
    public function ilSaisitDatesAbsenceValides(): void
    {
        if (!$this->formFilled) {
            throw new RuntimeException('Le formulaire doit etre rempli avant de saisir les dates.');
        }

        $this->absenceStart = new DateTimeImmutable('2026-04-08 07:30:00');
        $this->absenceEnd = new DateTimeImmutable('2026-04-08 16:30:00');
        $this->concernedCourses = $this->submissionService->findConcernedCourses(
            $this->courseCatalog,
            $this->absenceStart,
            $this->absenceEnd
        );
    }

    #[Then('/^le systeme affiche automatiquement les cours concernes$/')]
    public function systemeAfficheCoursConcernesAutomatiquement(): void
    {
        if (count($this->concernedCourses) === 0) {
            throw new RuntimeException('Aucun cours concerne na ete affiche.');
        }
    }

    #[Given('/^que l\'etudiant est connecte et que sa date de retour date de plus de 48 heures$/')]
    public function etudiantConnecteDateRetourPlus48h(): void
    {
        $this->studentConnected = true;
        $this->formFilled = true;
        $this->absenceStart = $this->now->sub(new DateInterval('P4D'));
        $this->absenceEnd = $this->now->sub(new DateInterval('P3D'));
        $this->returnDate = $this->now->sub(new DateInterval('PT72H'));
    }

    #[When('/^il tente de soumettre son justificatif$/')]
    public function ilTenteSoumettreJustificatif(): void
    {
        $this->submitJustification();
    }

    #[Then('/^la soumission est refusee avec un message d\'erreur de depassement de delai$/')]
    public function soumissionRefuseeDepassementDelai(): void
    {
        if ($this->submissionAccepted) {
            throw new RuntimeException('La soumission ne devait pas etre acceptee apres 48h.');
        }

        $expected = 'La soumission est impossible: delai de 48h depasse.';
        if ($this->errorMessage !== $expected) {
            throw new RuntimeException('Message derreur inattendu: ' . $this->errorMessage);
        }
    }

    #[Given('/^que l\'etudiant a fini de remplir le formulaire d\'absence dans le delai autorise$/')]
    public function etudiantFormulaireFiniDansDelai(): void
    {
        $this->studentConnected = true;
        $this->formFilled = true;
        $this->absenceStart = $this->now->sub(new DateInterval('P1D'));
        $this->absenceEnd = $this->now->sub(new DateInterval('PT12H'));
        $this->returnDate = $this->now->sub(new DateInterval('PT12H'));
        $this->concernedCourses = $this->submissionService->findConcernedCourses(
            $this->courseCatalog,
            $this->absenceStart,
            $this->absenceEnd
        );
    }

    #[When('/^il appuie sur le bouton Soumettre$/')]
    public function ilAppuieBoutonSoumettre(): void
    {
        $this->submitJustification();
    }

    #[Then('/^le justificatif est enregistre et un email de confirmation avec le tag \[GESTION-ABS\] est envoye$/')]
    public function justificatifEnregistreEtEmailTagEnvoye(): void
    {
        if (!$this->justificationSaved) {
            throw new RuntimeException('Le justificatif devait etre enregistre.');
        }

        if (count($this->sentEmails) === 0) {
            throw new RuntimeException('Aucun email de confirmation na ete envoye.');
        }

        $subject = $this->sentEmails[0]['subject'] ?? '';
        if (strpos($subject, '[GESTION-ABS]') === false) {
            throw new RuntimeException('Le sujet email ne contient pas le tag [GESTION-ABS].');
        }
    }

    private function resetState(): void
    {
        $this->studentConnected = false;
        $this->formDisplayed = false;
        $this->formFilled = false;
        $this->now = new DateTimeImmutable('2026-04-07 10:00:00');
        $this->absenceStart = null;
        $this->absenceEnd = null;
        $this->returnDate = null;
        $this->concernedCourses = [];
        $this->submissionAccepted = false;
        $this->justificationSaved = false;
        $this->errorMessage = '';
        $this->sentEmails = [];

        $this->courseCatalog = [
            [
                'name' => 'Programmation Web',
                'start' => new DateTimeImmutable('2026-04-08 08:00:00'),
                'end' => new DateTimeImmutable('2026-04-08 10:00:00'),
            ],
            [
                'name' => 'Base de Donnees',
                'start' => new DateTimeImmutable('2026-04-08 14:00:00'),
                'end' => new DateTimeImmutable('2026-04-08 16:00:00'),
            ],
            [
                'name' => 'Mathematiques',
                'start' => new DateTimeImmutable('2026-04-09 09:00:00'),
                'end' => new DateTimeImmutable('2026-04-09 11:00:00'),
            ],
        ];
    }

    private function submitJustification(): void
    {
        if (!$this->studentConnected || !$this->formFilled) {
            throw new RuntimeException('Impossible de soumettre sans connexion et formulaire complet.');
        }

        $decision = $this->submissionService->canSubmitWithinDelay($this->now, $this->returnDate);
        if (!$decision['accepted']) {
            $this->submissionAccepted = false;
            $this->justificationSaved = false;
            $this->errorMessage = $decision['error'];
            return;
        }

        $this->submissionAccepted = true;
        $this->justificationSaved = true;
        $this->errorMessage = '';
        $this->sendEmail('etudiant@example.com', $this->submissionService->confirmationSubject());
    }

    private function sendEmail(string $recipient, string $subject): void
    {
        $this->sentEmails[] = [
            'to' => $recipient,
            'subject' => $subject,
        ];
    }
}
