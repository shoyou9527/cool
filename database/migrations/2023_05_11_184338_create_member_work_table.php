<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMemberWorkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_work', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('admin_user_id')->comment('會員關聯ID');
            $table->string('admin_user_name')->comment('會員關聯名稱');
            $table->date('work_date')->nullable()->comment('工作日期');
            $table->time('work_start_time')->nullable()->comment('上班時間');
            $table->time('work_end_time')->nullable()->comment('下班時間');
            $table->integer('total_hours')->nullable()->comment('總工作小時數');
            $table->integer('hourly_rate')->nullable()->comment('時薪');
            $table->integer('record_salary')->nullable()->comment('此筆紀錄薪水');
            $table->integer('sale_price')->nullable()->comment('從客收多少');
            $table->text('note')->nullable()->comment('備註');
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
        Schema::dropIfExists('member_work');
    }
}
