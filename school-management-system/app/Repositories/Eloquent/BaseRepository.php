<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\CrudRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements CrudRepositoryInterface
{
    abstract protected function modelClass(): string;

    protected function query()
    {
        $modelClass = $this->modelClass();
        return $modelClass::query();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->latest()->paginate($perPage);
    }

    public function create(array $data): Model
    {
        return $this->query()->create($data);
    }

    public function update(Model $model, array $data): bool
    {
        return $model->update($data);
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }
}
