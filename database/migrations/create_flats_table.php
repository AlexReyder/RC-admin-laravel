<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flats', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('rooms_number');
            $table->unsignedInteger('rooms_number_true');
            $table->unsignedInteger('floor');
            $table->double('square', 8, 2);

            $table->timestamps();

            $table->unsignedInteger('entrance_number');
            $table->double('living_square', 8, 2);
            $table->double('ceiling_height', 8, 2);

            $table->string('plan')->nullable();

            $table->boolean('sold')->default(false);

            $table->integer('building')->default(1);
            $table->integer('number')->default(1);

            $table->integer('price')->default(0);
            $table->integer('price_m2')->default(0);

            $table->string('floor_position')->nullable();

            $table->string('finish_date');
            $table->string('finishing');

            $table->boolean('action')->default(false);
            $table->integer('action_price_m2')->default(0);

            $table->string('title');
            $table->string('description');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flats');
    }
};