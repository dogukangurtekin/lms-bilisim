<?php

namespace App\Services\Domain;

use App\Repositories\Eloquent\AnnouncementRepository;

class AnnouncementService extends CrudService
{
    public function __construct(AnnouncementRepository $repository)
    {
        parent::__construct($repository);
    }
}
