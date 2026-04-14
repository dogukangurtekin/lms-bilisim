<?php

namespace App\Services\Contracts;

use Illuminate\Database\Eloquent\Model;

interface CrudServiceInterface
{
    public function list(int $perPage = 15);
    public function create(array $data): Model;
    public function update(Model $model, array $data): bool;
    public function delete(Model $model): bool;
}
