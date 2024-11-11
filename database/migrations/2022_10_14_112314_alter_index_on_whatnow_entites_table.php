<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterIndexOnWhatnowEntitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('whatnow_entities', function (Blueprint $table) {
            $table->dropForeign('whatnow_entities_org_id_foreign');
            $table->dropUnique(['org_id', 'event_type']);
            $table->unique(['org_id', 'event_type', 'region_id']);
            $table->index(['org_id', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('whatnow_entities', function (Blueprint $table) {
            $table->dropUnique(['org_id', 'event_type', 'region_id']);
            $table->unique(['org_id', 'event_type']);
        });
    }
}
