<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('station', function (Blueprint $table) {
            $table->id()->unsigned();
            $table->string('name')->unique();
            $table->string('location');
        });

        Schema::create('route', function (Blueprint $table) {
            $table->id()->unsigned();
            $table->bigInteger('origin_station_id')->unsigned();
            $table->bigInteger('destination_station_id')->unsigned();
            $table->time('departure_time');
            $table->time('arrival_time');
            $table->foreign('origin_station_id')->references('id')->on('station');
            $table->foreign('destination_station_id')->references('id')->on('station');
        });

        Schema::create('route_stop', function (Blueprint $table) {
            $table->id()->unsigned();
            $table->bigInteger('route_id')->unsigned();
            $table->bigInteger('station_id')->unsigned();
            $table->integer('stop_order')->unsigned();
            $table->time('departure_time');
            $table->time('arrival_time');
            $table->foreign('route_id')->references('id')->on('route');
            $table->foreign('station_id')->references('id')->on('station');
        });

        Schema::create('service', function (Blueprint $table) {
            $table->id()->unsigned();
            $table->string('name')->unique();
            $table->decimal('price', 8, 2);
        });

        Schema::create('train', function (Blueprint $table) {
            $table->id()->unsigned();
            $table->string('name');
            $table->integer('capacity')->default(0);
            $table->string('code')->unique();
            $table->string('status');
            $table->string('type');
        });

        Schema::create('personal', function (Blueprint $table) {
            $table->id()->unsigned();
            $table->string('name');
            $table->string('role');
            $table->bigInteger('train_id')->unsigned();
            $table->foreign('train_id')->references('id')->on('train');
        });

        Schema::create('service_train', function (Blueprint $table) {
            $table->bigInteger('train_id')->unsigned();
            $table->bigInteger('service_id')->unsigned();
        });

        Schema::create('route_train', function (Blueprint $table) {
            $table->bigInteger('train_id')->unsigned();
            $table->bigInteger('route_id')->unsigned();
        });

        Schema::create('ticket', function (Blueprint $table) {
            $table->id()->unsigned();
            $table->decimal('total_amount', 8, 2);
            $table->dateTime('purchase_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->date('travel_date');
            $table->bigInteger('train_id')->unsigned();
            $table->bigInteger('route_id')->unsigned();
            $table->foreign('train_id')->references('id')->on('train');
            $table->foreign('route_id')->references('id')->on('route');
        });

        Schema::create('meta', function (Blueprint $table) {
            $table->id()->unsigned();
            $table->bigInteger('meta_id')->unsigned();
            $table->string('meta_key');
            $table->string('meta_format');
            $table->longText('meta_value');
            $table->index(['meta_id', 'meta_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop child tables first to satisfy foreign key constraints.
        Schema::dropIfExists('ticket');
        Schema::dropIfExists('route_train');
        Schema::dropIfExists('service_train');
        Schema::dropIfExists('route_stop');
        Schema::dropIfExists('personal');
        Schema::dropIfExists('meta');

        // Drop parent tables after all dependents are removed.
        Schema::dropIfExists('route');
        Schema::dropIfExists('service');
        Schema::dropIfExists('train');
        Schema::dropIfExists('station');
    }
};
