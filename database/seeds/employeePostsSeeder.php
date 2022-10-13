<?php

use App\Models\Company\Company;
use App\Models\OneC\Employees1cpost;
use App\Models\OneC\Employees1cSubdivision;
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
        $posts = json_decode('{"data": [
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "e0b05280-9d8e-11e9-812f-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "d3dc5e53-9e35-11e9-812f-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "d3dc5e52-9e35-11e9-812f-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "73a97e6c-f364-11ea-80fe-1831bfcfda3c",
                                                    "postName": "Главный инженер",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "600682a9-f364-11ea-80fe-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "e0b0526e-9d8e-11e9-812f-00155d630402",
                                                    "postName": "Руководитель проектов",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "38c5089f-b8e9-11e9-8130-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "d3dc5e62-9e35-11e9-812f-00155d630402",
                                                    "postName": "Стропальщик",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "9d4f0eab-7118-11eb-8100-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "e0b0526e-9d8e-11e9-812f-00155d630402",
                                                    "postName": "Руководитель проектов",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "38c508af-b8e9-11e9-8130-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "d3dc5e61-9e35-11e9-812f-00155d630402",
                                                    "postName": "Геодезист",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "05367189-e0ec-11e9-8132-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "d3dc5e62-9e35-11e9-812f-00155d630402",
                                                    "postName": "Стропальщик",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "d42d826a-ebdf-11e9-8132-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "e0b0526d-9d8e-11e9-812f-00155d630402",
                                                    "postName": "Производитель работ",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "78d8cfb1-9fd7-11e9-812f-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "d42d8293-ebdf-11e9-8132-00155d630402",
                                                    "postName": "Специалист по охране труда",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "d42d8292-ebdf-11e9-8132-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "32a3e7b1-e427-11e9-8132-00155d630402",
                                                    "postName": "Инженер ПТО",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "83850fae-7692-11eb-8100-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "d678693f-8ca0-11eb-8100-1831bfcfda3c",
                                                    "postName": "Машинист буровой установки",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "a51a1396-8ca1-11eb-8100-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "77b66264-4c4f-11ec-8107-1831bfcfda3c",
                                                    "postName": "Менеджер проектов",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "09b83f8d-4c51-11ec-8107-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "ad831a46-fd05-11ec-810a-1831bfcfda3c",
                                                    "postName": "Начальник тендерного отдела",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "e078b486-010b-11ed-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "e078b485-010b-11ed-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "3a70184d-5f3b-11ec-8107-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "85db1cf5-9d6d-11e9-812f-00155d630402",
                                                    "postName": "Генеральный директор",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "e0b05276-9d8e-11e9-812f-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "c68964c9-5f34-11ec-8107-1831bfcfda3c",
                                                    "postName": "Начальник сметно-экономического отдела",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "3a70184e-5f3b-11ec-8107-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "77b66264-4c4f-11ec-8107-1831bfcfda3c",
                                                    "postName": "Менеджер проектов",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "569db87d-619d-11ec-8107-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "32a3e7b1-e427-11e9-8132-00155d630402",
                                                    "postName": "Инженер ПТО",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "569db87e-619d-11ec-8107-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "32a3e7b1-e427-11e9-8132-00155d630402",
                                                    "postName": "Инженер ПТО",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "8cea71fe-856c-11eb-8100-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "ab42520f-f379-11eb-8104-1831bfcfda3c",
                                                    "postName": "Экономист по материально-техническому снабжению",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "2cddd614-f37a-11eb-8104-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "5b08356e-20f2-11ec-8105-1831bfcfda3c",
                                                    "postName": "Менеджер по персоналу",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "c8f9a9f3-20f2-11ec-8105-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "7e44eae7-2ff1-11ec-8107-1831bfcfda3c",
                                                    "postName": "Директор по строительству",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "51a7b61f-2ff2-11ec-8107-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "d3dc5e5e-9e35-11e9-812f-00155d630402",
                                                    "postName": "Главный бухгалтер",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "a6244a2b-a2e6-11e9-812f-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "73a97e6c-f364-11ea-80fe-1831bfcfda3c",
                                                    "postName": "Главный инженер",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "079a9bb7-8621-11eb-8100-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "89bdad49-d283-11ec-810a-1831bfcfda3c",
                                                    "postName": "Юрист",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "33b29b75-d28d-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "e0b0526a-9d8e-11e9-812f-00155d630402",
                                                    "postName": "Машинист крана (крановщик)",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "33b29b76-d28d-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "e0b0526e-9d8e-11e9-812f-00155d630402",
                                                    "postName": "Руководитель проектов",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "a04de03f-7689-11eb-8100-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "d3dc5e61-9e35-11e9-812f-00155d630402",
                                                    "postName": "Геодезист",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "83850fb0-7692-11eb-8100-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "e0b0526b-9d8e-11e9-812f-00155d630402",
                                                    "postName": "Электрогазосварщик",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "d3dc5e49-9e35-11e9-812f-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "e0b0526d-9d8e-11e9-812f-00155d630402",
                                                    "postName": "Производитель работ",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "d16767a7-a151-11e9-812f-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "d3dc5e62-9e35-11e9-812f-00155d630402",
                                                    "postName": "Стропальщик",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "83850faf-7692-11eb-8100-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "32a3e7b1-e427-11e9-8132-00155d630402",
                                                    "postName": "Инженер ПТО",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "170e68c4-d12b-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "ffb35dd2-d288-11ec-810a-1831bfcfda3c",
                                                    "postName": "Машинист копра",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "33b29b77-d28d-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "77b66264-4c4f-11ec-8107-1831bfcfda3c",
                                                    "postName": "Менеджер проектов",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "170e68c3-d12b-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "c68964c9-5f34-11ec-8107-1831bfcfda3c",
                                                    "postName": "Начальник сметно-экономического отдела",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "170e68c2-d12b-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "d42d8293-ebdf-11e9-8132-00155d630402",
                                                    "postName": "Специалист по охране труда",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "2e9a2a99-d763-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "a092b52a-8c9f-11eb-8100-1831bfcfda3c",
                                                    "postName": "Помощник машиниста буровой установки",
                                                    "organizationUID": "a5f0bc19-9bf1-11e9-812f-00155d630402",
                                                    "organizationINN": "7807115228",
                                                    "staffListUID": "a51a1395-8ca1-11eb-8100-1831bfcfda3c"
                                                    }
                                                    ]}', false);

        foreach ($posts->data as $post) {
            $company = Company::where('company_1c_uid', $post->organizationUID)->get()->first();
            if (isset($company)) {
                Employees1cpost::updateOrCreate(
                    [
                        'post_1c_uid' => $post->postUID,
                    ],
                    [
                        "name" => $post->postName,
                        "company_id" => $company->id
                    ]
                );
            }
        }
    }
}
