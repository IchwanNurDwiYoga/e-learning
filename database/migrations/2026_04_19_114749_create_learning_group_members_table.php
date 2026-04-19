<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('learning_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['leader', 'member'])->default('member');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_group_members', function (Blueprint $table) {
            $table->dropForeign(['learning_group_id', "user_id"]);
        });
        Schema::dropIfExists('learning_group_members');
    }
};
