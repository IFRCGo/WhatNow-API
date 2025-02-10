<?php

use Illuminate\Database\Seeder;

class WhatNowTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$path = dirname(__FILE__) . '/whatnow_entities.csv';

		$file = new SplFileObject($path);;
		$file->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_CSV);

		foreach ($file as $row) {

			DB::table('whatnow_entities')->insert([
				'id' => $row[0],
				'org_id' => $row[1],
				'event_type' => $row[2],
				'created_at' => $row[3],
				'updated_at' => $row[4]
			]);

		}

		$path = dirname(__FILE__) . '/whatnow_entity_translations.csv';

		$file = new SplFileObject($path);;
		$file->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_CSV);

		foreach ($file as $row) {

			DB::table('whatnow_entity_translations')->insert([
				'id' => $row[0],
				'entity_id' => $row[1],
				'language_code' => $row[2],
				'title' => $row[3],
				'description' => $row[4],
				'web_url' => $row[5],
				'created_at' => $row[6],
				'published_at' => $row[7]
			]);

		}

		$path = dirname(__FILE__) . '/whatnow_entity_stages.csv';

		$file = new SplFileObject($path);;
		$file->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_CSV);

		foreach ($file as $row) {
			DB::table('whatnow_entity_stages')->insert([
				'id' => $row[0],
				'translation_id' => $row[1],
				'language_code' => $row[2],
				'stage' => $row[3]
			]);

		}

        $path = dirname(__FILE__) . '/regions.csv';

        $file = new SplFileObject($path);;
        $file->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_CSV);
        $timestamp = \Carbon\Carbon::now();

        foreach ($file as $row) {
            DB::table('regions')->insert([
                'id' => $row[0],
                'organisation_id' => $row[1],
                'title' => $row[2],
                'slug' => $row[3],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

        }

        $path = dirname(__FILE__) . '/region_translations.csv';

        $file = new SplFileObject($path);;
        $file->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_CSV);

        foreach ($file as $row) {
            DB::table('region_translations')->insert([
                'region_id' => $row[0],
                'language_code' => $row[1],
                'title' => $row[2],
                'description' => $row[3],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

        }

    }
}

