<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 920);
            $table->string('instructions', 8000)->nullable();
            $table->boolean('commentaries')->default(false);
            $table->longText('question_structure')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->cascadeOnDelete();       
            $table->timestamps();
            $table->softDeletes();
            /* no se pueden repetir los dos de name y instructions */
        });
    }

    public function down()
    {
        Schema::dropIfExists('questions');
    }
};

