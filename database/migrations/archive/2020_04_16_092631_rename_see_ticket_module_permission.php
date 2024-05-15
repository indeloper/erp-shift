<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Permission::where('codename', 'tech_acc_see_technic_ticket_module')->update(['name' => 'Просмотр раздела Заявки на технику', 'category' => 13]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        //we should rollback all changes here )
        Permission::where('codename', 'tech_acc_see_technic_ticket_module')->update(['name' => 'Просмотр раздела Учет Топлива', 'category' => 17]);
    }
};
