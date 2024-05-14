<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLinkPropertyToSupportMail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('support_mails', function (Blueprint $table) {
            $table->text('gitlab_link')->nullable()->after('estimate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('support_mails', function (Blueprint $table) {
            $table->dropColumn('gitlab_link');
        });
    }
}
