<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->text('message');
            $table->dateTime('sent_at');
            $table->dateTime('received_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
