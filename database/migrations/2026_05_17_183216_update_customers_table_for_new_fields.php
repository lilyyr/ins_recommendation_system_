<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Drop old occupation column if it exists
            if (Schema::hasColumn('customers', 'occupation')) {
                $table->dropColumn('occupation');
            }

            // Add new fields
            $table->foreignId('occupation_id')->nullable()->after('num_dependents')->constrained('occupations')->onDelete('set null');
            $table->enum('marital_status', ['single', 'married'])->default('single')->after('gender');

            // Change income to string for categorical ranges
            if (Schema::hasColumn('customers', 'income')) {
                $table->dropColumn('income');
            }
            $table->enum('income_range', [
                'below_50m',
                '50m_100m',
                '100m_300m',
                '300m_500m',
                '500m_1b',
                'above_1b'
            ])->default('below_50m')->after('occupation_id');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['occupation_id']);
            $table->dropColumn(['occupation_id', 'marital_status', 'income_range']);
            $table->string('occupation')->nullable();
            $table->decimal('income', 15, 2)->default(0);
        });
    }
};
