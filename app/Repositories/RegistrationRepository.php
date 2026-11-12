<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Registration;

class RegistrationRepository
{
    private Registration $model;

    public function __construct(?Registration $model = null)
    {
        $this->model = $model ?? new Registration();
    }

    public function getVolunteerRegistrations(int $userId): array
    {
        return $this->model->getByUser($userId);
    }

    public function getEventParticipants(int $eventId): array
    {
        return $this->model->getByEvent($eventId);
    }
}
