<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('name');
            $table->string('address',300);
            $table->string('phone',30);
            $table->string('zip_code')->nullable();
            $table->string('orders');
            $table->string('desc',300)->nullable();
            $table->string('receipt')->nullable();
            $table->decimal('total',10,0);
            $table->boolean('fromCredit');
            $table->enum('state',['0','1','2','3','4','5','6'])->default('0');
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
        Schema::dropIfExists('orders');
    }
}
