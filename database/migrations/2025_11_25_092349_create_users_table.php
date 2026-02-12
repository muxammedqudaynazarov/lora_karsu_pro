<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->unique();
            $table->string('_role')->default('user');
            $table->json('user_roles')->default(json_encode(['user']));
            // 0 - deactivated, 1 - activated, 2 - block
            $table->enum('status', ['0', '1', '2', '3', '4'])->default('0');
            $table->string('verify_code')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
