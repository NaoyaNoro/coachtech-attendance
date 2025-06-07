<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreakCorrectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('break_corrects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_correct_id')->constrained()->cascadeOnDelete();
            $table->foreignId('break_id')->constrained()->cascadeOnDelete();
            $table->timestamp('default_start');
            $table->timestamp('default_end');
            $table->timestamp('requested_start')->nullable();
            $table->timestamp('requested_end')->nullable();
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->useCurrent()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('break_corrects');
    }
}
