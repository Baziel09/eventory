<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Drop event_id from locations
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropColumn('event_id');
        });

        // 2. Add new location_id column to events
        Schema::table('events', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable()->after('location'); // place after old column
        });

        // 3. Copy data from old 'location' to 'location_id
        DB::statement('UPDATE events SET location_id = location');

        // 4. Drop old column
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('location');
        });

        // 5. Add foreign key constraint to new location_id
        Schema::table('events', function (Blueprint $table) {
            $table->unsignedBigInteger('location_id')->nullable(false)->change(); // this line still needs doctrine/dbal
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
        });
    }

    public function down()
    {
        // Drop foreign key constraint from location_id
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
        });

        // Add new location column to events
        Schema::table('events', function (Blueprint $table) {
            $table->integer('location')->nullable(); // adjust type as needed
        });

        // Copy data back from location_id to location
        Schema::table('locations', function (Blueprint $table) {
            $table->unsignedBigInteger('event_id')->nullable();
            $table->foreign('event_id')->references('id')->on('events')->onDelete('set null');
        });
    }
};