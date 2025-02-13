<?php

namespace App\Classes\Repositories;

use App\Models\KeyMessage;


class KeyMessageRepository implements KeyMessageRepositoryInterface
{

    /**
     * @var KeyMessage
     */
    protected $keyMessageModel;

    /**
     * @param KeyMessage $keyMessageModel
     */
    public function __construct(KeyMessage $keyMessageModel)
    {
        $this->keyMessageModel = $keyMessageModel;
    }

    /**
     * @param array $attributes
     * @return static
     */
    public function newInstance(array $attributes = [])
    {
        return $this->keyMessageModel->newInstance($attributes);
    }

    /**
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all($columns = ['*'])
    {
        return $this->keyMessageModel->all($columns);
    }

    /**
     * @param array $attributes
     * @return static
     */
    public function create(array $attributes)
    {
        return $this->keyMessageModel->create($attributes);
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        return $this->keyMessageModel->findOrFail($id, $columns);
    }

    /**
     * @param $id
     * @return int
     */
    public function destroy($id)
    {
        return $this->keyMessageModel->destroy($id);
    }

    /**
     * @param $id
     * @param array $input
     * @return mixed
     */
    public function updateWithIdAndInput($id, array $input)
    {
        return $this->keyMessageModel->where('id', $id)->update($input);
    }

    
    /**
     * @param $stageId
     * @return mixed
    */
    public function findItemsByStageId($stageId)
    {
        return $this->keyMessageModel->where('stage_id', $stageId)->get();
    }
}