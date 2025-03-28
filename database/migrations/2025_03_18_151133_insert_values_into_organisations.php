<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
class InsertValuesIntoOrganisations extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('organisations')->insert([
            ['country_code' => 'GLB', 'org_name' => 'Global', 'oid_code' => 'urn:oid:2.49.0.4.1', 'attribution_url' => 'https://global.org/', 'attribution_file_name' => null],
            ['country_code' => 'AMR', 'org_name' => 'Americas', 'oid_code' => 'urn:oid:2.49.0.4.2', 'attribution_url' => 'https://americas.org/', 'attribution_file_name' => null],
            ['country_code' => 'APC', 'org_name' => 'Asia Pacific', 'oid_code' => 'urn:oid:2.49.0.4.3', 'attribution_url' => 'https://asiapacific.org/', 'attribution_file_name' => null],
            ['country_code' => 'MNA', 'org_name' => 'MENA', 'oid_code' => 'urn:oid:2.49.0.4.4', 'attribution_url' => 'https://mena.org/', 'attribution_file_name' => null],
            ['country_code' => 'EUR', 'org_name' => 'Europe', 'oid_code' => 'urn:oid:2.49.0.4.5', 'attribution_url' => 'https://europe.org/', 'attribution_file_name' => null],
            ['country_code' => 'AFR', 'org_name' => 'Africa', 'oid_code' => 'urn:oid:2.49.0.4.6', 'attribution_url' => 'https://africa.org/', 'attribution_file_name' => null],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('organisations')
            ->whereIn('country_code', ['GLB', 'AMR', 'APC', 'MNA', 'EUR', 'AFR'])
            ->delete();
    }
}
