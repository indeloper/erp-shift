<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateObjectResponsibleUserRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('object_responsible_user_roles', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентфикатор');
            $table->string('slug')->index()->comment('Кодовое наименование');
            $table->string('name')->comment('Наименование');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE object_responsible_user_roles COMMENT 'Роли ответственных на объектах'");
        $this->uploadData();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('object_responsible_user_roles');
    }

    public function uploadData()
    {
        DB::table('object_responsible_user_roles')->insert([
            ['name' => 'Ответственный РП', 'slug' => 'TONGUE_PROJECT_MANAGER'],
            ['name' => 'Ответственный ПТО', 'slug' => 'TONGUE_PTO_ENGINEER'],
            ['name' => 'Ответственный прораб', 'slug' => 'TONGUE_FOREMAN'],    
        ]);
    }

}