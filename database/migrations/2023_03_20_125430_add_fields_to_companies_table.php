<?php

use App\Models\Company\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->bigInteger('ceo_employee_id')->unsigned()->nullable()->index()->after('email');
            $table->bigInteger('chief_engineer_employee_id')->unsigned()->nullable()->index()->after('email');

            $table->foreign('ceo_employee_id')->references('id')->on('employees');
            $table->foreign('chief_engineer_employee_id')->references('id')->on('employees');
        });

        Company::find(1)->update(['ceo_employee_id' => 1, 'chief_engineer_employee_id' => 185]);
        Company::find(2)->update(['ceo_employee_id' => 1, 'chief_engineer_employee_id' => 312]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['ceo_employee_id']);
            $table->dropForeign(['chief_engineer_employee_id']);

            $table->dropColumn('ceo_employee_id');
            $table->dropColumn('chief_engineer_employee_id');
        });
    }
}
