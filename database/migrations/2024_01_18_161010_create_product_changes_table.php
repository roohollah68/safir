<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id');
            $table->foreignId('order_id')->nullable();
            $table->integer('change')->default(0);
            $table->integer('quantity');
            $table->boolean('isDeleted')->default(false);
            $table->text('desc')->nullable();
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
        Schema::dropIfExists('product_changes');
    }
}
