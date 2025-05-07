<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->app->instance('middleware.disable', true);

        DB::delete('DELETE FROM applications');
    }

    /**
     * @return $this
     */
    public function test_it_lists_apps()
    {
        $app = \App\Models\Application::create([
            'tenant_user_id' => '999',
            'tenant_id' => 1,
            'name' => 'test',
            'description' => null,
            'key' => '1234567890',
        ]);

        $data = [
            'data' => [
                [
                    'id' => $app->id,
                    'name' => 'test',
                    'description' => null,
                    'key' => '1234567890',
                ],
            ],
        ];

        $response = $this->json('GET', '/' . config('app.api_version') . '/apps?userId=999');

        $response->assertStatus(200);

        $response->assertJsonFragment($data);
    }

    /**
     * @return $this
     */
    public function test_it_gets_app()
    {
        $app = \App\Models\Application::create([
            'tenant_user_id' => '999',
            'tenant_id' => 1,
            'name' => 'test',
            'description' => null,
            'key' => '1234567890',
        ]);

        $data = [
            'data' => [
                'id' => $app->id,
                'name' => 'test',
                'description' => null,
                'key' => '1234567890',
            ],
        ];

        $response = $this->json('GET', '/' . config('app.api_version') . '/apps/'.$app->id);

        $response->assertStatus(200);

        $response->assertJsonFragment($data);
    }

    /**
     * @return $this
     */
    public function test_it_deletes_app()
    {
        $app = \App\Models\Application::create([
            'tenant_user_id' => '999',
            'tenant_id' => 1,
            'name' => 'test',
            'description' => null,
            'key' => '1234567890',
        ]);

        $this->json('DELETE', '/' . config('app.api_version') . '/apps/'.$app->id)->assertStatus(200);

        $this->assertDatabaseHas('applications', [
            'id' => $app->id,
            'deleted_at' => Carbon::now()->toDateTimeString(),
        ]);
    }

    /**
     * @return $this
     */
    public function test_it_creates_app()
    {
        $this->markTestSkipped('Bug Request POST parameters lost when unit testing https://github.com/laravel/lumen-framework/issues/559 upgrade to Lumen 5.4.2+');

        $this->json('POST', '/' . config('app.api_version') . '/apps/', [
            'userId' => '55',
            'name' => 'Hello',
        ])->assertStatus(200);

        $this->assertDatabaseMissing('applications', [
            'tenant_user_id' => '55',
            'name' => 'Hello',
        ]);
    }
}
