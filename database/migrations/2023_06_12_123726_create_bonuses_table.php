<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('company_id')->default('')->comment('公司ID');
            $table->string('hours_threshold')->default('')->comment('時數門檻');
            $table->string('bonus_amount')->nullable()->comment('獎金金額');
            $table->timestamps();

            //添加外鍵約束
            $table->foreign('company_id')->references('id')->on('admin_users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bonuses');
    }
}
