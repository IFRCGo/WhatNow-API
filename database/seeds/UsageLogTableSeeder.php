<?php

namespace Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsageLogTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $path = dirname(__FILE__) . '/usage_logs.csv';

        $file = new SplFileObject($path);

        $file->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_CSV);

        foreach ($file as $row) {
            DB::connection('stats_mysql')->table('usage_logs')->insert([
                'id' => $row[0],
                'method' => $row[1],
                'application_id' => $row[2],
                'endpoint' => $row[3],
                'timestamp' => $row[4],
            ]);
        }
    }
}
