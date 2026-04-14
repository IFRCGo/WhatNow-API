<?php

use Illuminate\Database\Seeder;
use App\Models\Application;

class ApplicationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $applications = [
            [
                'tenant_id' => 1,
                'tenant_user_id' => 1,
                'name' => 'Test App - No Rules',
                'description' => 'App with no policy rules (full access)',
                'key' => Application::generateKey(),
                'is_active' => true,
                'rules' => null,
            ],
            [
                'tenant_id' => 1,
                'tenant_user_id' => 2,
                'name' => 'Test App - All Countries, Preparedness V2 Only',
                'description' => 'No country restriction, legacy disabled, preparedness v2 enabled',
                'key' => Application::generateKey(),
                'is_active' => true,
                'rules' => [
                    'allowed_country_code' => [],
                    'can_access_legacy_whatnow' => false,
                    'can_access_preparedness_v2' => true,
                ],
            ],
            [
                'tenant_id' => 1,
                'tenant_user_id' => 3,
                'name' => 'IRCS WhatNow Messages',
                'description' => 'IRCS, NHQ WhatNow Messages — India only, preparedness v2',
                'key' => 'PYxJ6xCp2mdftP4FOj0mEDiKAqj1OulC',
                'is_active' => true,
                'rules' => [
                    'allowed_country_code' => ['IND'],
                    'can_access_legacy_whatnow' => false,
                    'can_access_preparedness_v2' => true,
                ],
            ],
            [
                'tenant_id' => 1,
                'tenant_user_id' => 4,
                'name' => 'Test App - USA, Legacy + Preparedness V2',
                'description' => 'USA only, legacy enabled, preparedness v2 enabled',
                'key' => Application::generateKey(),
                'is_active' => true,
                'rules' => [
                    'allowed_country_code' => ['USA'],
                    'can_access_legacy_whatnow' => true,
                    'can_access_preparedness_v2' => true,
                ],
            ],
            [
                'tenant_id' => 1,
                'tenant_user_id' => 5,
                'name' => 'Test App - All Countries, Legacy + Preparedness V2',
                'description' => 'No country restriction, legacy enabled, preparedness v2 enabled',
                'key' => Application::generateKey(),
                'is_active' => true,
                'rules' => [
                    'allowed_country_code' => [],
                    'can_access_legacy_whatnow' => true,
                    'can_access_preparedness_v2' => true,
                ],
            ],
            [
                'tenant_id' => 1,
                'tenant_user_id' => 6,
                'name' => 'Test App - USA, Legacy Only',
                'description' => 'USA only, legacy enabled, preparedness v2 disabled',
                'key' => Application::generateKey(),
                'is_active' => true,
                'rules' => [
                    'allowed_country_code' => ['USA'],
                    'can_access_legacy_whatnow' => true,
                    'can_access_preparedness_v2' => false,
                ],
            ],
        ];

        foreach ($applications as $data) {
            Application::create($data);
        }
    }
}

