<?php

namespace App\Services\Domain;

use App\Repositories\Eloquent\CourseRepository;

class CourseService extends CrudService
{
    public function __construct(CourseRepository $repository)
    {
        parent::__construct($repository);
    }
}
