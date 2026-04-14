<?php

namespace App\Services\Domain;

use App\Repositories\Contracts\CrudRepositoryInterface;
use App\Services\Contracts\CrudServiceInterface;
use Illuminate\Database\Eloquent\Model;

class CrudService implements CrudServiceInterface
{
    public function __construct(private CrudRepositoryInterface $repository)
    {
    }

    public function list(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    public function create(array $data): Model
    {
        return $this->repository->create($data);
    }

    public function update(Model $model, array $data): bool
    {
        return $this->repository->update($model, $data);
    }

    public function delete(Model $model): bool
    {
        return $this->repository->delete($model);
    }
}
