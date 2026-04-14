<?php

namespace App\Services\Domain;

use App\Repositories\Eloquent\SchoolClassRepository;

class SchoolClassService extends CrudService
{
    public function __construct(SchoolClassRepository $repository)
    {
        parent::__construct($repository);
    }
}
