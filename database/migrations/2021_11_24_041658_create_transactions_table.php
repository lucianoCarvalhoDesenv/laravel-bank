<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('owner');
            $table->char('type', 1);//D or P
            $table->char('approved', 1);//Y or N or W or R
            $table->double('amount', 15, 2);       
            $table->bigInteger('order') ;
            $table->double('balance_after', 15, 2)->nullable();
            $table->bigInteger('pre_transaction')->nullable()->unique();
            $table->dateTime('date');
            $table->longText('imgurl')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('transactions');
    }
}
