<?php

use CybexGmbh\LaravelTwoFactor\Enums\TwoFactorAuthMethod;
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
        Schema::create('two_factor_auth_methods', function (Blueprint $table) {
            $table->id();
            $table->enum('type', array_column(TwoFactorAuthMethod::cases(), 'value'))->unique();
            $table->timestamps();
        });
    }
};
