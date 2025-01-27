<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('link_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('link_id')->nullable()->index();
            $table->string('country_name', 255);
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->string('browser', 255);
            $table->string('os', 255);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('link_histories');
    }
};
