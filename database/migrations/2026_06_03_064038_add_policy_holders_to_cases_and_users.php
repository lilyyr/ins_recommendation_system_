<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->foreignId('policy_holder_id')->nullable()->after('customer_id')->constrained('policy_holders')->nullOnDelete();
            $table->boolean('holder_is_insured')->default(true)->after('policy_holder_id');
            $table->string('holder_relationship_to_insured')->default('self')->after('holder_is_insured');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('policy_holder_id')->nullable()->after('id')->constrained('policy_holders')->nullOnDelete();
        });

        DB::table('weights')->insert([
            ['feature_name' => 'holder_income', 'weight' => 1.0],
            ['feature_name' => 'holder_relationship', 'weight' => 0.5],
        ]);
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropForeign(['policy_holder_id']);
            $table->dropColumn(['policy_holder_id', 'holder_is_insured', 'holder_relationship_to_insured']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['policy_holder_id']);
            $table->dropColumn('policy_holder_id');
        });

        DB::table('weights')->whereIn('feature_name', ['holder_income', 'holder_relationship'])->delete();
    }
};
