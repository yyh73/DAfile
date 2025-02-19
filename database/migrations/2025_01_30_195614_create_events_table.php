<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //移行を実行
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('event_title');
            $table->string('event_body');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('event_color');
            $table->string('event_border_color');
            $table->dateTime('reminder_time')->nullable();//リマインダー

            $table->timestamps();
        });


    }

    //移行をもとに戻す
    public function down(): void
    {
        Schema::dropIfExists('events');
    }

};
