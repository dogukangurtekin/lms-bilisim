<?php

namespace App\Services\Domain;

use App\Repositories\Eloquent\StudentRepository;

class StudentService extends CrudService
{
    public function __construct(StudentRepository $repository)
    {
        parent::__construct($repository);
    }
}
