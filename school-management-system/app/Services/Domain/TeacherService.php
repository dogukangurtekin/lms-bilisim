<?php

namespace App\Services\Domain;

use App\Repositories\Eloquent\TeacherRepository;

class TeacherService extends CrudService
{
    public function __construct(TeacherRepository $repository)
    {
        parent::__construct($repository);
    }
}
