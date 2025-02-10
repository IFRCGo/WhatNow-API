<?php

namespace App\Classes\Repositories;

use App\Models\UsageLog;
use Illuminate\Support\Facades\DB;

class UsageLogRepository implements UsageLogRepositoryInterface
{
    /**
     * @var \App\UsageLog
     */
    protected $usageLogModel;

    public function __construct(UsageLog $usageLogModel)
    {
        $this->usageLogModel = $usageLogModel;
    }

    public function all($columns = ['*'])
    {
        return $this->usageLogModel->all($columns);
    }

    public function whereBetween($fromDate, $toDate, $columns = ['*'])
    {
        return $this->usageLogModel->where('timestamp', '>=', $fromDate)
            ->where('timestamp', '<=', $toDate)
            ->get($columns);
    }

    public function create(array $attributes)
    {
        return $this->usageLogModel->create($attributes);
    }

    public function destroy($id)
    {
        return $this->usageLogModel->destroy($id);
    }

    public function find($id, $columns = ['*'])
    {
        return $this->usageLogModel->findOrFail($id, $columns);
    }

    public function newInstance(array $attributes = [])
    {
        return $this->usageLogModel->newInstance($attributes);
    }

    public function updateWithIdAndInput($id, array $input)
    {
        return $this->usageLogModel->where('id', $id)->update($input);
    }

    public function getForApplication($applicationId, $fromDate = null, $toDate = null)
    {
        $query = $this->usageLogModel->where('application_id', $applicationId);

        if ($fromDate) {
            $query->where('timestamp', '>=', $fromDate);
        }

        if ($toDate) {
            $query->where('timestamp', '<=', $toDate);
        }

        return $query->get();
    }

    public function getForEndpoint($endpoint, $fromDate = null, $toDate = null)
    {
        $query = $this->usageLogModel->where('endpoint', $endpoint);

        if ($fromDate) {
            $query->where('timestamp', '>=', $fromDate);
        }

        if ($toDate) {
            $query->where('timestamp', '<=', $toDate);
        }

        return $query->get();
    }

    public function listByEndpoint($fromDate = null, $toDate = null)
    {
        $query = DB::table('usage_logs')
            ->select('endpoint', 'application_id', DB::raw('count(*) as hit_count'))
            ->groupBy('application_id', 'endpoint')
            ->orderBy('hit_count', 'desc');

        if ($fromDate) {
            $query->where('timestamp', '>=', $fromDate);
        }

        if ($toDate) {
            $query->where('timestamp', '<=', $toDate);
        }

        return $query->get();
    }
}
