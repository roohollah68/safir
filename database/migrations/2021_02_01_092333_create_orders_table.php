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
            $table->foreignId('customer_id')->nullable()->default(null);
            $table->string('name');
            $table->string('address',300);
            $table->string('phone',30);
            $table->string('zip_code')->nullable();
            $table->string('orders');
            $table->string('desc',300)->nullable();
            $table->string('receipt')->nullable();
            $table->decimal('total',10,0);
            $table->enum('paymentMethod',['credit','receipt','onDelivery','admin']);
            $table->decimal('customerCost',3,0);
            $table->enum('deliveryMethod',['peyk','post','paskerayeh','admin']);
            $table->boolean('state')->default(false);
            $table->softDeletes();
            $table->timestamps();
            $table->UnsignedBigInteger('admin')->nullable();
            $table->foreign('admin')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
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
