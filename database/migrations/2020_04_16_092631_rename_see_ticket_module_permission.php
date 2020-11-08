<?php

use App\Models\Permission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameSeeTicketModulePermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Permission::where('codename', 'tech_acc_see_technic_ticket_module')->update(['name' => 'Просмотр раздела Заявки на технику', 'category' => 13]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //we should rollback all changes here )
        Permission::where('codename', 'tech_acc_see_technic_ticket_module')->update(['name' => 'Просмотр раздела Учет Топлива', 'category' => 17]);
    }
}
