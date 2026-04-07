<?php

declare(strict_types=1);

namespace src\Services;

final class ResponsableJustificationDecisionService
{
    public function accept(array $justification, array $waitingList): array
    {
        $filteredWaitingList = array_values(array_filter(
            $waitingList,
            static fn (int $id): bool => $id !== ($justification['id'] ?? -1)
        ));

        return [
            'status' => 'Excusee',
            'waiting_list' => $filteredWaitingList,
            'email_subject' => '[GESTION-ABS] Justificatif accepte',
        ];
    }

    public function reject(string $reason, array $knownReasons): array
    {
        $normalizedReason = trim($reason);
        if ($normalizedReason === '') {
            return [
                'recorded' => false,
                'error' => 'Le motif de rejet est obligatoire',
                'status' => 'En attente',
                'reasons' => $knownReasons,
            ];
        }

        if (!in_array($normalizedReason, $knownReasons, true)) {
            $knownReasons[] = $normalizedReason;
        }

        return [
            'recorded' => true,
            'error' => '',
            'status' => 'Refusee',
            'reasons' => $knownReasons,
        ];
    }
}
