<?php

use Cybex\LaravelMultiFactor\Enums\MultiFactorAuthMethod;
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
        Schema::create('multi_factor_auth_methods', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['email'])->unique();
            $table->timestamps();
        });
    }
};
