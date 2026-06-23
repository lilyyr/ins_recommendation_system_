<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_holders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('dob');
            $table->enum('gender', ['male', 'female']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_holders');
    }
};
