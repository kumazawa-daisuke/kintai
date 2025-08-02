<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectionRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date')->nullable();
            $table->string('request_type'); // edit, delete, etc.
            $table->string('reason');
            $table->string('status')->default('pending'); // pending/approved/rejected
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->time('clock_in_before')->nullable();
            $table->time('clock_in_after')->nullable();
            $table->time('clock_out_before')->nullable();
            $table->time('clock_out_after')->nullable();
            $table->text('reason_before')->nullable();
            $table->text('reason_after')->nullable();
            $table->json('breaks_before')->nullable();
            $table->json('breaks_after')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('correction_requests');
    }
}
