<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('service');
            $table->string('webhook_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('webhooks');
    }
};
