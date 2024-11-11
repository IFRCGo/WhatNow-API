<?php

use Illuminate\Database\Seeder;

class AlertsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $path = dirname(__FILE__) . '/alerts.csv';

        $file = new SplFileObject($path);
        ;
        $file->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_CSV);

        $expiry = (new DateTimeImmutable())->add(new \DateInterval('P1M'))->format('Y-m-d H:i:s');

        foreach ($file as $row) {
            DB::table('alerts')->insert([
                'id' => $row[0],
                'org_id' => $row[1],
                // 'source_id' => $row[2], - source_id is redundant now so we'll ignore it
                'country_code' => $row[3],
                'language_code' => $row[4],
                'event' => $row[5],
                'headline' => $row[6],
                'description' => $row[7],
                'area_polygon' => $row[8],
                'area_description' => $row[9],
                'type' => $row[10],
                'status' => $row[11],
                'scope' => $row[12],
                'category' => $row[13],
                'urgency' => $row[14],
                'severity' => $row[15],
                'certainty' => $row[16],
                'sent_date' => $row[17],
                'onset_date' => $row[18],
                'effective_date' => $row[19],
                'expiry_date' => $expiry,
                'created_at' => $row[21],
                'updated_at' => $row[22],
            ]);
        }
    }
}
