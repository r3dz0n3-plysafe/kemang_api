<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('kmg_sewas', function (Blueprint $table) {
            $table->id('kmg_id');
            $table->string('kmg_floor');
            $table->string('kmg_unit');
            $table->string('kmg_periode');
            $table->string('kmg_price');
            $table->timestamp('kmg_check_in');
            $table->string('kmg_agent');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('kmg_sewas');
    }
};
