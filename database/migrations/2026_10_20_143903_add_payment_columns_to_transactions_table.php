<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('subtotal', 14, 2)->default(0)->after('transaction_date');
            $table->string('discount_type', 10)->default('amount')->after('subtotal');
            $table->decimal('discount_value', 14, 2)->default(0)->after('discount_type');
            $table->decimal('discount_amount', 14, 2)->default(0)->after('discount_value');
            $table->decimal('tax_percent', 5, 2)->default(0)->after('discount_amount');
            $table->decimal('tax_amount', 14, 2)->default(0)->after('tax_percent');
            $table->string('payment_method', 20)->default('cash')->after('total');
            $table->decimal('paid_amount', 14, 2)->default(0)->after('payment_method');
            $table->decimal('change_amount', 14, 2)->default(0)->after('paid_amount');
            $table->string('payment_status', 20)->default('unpaid')->after('change_amount');
        });

        DB::table('transactions')->update(['subtotal' => DB::raw('total')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal',
                'discount_type',
                'discount_value',
                'discount_amount',
                'tax_percent',
                'tax_amount',
                'payment_method',
                'paid_amount',
                'change_amount',
                'payment_status',
            ]);
        });
    }
};
