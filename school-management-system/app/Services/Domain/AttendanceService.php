<?php

namespace App\Services\Domain;

use App\Repositories\Eloquent\AttendanceRepository;

class AttendanceService extends CrudService
{
    public function __construct(AttendanceRepository $repository)
    {
        parent::__construct($repository);
    }
}
