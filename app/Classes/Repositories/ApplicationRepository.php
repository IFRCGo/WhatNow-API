<?php

namespace App\Classes\Repositories;

use App\Models\Application;

class ApplicationRepository implements ApplicationRepositoryInterface
{
    /**
     * @var Application
     */
    protected $applicationModel;

    /**
     * @param Application $applicationModel
     */
    public function __construct(Application $applicationModel)
    {
        $this->applicationModel = $applicationModel;
    }

    /**
     * @param array $attributes
     * @return Application
     */
    public function newInstance(array $attributes = [])
    {
        return $this->applicationModel->newInstance($attributes);
    }

    /**
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all($columns = ['*'])
    {
        return $this->applicationModel->all($columns);
    }

    public function allDesc($columns = ['*'])
    {
        return $this->applicationModel->orderBy('id', 'desc')->get($columns);
    }

    public function findIn($ids = [], $columns = ['*'])
    {
        return $this->applicationModel->whereIn('id', $ids)->get($columns);
    }

    /**
     * @param array $attributes
     * @return Application
     */
    public function create(array $attributes)
    {
        $attributes['key'] = Application::generateKey();

        return $this->applicationModel->create($attributes);
    }

    /**
     * @param $id
     * @param array $columns
     * @return Application
     */
    public function find($id, $columns = ['*'])
    {
        return $this->applicationModel->find($id, $columns);
    }

    /**
     * @param $id
     * @param array $input
     * @return Application
     */
    public function updateWithIdAndInput($id, array $input)
    {
        return $this->applicationModel->where('id', $id)->update($input);
    }

    /**
     * @param $id
     * @return int
     */
    public function destroy($id)
    {
        return $this->applicationModel->destroy($id);
    }

    /**
     * @param $tenantId
     * @param $userId
     * @return Application[]
     */
    public function findForUserId($tenantId, $userId)
    {
        return $this->applicationModel->where('tenant_id', '=', $tenantId)->where('tenant_user_id', '=', $userId)->get();
    }
}
