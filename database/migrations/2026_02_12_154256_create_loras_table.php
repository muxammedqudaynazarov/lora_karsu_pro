<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loras', function (Blueprint $table) {
            $table->id();
            $table->string('deviceName');
            $table->string('devEUI');
            $table->string('electricity');
            $table->string('moisture');
            $table->string('temperature');
            $table->json('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loras');
    }
};
