<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lexx\ChatMessenger\Models\Models;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn(Models::table('threads'), 'starred')) {
            Schema::table(Models::table('threads'), function (Blueprint $table) {
                $table->dropColumn('starred');
            });
        }

        if (! Schema::hasColumn(Models::table('participants'), 'starred')) {
            Schema::table(Models::table('participants'), function (Blueprint $table) {
                $table->boolean('starred')->default(false)->after('last_read');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn(Models::table('threads'), 'starred')) {
            Schema::table(Models::table('threads'), function (Blueprint $table) {
                $table->boolean('starred')->default(false)->after('id');
            });
        }

        if (Schema::hasColumn(Models::table('participants'), 'starred')) {
            Schema::table(Models::table('participants'), function (Blueprint $table) {
                $table->dropColumn('starred');
            });
        }
    }
};
