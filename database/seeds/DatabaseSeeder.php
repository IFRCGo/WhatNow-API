<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $path = dirname(__FILE__) . '/organisations.csv';

        $file = new SplFileObject($path);

        $file->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_CSV);

        foreach ($file as $row) {
            /** @var \App\Models\Organisation $org */
            $org = app(App\Models\Organisation::class)
                ->create([
                    'country_code' => $row[0],
                    'org_name' => $row[1],
                    'oid_code' => $row[2],
                ]);

            $org->details()->create([
                'language_code' => 'en',
                'org_name' => 'International Federation of Red Cross and Red Crescent Societies',
                'attribution_message' => 'Key Messages from International Federation of Red Cross and Red Crescent Societies',
                'published' => true,
            ]);

            // Sometimes give it a Spanish translation too =)
            if (rand(1, 3) === 1) {
                $org->details()->create([
                    'language_code' => 'es',
                    'org_name' => 'La Federacion Internacional de Sociedades de la Cruz Roja y de la Media Luna Roja',
                    'attribution_message' => 'Mensajes importantes de la Federacion Internacional de Sociedades de la Cruz Roja y de la Media Luna Roja',
                    'published' => true,
                ]);
            }
        }

        $this->call(ApplicationTableSeeder::class);
        $this->call(WhatNowTableSeeder::class);
        $this->call(AlertsTableSeeder::class);
    }
}
