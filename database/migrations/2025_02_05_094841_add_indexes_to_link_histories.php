<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('link_histories', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('ip_address');
            $table->index('country_name');
            $table->index('browser');
            $table->index('os');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('link_histories', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['ip_address']);
            $table->dropIndex(['country_name']);
            $table->dropIndex(['browser']);
            $table->dropIndex(['os']);
            $table->dropIndex(['deleted_at']);
        });
    }
};

