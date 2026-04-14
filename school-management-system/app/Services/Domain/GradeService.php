<?php

namespace App\Services\Domain;

use App\Repositories\Eloquent\GradeRepository;

class GradeService extends CrudService
{
    public function __construct(GradeRepository $repository)
    {
        parent::__construct($repository);
    }
}
