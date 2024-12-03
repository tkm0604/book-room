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
        Schema::table('users', function (Blueprint $table) {
            $table->string('twitter_id')->unique()->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // カラムが存在する場合のみ削除する
        if (Schema::hasColumn('users', 'twitter_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('twitter_id');
            });
        }
    }
};
