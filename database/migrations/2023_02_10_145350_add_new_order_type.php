<?php

use App\Models\LaborSafety\LaborSafetyOrderType;
use App\Models\LaborSafety\LaborSafetyOrderTypeCategory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $laborSafetyOrderTypeCategoryArray = [
            'Приказ о назначении ответственных за работы на высоте',
        ];
        foreach ($laborSafetyOrderTypeCategoryArray as $laborSafetyOrderTypeCategoryElement) {
            $laborSafetyOrderTypeCategory = new LaborSafetyOrderTypeCategory([
                'name' => $laborSafetyOrderTypeCategoryElement,
            ]);
            $laborSafetyOrderTypeCategory->save();
        }

        $laborSafetyOrderTypesArray = ['Ответственный за работы на высоте‡В‡О назначении ответственного за организацию и безопасное производство работ на высоте, выдачу нарядов-допусков на работы на высоте‡13‡<p>&nbsp;</p><p style="text-align: center; font-size: 20px;"><strong>ПРИКАЗ №{request_id}-{template_short_name}</strong></p><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p>г. Санкт-Петербург</p></td><td><p style="text-align: right;">{pretty_order_date}</p></td></tr></tbody></table><p>&nbsp;</p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: justify;">&laquo;О назначении ответственного за организацию и безопасное производство работ на высоте, выдачу нарядов-допусков на работы на высоте при выполнении полного комплекса строительных работ по устройству шпунтового ограждения котлована на строительном объекте: &laquo;{project_object_name}&raquo;, расположенном по адресу: {project_object_full_address}{project_object_cadastral_number}&raquo;</p></td><td style="width: 9.67188px;"><p>&nbsp;</p></td></tr></tbody></table><p>&nbsp;</p><p style="text-align: justify;">В соответствии с требованиями Трудового кодекса РФ и приказа Министерства труда и социальной защиты Российской Федерации от 16.11.2020 г. № 782н &laquo;Об утверждении Правил по охране труда при работе на высоте&raquo;,</p><p>&nbsp;</p><p><strong>ПРИКАЗЫВАЮ:</strong></p><ol><li>Назначить ответственным за организацию и безопасное производство работ на высоте на строительном объекте {object_responsible_user_post_name} {object_responsible_user_full_name}</li><li>{object_responsible_user_post_name}&nbsp; {object_responsible_user_post_name_initials_after} обеспечить:<br /><ol><li style="text-align: left;">Контроль за соблюдением работниками требований законодательства Российской Федерации, правил внутреннего трудового распорядка, инструкций по охране труда и иных локальных нормативных актов, действующих в компании;</li><li style="text-align: left;">Принятие мер по предотвращению аварийных ситуаций на рабочих местах подчинённого персонала, сохранению жизни и здоровья подчинённых работников при возникновении таких ситуаций, в том числе по оказанию пострадавшим первой помощи;</li><li style="text-align: left;">Составление и актуализацию плана мероприятий по эвакуации и спасению работников при возникновении аварийной ситуации и при проведении спасательных работ;</li><li style="text-align: left;">Безопасность работников при эксплуатации зданий, сооружений, оборудования, осуществлении технологических процессов, применении в производстве работ инструментов, сырья и материалов, соответствующих требованиям охраны труда на каждом рабочем месте;</li><li style="text-align: left;">Режим труда и отдыха подчиненных работников в соответствии с трудовым законодательством Российской Федерации и правилами внутреннего трудового распорядка;</li><li style="text-align: left;">Незамедлительное уведомление директора по строительству Левичева С. А. для принятия мер по отстранению (недопущению к работе) подчинённого работника, появившегося на работе в состоянии алкогольного, наркотического или токсического опьянения;</li><li style="text-align: left;">Контроль за состоянием условий труда на рабочих местах, а также правильностью применения и использованием подчинёнными работниками специальной одежды, специальной обуви и других средств индивидуальной защиты;</li><li style="text-align: left;">Своевременное, незамедлительное оповещение в установленном порядке о несчастных случаях на производстве, произошедших с подчинённым персоналом;</li><li style="text-align: left;">Ограждение мест производства работ, наличие предупреждающих и предписывающих плакатов (знаков), использование средств коллективной и индивидуальной защиты;</li><li style="text-align: left;">Проведение работ на высоте в соответствии с требованиями выданного и оформленного в установленном порядке наряда-допуска;</li></ol></li><li style="text-align: left;">Ответственному не допускать работников к выполнению работ на высоте:<ol><li style="text-align: left;">В открытых местах при скорости воздушного потока (ветра) 15 м/с и более;</li><li style="text-align: left;">При грозе или тумане, исключающем видимость в пределах фронта работ, а также при гололеде с обледенелых конструкций и в случаях нарастания стенки гололеда на проводах, оборудовании, инженерных конструкциях (в том числе опорах линий электропередачи), деревьях;</li><li style="text-align: left;">При монтаже (демонтаже) конструкций с большой парусностью при скорости ветра 10 м/с и более;</li><li style="text-align: left;">Не обученных правилам по охране труда при работе на высоте в установленном порядке, а также работников, имеющих медицинские противопоказания к данному виду работ;</li></ol></li><li style="text-align: left;">Лицом, имеющем право оформления и выдачи нарядов-допусков на производство работ на высоте назначить {object_responsible_user_post_name} {object_responsible_user_post_name_initials_after};</li><li style="text-align: left;">Ответственному лицу руководителю проектов {object_responsible_user_post_name} {object_responsible_user_post_name_initials_after} осуществлять контроль за выполнением предусмотренных в наряде-допуске мероприятий по обеспечению безопасности производства работ;</li><li style="text-align: left;">Ответственным руководителем работ на высоте, в том числе выполняемых с оформлением наряда-допуска, назначить {foreman_user_post_name} {foreman_user_full_name}[optional-section-start|subresponsible_foreman], а в его отсутствие (отпуск, болезнь, командировка) {sub_foreman_user_post_name} {sub_foreman_user_full_name}[optional-section-end|subresponsible_foreman];</li><li style="text-align: left;">Ответственными исполнителями при производстве работ на высоте, в том числе выполняемых с оформлением наряда-допуска, назначить работников, прошедших специальную подготовку в установленном порядке:<br />{workers_list}</li><li style="text-align: left;">Ответственным в своей работе руководствоваться Правилами по охране труда при работе на высоте, утверждёнными Приказом Министерства труда и социальной защиты Российской Федерации от 16.11.2020 г. № 782н, и другими действующими нормативными правовыми актами по охране труда;</li><li style="text-align: left;">Контроль за исполнением настоящего приказа оставляю за собой.</li></ol><p><br /><br /><br /></p><table style="width: 100%;"><tbody><tr><td style="width: 50%;"><p style="text-align: left;">Генеральный директор</p></td><td><p style="text-align: right;">М. Д. Исмагилов</p></td></tr></tbody></table><br /><br /><br />{sign_list}'];

        foreach ($laborSafetyOrderTypesArray as $laborSafetyOrderTypeElement) {
            $laborSafetyOrderType = new LaborSafetyOrderType([
                'name' => explode('‡', $laborSafetyOrderTypeElement)[0],
                'short_name' => explode('‡', $laborSafetyOrderTypeElement)[1],
                'full_name' => explode('‡', $laborSafetyOrderTypeElement)[2],
                'order_type_category_id' => explode('‡', $laborSafetyOrderTypeElement)[3],
                'template' => explode('‡', $laborSafetyOrderTypeElement)[4],
            ]);
            $laborSafetyOrderType->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
