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
        Schema::create('url_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('url_id')->constrained('urls')->onDelete('cascade');
            $table->string('geographic_location', 100)->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('referrer', 500)->nullable();
            $table->timestamp('clicked_at')->useCurrent();
            $table->timestamps();

            // Indexes for performance
            $table->index('url_id');
            $table->index('clicked_at');
            $table->index(['url_id', 'clicked_at']);
            $table->index('geographic_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('url_analytics');
    }
};
