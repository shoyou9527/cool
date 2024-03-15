<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentIdToAdminUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');
            $table->foreign('parent_id')->references('id')->on('admin_users')->onDelete('cascade');
            $table->integer('default_fee')->nullable()->after('avatar'); // Add this line to add default_fee column
            $table->boolean('lang')->default(0); // 0 代表中文，1 代表英文
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_users', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
            $table->dropColumn('default_fee'); // Add this line to drop default_fee column
            $table->dropColumn('lang'); // Add this line to drop default_fee column
        });
    }
}
