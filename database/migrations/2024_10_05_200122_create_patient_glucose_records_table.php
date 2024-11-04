<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientGlucoseRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('patient_glucose_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users'); // Assuming patient is from the users table
            $table->timestamp('timestamp');
            $table->float('glucose_before');
            $table->float('food_carbo')->nullable();
            $table->float('insulin_dosage')->nullable();
            $table->float('glucose_after');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('patient_glucose_records');
    }
}
