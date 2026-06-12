<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('email', 55);
            $table->string('token', 45);
            $table->enum('type', ['verification', 'reset']);
            $table->dateTime('expiration');
            $table->tinyInteger('status')->default(0); 
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users_tokens');
    }
};

