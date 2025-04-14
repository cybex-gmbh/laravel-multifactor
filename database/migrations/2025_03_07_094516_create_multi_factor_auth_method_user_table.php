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
        Schema::create('multi_factor_auth_method_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('multi_factor_auth_method_id');
            $table->timestamps();

            $table->foreign('user_id', 'multi_factor_auth_method_user_user_id_fk')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('multi_factor_auth_method_id', 'multi_factor_auth_method_user_auth_method_id_fk')
                ->references('id')
                ->on('multi_factor_auth_methods')
                ->onDelete('cascade');
        });
    }
};
