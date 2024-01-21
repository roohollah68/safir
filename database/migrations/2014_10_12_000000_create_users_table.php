<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('phone');
            $table->string('telegram_id')->nullable();
            $table->boolean('verified')->default(false);
            $table->enum('role' , ['admin' , 'user' , 'print' , 'warehouse'])->default('user');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('telegram_code');
            $table->decimal('balance',10,0)->default(0);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
