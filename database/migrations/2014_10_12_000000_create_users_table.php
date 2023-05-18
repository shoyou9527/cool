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
            $table->string('account')->unique()->comment('帳號');
            $table->string('password')->comment('密碼');
            $table->string('code')->unique()->comment('專屬代碼');
            $table->integer('role')->comment('社區(角色)');
            $table->string('name')->comment('使用者名稱');
            $table->string('email')->unique()->comment('信箱');
            $table->timestamp('email_verified_at')->nullable()->comment('信箱驗證');
            $table->string('gender')->comment('性別')->nullable();
            $table->string('phone')->comment('手機號碼')->nullable();
            $table->string('point')->comment('紅利點數')->nullable();
            $table->string('address')->comment('地址')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
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
