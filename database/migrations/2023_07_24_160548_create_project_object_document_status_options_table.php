<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('project_object_document_status_options', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентфикатор');

            $table->unsignedBigInteger('document_type_id')->comment('ID типа документа');
            $table->foreign('document_type_id', 'document_type_id_foreign')->references('id')->on('project_object_document_types');

            $table->unsignedBigInteger('document_status_id')->nullable()->comment('ID статуса документа');
            $table->foreign('document_status_id', 'document_status_id_foreign')->references('id')->on('project_object_document_statuses');

            $table->json('options')->nullable()->comment('Параметры формы дополнительные');

            $table->timestamps();
        });

        DB::statement("ALTER TABLE project_object_document_status_options COMMENT 'Дополнительные поля ввода формы в модуле «Документооборот на объектах»'");

        $this->uploadData();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('project_object_document_status_options');
    }

    public function uploadData()
    {
        DB::table('project_object_document_status_options')->insert([

            [
                'document_type_id' => 1,
                'document_status_id' => null,
                'options' => json_encode([
                    [
                        'type' => 'checkbox',
                        'id' => 'rd_to_production',
                        'label' => 'В производство',
                    ],
                ]),
            ],

            [
                'document_type_id' => 1,
                'document_status_id' => 9,
                'options' => json_encode([
                    [
                        'type' => 'select',
                        'id' => 'rd_who_recieved',
                        'label' => 'Кем получен',
                        'source' => 'responsible_managers_and_pto',
                    ],
                ]),
            ],

            [
                'document_type_id' => 2,
                'document_status_id' => 8,
                'options' => json_encode([
                    [
                        'type' => 'select',
                        'id' => 'act_who_recieved',
                        'label' => 'Кому передан',
                        'source' => 'responsible_managers_and_pto',
                    ],
                ]),
            ],

            [
                'document_type_id' => 3,
                'document_status_id' => 8,
                'options' => json_encode([
                    [
                        'type' => 'select',
                        'id' => 'log_who_recieved',
                        'label' => 'Кому передан',
                        'source' => 'responsible_managers_and_pto',
                    ],
                ]),
            ],

            [
                'document_type_id' => 4,
                'document_status_id' => null,
                'options' => json_encode([
                    [
                        'type' => 'checkbox',
                        'id' => 'ppr_confirmed_digital_format',
                        'label' => 'Согласован электронный вид',
                    ],
                    [
                        'type' => 'checkbox',
                        'id' => 'ppr_confirmed_paper_format',
                        'label' => 'Согласован в бумаге',
                    ],
                ]),
            ],

            [
                'document_type_id' => 5,
                'document_status_id' => null,
                'options' => json_encode([
                    [
                        'type' => 'checkbox',
                        'id' => 'id_document_signed',
                        'label' => 'Подписан',
                    ],
                ]),
            ],

            [
                'document_type_id' => 5,
                'document_status_id' => 6,
                'options' => json_encode([
                    [
                        'type' => 'select',
                        'id' => 'id_who_recieved',
                        'label' => 'Кому',
                        'source' => 'responsible_managers_and_foremen',
                    ],
                ]),
            ],

            [
                'document_type_id' => 5,
                'document_status_id' => 7,
                'options' => json_encode([
                    [
                        'type' => 'text',
                        'id' => 'id_delivered_to_customer',
                        'label' => 'Кому',
                    ],
                ]),
            ],

            [
                'document_type_id' => 5,
                'document_status_id' => 8,
                'options' => json_encode([
                    [
                        'type' => 'select',
                        'id' => 'id_who_recieved',
                        'label' => 'Кому передан',
                        'source' => 'responsible_managers_and_pto',
                    ],
                ]),
            ],

            [
                'document_type_id' => 6,
                'document_status_id' => null,
                'options' => json_encode([
                    [
                        'type' => 'checkbox',
                        'id' => 'performance_document_signed',
                        'label' => 'Подписан',
                    ],
                ]),
            ],

            [
                'document_type_id' => 6,
                'document_status_id' => 6,
                'options' => json_encode([
                    [
                        'type' => 'select',
                        'id' => 'performance_who_recieved',
                        'label' => 'Кому',
                        'source' => 'responsible_managers_and_foremen',
                    ],
                ]),
            ],

            [
                'document_type_id' => 6,
                'document_status_id' => 7,
                'options' => json_encode([
                    [
                        'type' => 'text',
                        'id' => 'performance_delivered_to_customer',
                        'label' => 'Кому',
                    ],
                ]),
            ],

            [
                'document_type_id' => 6,
                'document_status_id' => 8,
                'options' => json_encode([
                    [
                        'type' => 'select',
                        'id' => 'performance_who_recieved',
                        'label' => 'Кому передан',
                        'source' => 'responsible_managers_and_pto',
                    ],
                ]),
            ],

        ]);
    }
};
