<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('link_histories', function (Blueprint $table) {
            $table->foreign('link_id')->references('id')->on('links')->onUpdate('cascade')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('link_histories', function (Blueprint $table) {
            $table->dropForeign(['link_id']);
        });
    }
};
