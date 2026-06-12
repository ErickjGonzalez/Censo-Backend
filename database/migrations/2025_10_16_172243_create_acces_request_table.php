<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('acces_request', function (Blueprint $table) {
            $table->id();
            $table->string('email', 55);
            $table->integer('attemps');           
            $table->timestamp('date');            
            $table->string('ip_adress', 45);       
            $table->tinyInteger('valid')->default(0); 
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('acces_request');
    }
};

