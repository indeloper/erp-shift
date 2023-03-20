<?php

use App\Models\Company\Company;
use App\Models\Employees\Employees1cPost;
use App\Models\Employees\Employees1cPostInflection;
use App\Models\Employees\Employees1cSubdivision;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\Environment\Console;

class employeePostsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $posts = json_decode('{"data": [{
"postUID": "32a3e7b1-e427-11e9-8132-00155d630402",
"postName": "Инженер ПТО",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Инженер ПТО",
"genitive": "Инженер ПТО",
"dative": "Инженер ПТО",
"accusative": "Инженер ПТО",
"ablative": "Инженер ПТО",
"prepositional": "Инженер ПТО"
}
},
{
"postUID": "4983b011-9010-11ed-80bb-000c29565159",
"postName": "Специалист по охране труда и промышленной безопасности",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Специалист по охране труда и промышленной безопасности",
"genitive": "Специалиста по охране труда и промышленной безопасности",
"dative": "Специалисту по охране труда и промышленной безопасности",
"accusative": "Специалиста по охране труда и промышленной безопасности",
"ablative": "Специалистом по охране труда и промышленной безопасности",
"prepositional": "Специалисте по охране труда и промышленной безопасности"
}
},
{
"postUID": "85db1cf5-9d6d-11e9-812f-00155d630402",
"postName": "Генеральный директор",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Генеральный директор",
"genitive": "Генеральный директор",
"dative": "Генеральный директор",
"accusative": "Генеральный директор",
"ablative": "Генеральный директор",
"prepositional": "Генеральный директор"
}
},
{
"postUID": "a092b52a-8c9f-11eb-8100-1831bfcfda3c",
"postName": "Помощник машиниста буровой установки",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Помощник машиниста буровой установки",
"genitive": "Помощника машиниста буровой установки",
"dative": "Помощнику машиниста буровой установки",
"accusative": "Помощника машиниста буровой установки",
"ablative": "Помощником машиниста буровой установки",
"prepositional": "Помощнике машиниста буровой установки"
}
},
{
"postUID": "77b66264-4c4f-11ec-8107-1831bfcfda3c",
"postName": "Менеджер проектов",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Менеджер проектов",
"genitive": "Менеджера проектов",
"dative": "Менеджеру проектов",
"accusative": "Менеджер проектов",
"ablative": "Менеджером проектов",
"prepositional": "Менеджере проектов"
}
},
{
"postUID": "",
"postName": "",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "",
"genitive": "",
"dative": "",
"accusative": "",
"ablative": "",
"prepositional": ""
}
},
{
"postUID": "c68964c9-5f34-11ec-8107-1831bfcfda3c",
"postName": "Начальник сметно-экономического отдела",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Начальник сметно-экономического отдела",
"genitive": "Начальника сметно-экономического отдела",
"dative": "Начальнику сметно-экономического отдела",
"accusative": "Начальника сметно-экономического отдела",
"ablative": "Начальником сметно-экономического отдела",
"prepositional": "Начальнике сметно-экономического отдела"
}
},
{
"postUID": "d678693f-8ca0-11eb-8100-1831bfcfda3c",
"postName": "Машинист буровой установки",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Машинист буровой установки",
"genitive": "Машиниста буровой установки",
"dative": "Машинисту буровой установки",
"accusative": "Машиниста буровой установки",
"ablative": "Машинистом буровой установки",
"prepositional": "Машинисте буровой установки"
}
},
{
"postUID": "e0b0526e-9d8e-11e9-812f-00155d630402",
"postName": "Руководитель проектов",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Руководитель проектов",
"genitive": "Руководителя проектов",
"dative": "Руководителю проектов",
"accusative": "Руководителя проектов",
"ablative": "Руководителем проектов",
"prepositional": "Руководителе проектов"
}
},
{
"postUID": "d3dc5e62-9e35-11e9-812f-00155d630402",
"postName": "Стропальщик",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Стропальщик",
"genitive": "Стропальщика",
"dative": "Стропальщику",
"accusative": "Стропальщика",
"ablative": "Стропальщиком",
"prepositional": "Стропальщике"
}
},
{
"postUID": "ad831a46-fd05-11ec-810a-1831bfcfda3c",
"postName": "Начальник тендерного отдела",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Начальник тендерного отдела",
"genitive": "Начальника тендерного отдела",
"dative": "Начальнику тендерного отдела",
"accusative": "Начальника тендерного отдела",
"ablative": "Начальником тендерного отдела",
"prepositional": "Начальнике тендерного отдела"
}
},
{
"postUID": "d3dc5e61-9e35-11e9-812f-00155d630402",
"postName": "Геодезист",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Геодезист",
"genitive": "Геодезиста",
"dative": "Геодезисту",
"accusative": "Геодезиста",
"ablative": "Геодезистом",
"prepositional": "Геодезисте"
}
},
{
"postUID": "d3dc5e5e-9e35-11e9-812f-00155d630402",
"postName": "Главный бухгалтер",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Главный бухгалтер",
"genitive": "Главного бухгалтера",
"dative": "Главному бухгалтеру",
"accusative": "Главного бухгалтера",
"ablative": "Главным бухгалтером",
"prepositional": "Главном бухгалтере"
}
},
{
"postUID": "ffb35dd2-d288-11ec-810a-1831bfcfda3c",
"postName": "Машинист копра",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Машинист копра",
"genitive": "Машиниста копра",
"dative": "Машинисту копра",
"accusative": "Машиниста копра",
"ablative": "Машинистом копра",
"prepositional": "Машинисте копра"
}
},
{
"postUID": "7e44eae7-2ff1-11ec-8107-1831bfcfda3c",
"postName": "Директор по строительству",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Директор по строительству",
"genitive": "Директора по строительству",
"dative": "Директору по строительству",
"accusative": "Директора по строительству",
"ablative": "Директором по строительству",
"prepositional": "Директоре по строительству"
}
},
{
"postUID": "e0b0526a-9d8e-11e9-812f-00155d630402",
"postName": "Машинист крана (крановщик)",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Машинист крана (крановщик)",
"genitive": "Машиниста крана (крановщика)",
"dative": "Машинисту крана (крановщику)",
"accusative": "Машиниста крана (крановщика)",
"ablative": "Машинистом крана (крановщиком)",
"prepositional": "Машинисте крана (крановщике)"
}
},
{
"postUID": "e0b0526d-9d8e-11e9-812f-00155d630402",
"postName": "Производитель работ",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Производитель работ",
"genitive": "Производителя работ",
"dative": "Производителю работ",
"accusative": "Производителя работ",
"ablative": "Производителем работ",
"prepositional": "Производителе работ"
}
},
{
"postUID": "5ae4e864-a5eb-11ed-80bb-000c29565159",
"postName": "Главный механик",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Главный механик",
"genitive": "Главного механика",
"dative": "Главному механику",
"accusative": "Главного механика",
"ablative": "Главным механиком",
"prepositional": "Главном механике"
}
},
{
"postUID": "e0b0526b-9d8e-11e9-812f-00155d630402",
"postName": "Электрогазосварщик",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Электрогазосварщик",
"genitive": "Электрогазосварщика",
"dative": "Электрогазосварщику",
"accusative": "Электрогазосварщика",
"ablative": "Электрогазосварщиком",
"prepositional": "Электрогазосварщике"
}
},
{
"postUID": "ab42520f-f379-11eb-8104-1831bfcfda3c",
"postName": "Экономист по материально-техническому снабжению",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Экономист по материально-техническому снабжению",
"genitive": "Экономиста по материально-техническому снабжению",
"dative": "Экономисту по материально-техническому снабжению",
"accusative": "Экономиста по материально-техническому снабжению",
"ablative": "Экономистом по материально-техническому снабжению",
"prepositional": "Экономисте по материально-техническому снабжению"
}
},
{
"postUID": "d42d8293-ebdf-11e9-8132-00155d630402",
"postName": "Специалист по охране труда",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Специалист по охране труда",
"genitive": "Специалиста по охране труда",
"dative": "Специалисту по охране труда",
"accusative": "Специалиста по охране труда",
"ablative": "Специалистом по охране труда",
"prepositional": "Специалисте по охране труда"
}
},
{
"postUID": "5b08356e-20f2-11ec-8105-1831bfcfda3c",
"postName": "Менеджер по персоналу",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Менеджер по персоналу",
"genitive": "Менеджера по персоналу",
"dative": "Менеджеру по персоналу",
"accusative": "Менеджер по персоналу",
"ablative": "Менеджером по персоналу",
"prepositional": "Менеджере по персоналу"
}
},
{
"postUID": "73a97e6c-f364-11ea-80fe-1831bfcfda3c",
"postName": "Главный инженер",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Главный инженер",
"genitive": "Главного инженера",
"dative": "Главному инженеру",
"accusative": "Главного инженера",
"ablative": "Главным инженером",
"prepositional": "Главном инженере"
}
},
{
"postUID": "89bdad49-d283-11ec-810a-1831bfcfda3c",
"postName": "Юрист",
"organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
"organizationINN": "7807115228",
"inflection": {
"nominative": "Юрист",
"genitive": "Юриста",
"dative": "Юристу",
"accusative": "Юриста",
"ablative": "Юристом",
"prepositional": "Юристе"
}
}
]
        }', false);

        foreach ($posts->data as $post) {
            if (empty($post->postUID))
            {
                continue;
            }

            $company = Company::where('company_1c_uid', $post->organizationUID)->get()->first();
            if (isset($company)) {
                $employeePost = Employees1cPost::updateOrCreate(
                    [
                        'post_1c_uid' => $post->postUID,
                    ],
                    [
                        "name" => $post->postName,
                        "company_id" => $company->id
                    ]
                );

                    Log::channel('stderr')->info('[info] ' . var_dump($post->inflection->nominative));
                    Employees1cPostInflection::updateOrCreate(
                        [
                            'post_id' => $employeePost->id
                        ],
                        [
                            'nominative' => $post->inflection->nominative,
                            'genitive' => $post->inflection->genitive,
                            'dative' => $post->inflection->dative,
                            'accusative' => $post->inflection->accusative,
                            'ablative' => $post->inflection->ablative,
                            'prepositional' => $post->inflection->prepositional
                        ]
                    );

            }
        }
    }
}
