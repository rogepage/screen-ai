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
        Schema::create('analysis_histories', function (Blueprint $table) {
            $table->id();
            $table->string('action_type')->index();
            $table->string('provider');
            $table->string('model')->nullable();
            $table->string('status')->index();
            $table->longText('response_text')->nullable();
            $table->text('error_message')->nullable();
            $table->double('duration_ms')->nullable();
            $table->text('user_instruction')->nullable();
            $table->string('language')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_histories');
    }
};
