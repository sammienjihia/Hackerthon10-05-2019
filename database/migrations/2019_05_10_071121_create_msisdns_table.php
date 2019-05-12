<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsisdnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('msisdns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('msisdn')->unique();
            $table->bigInteger('iccid');
            $table->float('balance')->default(0);
            $table->timestamps();

            $table->foreign('iccid')->references('id')->on('sims')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('msisdns');
    }
}
