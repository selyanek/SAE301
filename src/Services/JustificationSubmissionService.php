<?php

declare(strict_types=1);

namespace src\Services;

use DateTimeImmutable;

final class JustificationSubmissionService
{
    public function findConcernedCourses(array $courseCatalog, DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        return array_values(array_filter(
            $courseCatalog,
            static function (array $course) use ($start, $end): bool {
                return $course['start'] < $end && $course['end'] > $start;
            }
        ));
    }

    public function canSubmitWithinDelay(DateTimeImmutable $now, ?DateTimeImmutable $returnDate): array
    {
        if ($returnDate === null) {
            return [
                'accepted' => true,
                'error' => '',
            ];
        }

        $hoursSinceReturn = ($now->getTimestamp() - $returnDate->getTimestamp()) / 3600;
        if ($hoursSinceReturn > 48) {
            return [
                'accepted' => false,
                'error' => 'La soumission est impossible: delai de 48h depasse.',
            ];
        }

        return [
            'accepted' => true,
            'error' => '',
        ];
    }

    public function confirmationSubject(): string
    {
        return '[GESTION-ABS] Confirmation de depot du justificatif';
    }
}
