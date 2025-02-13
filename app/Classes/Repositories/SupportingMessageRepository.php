<?php

namespace App\Classes\Repositories;

use App\Models\SupportingMessage;

class SupportingMessageRepository implements SupportingMessageRepositoryInterface
{

    /**
     * @var SupportingMessage
     */
    protected $suppotingMessageModel;

    /**
     * @param SupportingMessage $model
     */
    public function __construct(SupportingMessage $model)
    {
        $this->suppotingMessageModel = $model;
    }

    /**
     * @param array $attributes
     * @return static
     */
    public function newInstance(array $attributes = [])
    {
        return $this->suppotingMessageModel->newInstance($attributes);
    }

    /**
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all($columns = ['*'])
    {
        return $this->suppotingMessageModel->all($columns);
    }

    /**
     * @param array $attributes
     * @return static
     */
    public function create(array $attributes)
    {
        return $this->suppotingMessageModel->create($attributes);
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        return $this->suppotingMessageModel->findOrFail($id, $columns);
    }

    /**
     * @param $id
     * @return int
     */
    public function destroy($id)
    {
        return $this->suppotingMessageModel->destroy($id);
    }

    /**
     * @param $id
     * @param array $input
     * @return mixed
    */
    public function updateWithIdAndInput($id, array $input)
    {
        return $this->suppotingMessageModel->where('id', $id)->update($input);
    }

    public function findItemsByKeyMessageId($keyMessageId)
    {
        return $this->suppotingMessageModel->where('key_message_id', $keyMessageId)->get();
    }
}