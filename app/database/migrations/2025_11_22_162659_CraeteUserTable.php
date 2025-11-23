<?php

use System\Database\Migration\Migration;
use System\Database\Migration\Blueprint;

return new class extends Migration {
    public function up(): void
    {
        $this->createTable('users', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->dropTable('users');
    }
};
