<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Event;

class EventRepository
{
    private Event $model;

    public function __construct(?Event $model = null)
    {
        $this->model = $model ?? new Event();
    }

    public function findWithDetails(int $id): ?array
    {
        $event = $this->model->findById($id);
        if (!$event) {
            return null;
        }
        $event['images'] = $this->model->getEventImages($id);
        $event['requirements'] = $this->model->getEventRequirements($id);
        return $event;
    }

    public function paginateCatalog(array $filters, int $page = 1, int $perPage = 6): array
    {
        return $this->model->searchEvents($filters, $page, $perPage);
    }
}
