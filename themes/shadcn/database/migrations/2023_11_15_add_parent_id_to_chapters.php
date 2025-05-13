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
        if (!Schema::hasColumn('chapters', 'parent_id')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->integer('parent_id')->nullable()->default(null)->after('book_id');
                $table->index('parent_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('chapters', 'parent_id')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->dropColumn('parent_id');
            });
        }
    }
};