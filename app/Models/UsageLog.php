<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsageLog extends Model
{
    protected $connection = 'stats_mysql'; // This model uses a different database connection for the stats DB

    protected $table = 'usage_logs';
}
