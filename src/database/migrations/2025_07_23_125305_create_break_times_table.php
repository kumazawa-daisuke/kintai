<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreakTimesTable extends Migration
{
    public function up()
    {
        Schema::create('break_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('attendances')->onDelete('cascade');
            $table->time('break_start');
            $table->time('break_end')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('break_times');
    }
}
