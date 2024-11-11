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
        Application::create([
            'tenant_id' => 1,
            'tenant_user_id' => 1,
            'name' => 'Test App',
            'description' => 'App used for testing',
            'key' => '1234'
        ]);
    }
}

