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
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "2803b065-65a3-11e5-84a7-50465d8f7441",
                                                    "organizationINN": "7842528806",
                                                    "staffListUID": "7f8e86c3-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "cc6e434b-3541-11e7-8dc8-50465d8f7441",
                                                    "postName": "Геодезист",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "0d4f7d8c-24b0-11eb-80ff-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "2803b065-65a3-11e5-84a7-50465d8f7441",
                                                    "organizationINN": "7842528806",
                                                    "staffListUID": "7f8e86db-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "2529f0af-3a20-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Генеральный директор",
                                                    "organizationUID": "2803b065-65a3-11e5-84a7-50465d8f7441",
                                                    "organizationINN": "7842528806",
                                                    "staffListUID": "7f8e86c4-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "75910d7c-f8dd-11e2-bec1-0019d11ffeaf",
                                                    "postName": "Технический директор",
                                                    "organizationUID": "2803b065-65a3-11e5-84a7-50465d8f7441",
                                                    "organizationINN": "7842528806",
                                                    "staffListUID": "7f8e86cd-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "7ed9795e-00e5-11e4-8bb5-50465d8f7441",
                                                    "postName": "Финансовый директор",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfaffc9-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "51f32951-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Механик",
                                                    "organizationUID": "2803b065-65a3-11e5-84a7-50465d8f7441",
                                                    "organizationINN": "7842528806",
                                                    "staffListUID": "7f8e86dc-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfafff8-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "322c3e2c-09ce-11e5-a5b3-50465d8f7441",
                                                    "postName": "Руководитель проектов",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb0089-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfaffd2-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb003a-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "7f8e86e7-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfaffdc-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb001e-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "3c0af6f9-0bd9-11e8-80c5-00155d4c1e00",
                                                    "postName": "Юрист",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfafff9-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb0088-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "1bb5b483-da92-11e3-8bdf-50465d8f7441",
                                                    "postName": "Главный механик",
                                                    "organizationUID": "2803b065-65a3-11e5-84a7-50465d8f7441",
                                                    "organizationINN": "7842528806",
                                                    "staffListUID": "7f8e86d6-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "d484535d-191b-11e7-9c90-50465d8f7441",
                                                    "postName": "Секретарь руководителя",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb0028-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "1c9981b0-7631-11e9-8123-00155d630402",
                                                    "postName": "Специалист по логистике",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "8f028ec7-abdf-11ea-80fc-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "cc6e434b-3541-11e7-8dc8-50465d8f7441",
                                                    "postName": "Геодезист",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "85ec5f9b-abe1-11ea-80fc-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "51f3295a-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Инженер ПТО",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "0ba8b0a9-dadf-11ea-80fe-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "75910d7c-f8dd-11e2-bec1-0019d11ffeaf",
                                                    "postName": "Технический директор",
                                                    "organizationUID": "2eab7a61-7bfe-11e6-b771-50465d8f7441",
                                                    "organizationINN": "7810950525",
                                                    "staffListUID": "a21cfc54-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "2eab7a61-7bfe-11e6-b771-50465d8f7441",
                                                    "organizationINN": "7810950525",
                                                    "staffListUID": "a21cfc53-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "b447e17f-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "b447e180-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb0092-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb007e-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "51f3294a-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Производитель работ",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "4d2b87d2-25de-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "4d2b87de-25de-11e9-8111-00155d630402",
                                                    "postName": "Специалист по охране труда",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "4d2b87e1-25de-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "2529f0b2-3a20-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Стропальщик",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "0ba8b0aa-dadf-11ea-80fe-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "1cbbcd90-248b-11e9-8111-00155d630402",
                                                    "postName": "Агент по снабжению",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "4d2b87f0-25de-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "51f32938-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Электрогазосварщик",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "0ba8b0a8-dadf-11ea-80fe-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "51f3293b-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Главный инженер",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb0031-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "a0eefd3f-8819-11e9-8123-00155d630402",
                                                    "postName": "Инженер ПТО",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "a0eefd40-8819-11e9-8123-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "1c9981b0-7631-11e9-8123-00155d630402",
                                                    "postName": "Специалист по логистике",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "1c9981b1-7631-11e9-8123-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "0782e8b4-77ab-11e9-8123-00155d630402",
                                                    "postName": "Уборщица",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "42ee904e-dae8-11ea-80fe-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "c2d10949-79e6-11ec-8107-1831bfcfda3c",
                                                    "postName": "Бухгалтер по расчету заработной платы",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "01183831-79e7-11ec-8107-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "0782e8bd-77ab-11e9-8123-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "5889fc29-7606-11e9-8123-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "51f32938-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Электрогазосварщик",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb00bb-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "322c3e2c-09ce-11e5-a5b3-50465d8f7441",
                                                    "postName": "Руководитель проектов",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb00c4-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "51f3294a-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Производитель работ",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "5905608a-abe1-11ea-80fc-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "199cb9ef-3408-11e9-8111-00155d630402",
                                                    "postName": "Техник",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "199cb9ee-3408-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "2529f0b2-3a20-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Стропальщик",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb00d7-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "cc6e434b-3541-11e7-8dc8-50465d8f7441",
                                                    "postName": "Геодезист",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb00e9-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "cc6e434b-3541-11e7-8dc8-50465d8f7441",
                                                    "postName": "Геодезист",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "ccd7a0e2-cd48-11e9-8130-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "99884c42-66c3-11e7-a358-50465d8f7441",
                                                    "postName": "Экономист по материально-техническому снабжению",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfaffc0-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "51f3294a-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Производитель работ",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "210f4629-d388-11e9-8130-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "0782e8b4-77ab-11e9-8123-00155d630402",
                                                    "postName": "Уборщица",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "0782e8b2-77ab-11e9-8123-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "2529f0b0-3a20-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Машинист крана",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb00b0-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "1649b96d-9319-11e9-812d-00155d630402",
                                                    "postName": "Специалист по управленческому учёту",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "1649b96c-9319-11e9-812d-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "1cbbcd90-248b-11e9-8111-00155d630402",
                                                    "postName": "Агент по снабжению",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "1c9981aa-7631-11e9-8123-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "7047d402-9c32-11e7-bc88-50465d8f7441",
                                                    "postName": "Машинист копра",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb00e0-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "51f3294a-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Производитель работ",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb00cd-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "de94c1ab-3d13-11e2-afa6-0019d11ffeaf",
                                                    "postName": "Мастер строительно-монтажных работ (СМР)",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9fb66143-8037-11ec-8107-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "b06fbb5e-e6d9-11e7-80c4-00155d4c1e00",
                                                    "postName": "Специалист по договорной и претензионной работе",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9fb66142-8037-11ec-8107-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "8094d88f-717f-11e9-811d-00155d630402",
                                                    "postName": "Подсобный рабочий",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "42ee9052-dae8-11ea-80fe-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "1cbbcd90-248b-11e9-8111-00155d630402",
                                                    "postName": "Агент по снабжению",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "8f028ec8-abdf-11ea-80fc-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "322c3e2c-09ce-11e5-a5b3-50465d8f7441",
                                                    "postName": "Руководитель проектов",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "42ee905b-dae8-11ea-80fe-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "2ede92e2-6302-11e8-80fb-00155d4c1e00",
                                                    "postName": "Инженер по строительному контролю",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb0075-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "2529f0b0-3a20-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Машинист крана",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb0045-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "74ab7b14-c308-11e9-8130-00155d630402",
                                                    "postName": "Специалист по промышленной безопасности",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "42ee904d-dae8-11ea-80fe-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "cc6e434b-3541-11e7-8dc8-50465d8f7441",
                                                    "postName": "Геодезист",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "42ee9050-dae8-11ea-80fe-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "74ab7b14-c308-11e9-8130-00155d630402",
                                                    "postName": "Специалист по промышленной безопасности",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "74ab7b18-c308-11e9-8130-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "733c7934-34ce-11e9-8111-00155d630402",
                                                    "postName": "Начальник производства",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "733c7935-34ce-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "733c7934-34ce-11e9-8111-00155d630402",
                                                    "postName": "Начальник производства",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "dc052c40-abe0-11ea-80fc-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "99884c42-66c3-11e7-a358-50465d8f7441",
                                                    "postName": "Экономист по материально-техническому снабжению",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "5889fc24-7606-11e9-8123-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "322c3e2c-09ce-11e5-a5b3-50465d8f7441",
                                                    "postName": "Руководитель проектов",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "210f4633-d388-11e9-8130-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "99884c42-66c3-11e7-a358-50465d8f7441",
                                                    "postName": "Экономист по материально-техническому снабжению",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "e64e5494-abde-11ea-80fc-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb00af-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb0074-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "5783b8ab-415d-11ec-8107-1831bfcfda3c",
                                                    "postName": "Руководитель отдела персонала",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "cac9b926-415d-11ec-8107-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb000b-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "2529f0b0-3a20-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Машинист крана",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "78824f7c-0a50-11ec-8104-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "322c3e2c-09ce-11e5-a5b3-50465d8f7441",
                                                    "postName": "Руководитель проектов",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfaffb6-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "8006ff09-2ce8-11ec-8107-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "3b0223f2-d751-11e8-8110-00155d630402",
                                                    "postName": "Начальник участка",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfaffad-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "de94c1ab-3d13-11e2-afa6-0019d11ffeaf",
                                                    "postName": "Мастер строительно-монтажных работ (СМР)",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfaffa4-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "1c9981b0-7631-11e9-8123-00155d630402",
                                                    "postName": "Специалист по логистике",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "42ee9058-dae8-11ea-80fe-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "51f3295a-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Инженер ПТО",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfaffdd-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "99884c42-66c3-11e7-a358-50465d8f7441",
                                                    "postName": "Экономист по материально-техническому снабжению",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "42ee9056-dae8-11ea-80fe-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "cc6e434b-3541-11e7-8dc8-50465d8f7441",
                                                    "postName": "Геодезист",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "8f8cf5e1-f271-11ea-80fe-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "77de8b3d-d752-11ec-810a-1831bfcfda3c",
                                                    "postName": "Начальник отдела охраны труда и промышленной безопасности",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "d1569b79-d75a-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "f6f6ad65-c4c0-11ec-810a-1831bfcfda3c",
                                                    "postName": "Аналитик структуры управления и оптимизации бизнес-процессов",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "6a95047c-d296-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "bb85b835-c4b3-11ec-810a-1831bfcfda3c",
                                                    "postName": "Заместитель главного бухгалтера",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "6a95047d-d296-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "4d2b87de-25de-11e9-8111-00155d630402",
                                                    "postName": "Специалист по охране труда",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "d1569b78-d75a-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "1649b96d-9319-11e9-812d-00155d630402",
                                                    "postName": "Специалист по управленческому учёту",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "92d5e446-f26a-11ea-80fe-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "9ec59bd7-a8f3-11ec-810a-1831bfcfda3c",
                                                    "postName": "Специалист планово-экономического отдела",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "5e512ef1-a8f4-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "142e9460-c4b5-11ec-810a-1831bfcfda3c",
                                                    "postName": "Водитель-экспедитор",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "6a95047b-d296-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "b1b1b6ae-ed08-11e6-907e-50465d8f7441",
                                                    "postName": "Электросварщик",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfaff7d-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "6d645f40-ee0b-11ec-810a-1831bfcfda3c",
                                                    "postName": "Ведущий специалист по кадровому делопроизводству",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "5b091b8c-ee0e-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "b06fbb5e-e6d9-11e7-80c4-00155d4c1e00",
                                                    "postName": "Специалист по договорной и претензионной работе",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "8006ff0b-2ce8-11ec-8107-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "2529f0b2-3a20-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Стропальщик",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfaff74-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "2529f0b0-3a20-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Машинист крана",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfaff87-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "51f3294a-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Производитель работ",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfaff6a-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "51f32938-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Электрогазосварщик",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "7f8e86e8-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "85181e65-fde8-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "199cb9ef-3408-11e9-8111-00155d630402",
                                                    "postName": "Техник",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "42ee9051-dae8-11ea-80fe-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "bb85b835-c4b3-11ec-810a-1831bfcfda3c",
                                                    "postName": "Заместитель главного бухгалтера",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "e52cc107-c4c5-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "f6f6ad65-c4c0-11ec-810a-1831bfcfda3c",
                                                    "postName": "Аналитик структуры управления и оптимизации бизнес-процессов",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "e52cc109-c4c5-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "142e9460-c4b5-11ec-810a-1831bfcfda3c",
                                                    "postName": "Водитель-экспедитор",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "e52cc108-c4c5-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "899624c3-8a67-11ec-8107-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "9237d26d-79e2-11ec-8107-1831bfcfda3c",
                                                    "postName": "Экономист по материально-техническому учёту",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "edc24bb8-79e2-11ec-8107-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "5e512eef-a8f4-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfaffbf-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "b06fbb5e-e6d9-11e7-80c4-00155d4c1e00",
                                                    "postName": "Специалист по договорной и претензионной работе",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfaffe6-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "1bb5b483-da92-11e3-8bdf-50465d8f7441",
                                                    "postName": "Главный механик",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb00a5-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "51f32951-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Механик",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb0093-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "98f03d0c-79e6-11ec-8107-1831bfcfda3c",
                                                    "postName": "Ведущий бухгалтер",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "01183830-79e7-11ec-8107-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "75ef8094-8a67-11ec-8107-1831bfcfda3c",
                                                    "postName": "Программист",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "899624c4-8a67-11ec-8107-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "4d2b87e0-25de-11e9-8111-00155d630402",
                                                    "postName": "Специалист по охране труда",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "42ee904c-dae8-11ea-80fe-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "b6661b8d-d74c-11ec-810a-1831bfcfda3c",
                                                    "postName": "Специалист по охране труда и промышленной безопасности",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "d1569b77-d75a-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "2529f0ae-3a20-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Копровщик",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "d1569b7a-d75a-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "e98cfae4-79e6-11ec-8107-1831bfcfda3c",
                                                    "postName": "Бухгалтер по учету материально-производственных запасов",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "01183832-79e7-11ec-8107-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "cc5a890d-b7e4-11e7-80bf-00155d4c1e00",
                                                    "postName": "Менеджер по персоналу",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "85181e64-fde8-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "dba907d4-80db-11e8-80fb-00155d4c1e00",
                                                    "postName": "Инженер-проектировщик",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb007f-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "3c0af6f9-0bd9-11e8-80c5-00155d4c1e00",
                                                    "postName": "Юрист",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "def6e7bc-e33a-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "2529f0ae-3a20-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Копровщик",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfaff91-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "a78efbde-fdd0-11ec-810a-1831bfcfda3c",
                                                    "postName": "Ведущий инженер ПТО",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "85181e69-fde8-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "cc6e434b-3541-11e7-8dc8-50465d8f7441",
                                                    "postName": "Геодезист",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfaff9a-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "289ab6dd-fdc4-11ec-810a-1831bfcfda3c",
                                                    "postName": "Начальник тендерного отдела",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "85181e66-fde8-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "b2e33c9b-fdc4-11ec-810a-1831bfcfda3c",
                                                    "postName": "Специалист тендерного отдела",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "85181e67-fde8-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "08e235ab-a8f0-11ec-810a-1831bfcfda3c",
                                                    "postName": "Начальник планово-экономического отдела",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "5e512ef0-a8f4-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "64230fa8-fde2-11ec-810a-1831bfcfda3c",
                                                    "postName": "Ведущий экономист планово-экономического отдела",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "85181e68-fde8-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "bf0dc297-7106-11e7-a850-50465d8f7441",
                                                    "postName": "Начальник ПТО",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfaffef-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "1f8c1118-2ce4-11ec-8107-1831bfcfda3c",
                                                    "postName": "Начальник отдела",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "8006ff0a-2ce8-11ec-8107-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "2529f0af-3a20-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Генеральный директор",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb001f-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "48b87ee7-3666-11e9-8111-00155d630402",
                                                    "postName": "Архивариус",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "42ee904b-dae8-11ea-80fe-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "3c0af6f9-0bd9-11e8-80c5-00155d4c1e00",
                                                    "postName": "Юрист",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "85181e62-fde8-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "cc5a890d-b7e4-11e7-80bf-00155d4c1e00",
                                                    "postName": "Менеджер по персоналу",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfaffd3-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "74ab7b14-c308-11e9-8130-00155d630402",
                                                    "postName": "Специалист по промышленной безопасности",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "b4656084-abe0-11ea-80fc-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "51f3294a-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Производитель работ",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "42ee9059-dae8-11ea-80fe-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "",
                                                    "postName": "",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "d1569b76-d75a-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "75ef8094-8a67-11ec-8107-1831bfcfda3c",
                                                    "postName": "Программист",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "85181e63-fde8-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "7047d402-9c32-11e7-bc88-50465d8f7441",
                                                    "postName": "Машинист копра",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "0cb2e23f-9cfe-11eb-8103-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "18f4e141-8d2e-11e6-b1e8-50465d8f7441",
                                                    "postName": "Заведующий складом",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb0058-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "733c7934-34ce-11e9-8111-00155d630402",
                                                    "postName": "Начальник производства",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "42ee9054-dae8-11ea-80fe-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "1cbbcd90-248b-11e9-8111-00155d630402",
                                                    "postName": "Агент по снабжению",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "42ee9057-dae8-11ea-80fe-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "b5eb9d97-eed7-11eb-8104-1831bfcfda3c",
                                                    "postName": "Мастер погрузо-разгрузочных работ",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "6b4f78fc-eed8-11eb-8104-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "51f3294e-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Бухгалтер",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb000c-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "51f3294c-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Главный бухгалтер",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb0015-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "190c115a-fdd2-11ec-810a-1831bfcfda3c",
                                                    "postName": "Начальник проектного отдела",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "85181e6a-fde8-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "1cb37285-ae72-11ec-810a-1831bfcfda3c",
                                                    "postName": "Начальник отдела материально-технического снабжения и логистики (ОМТС и логистики)",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9c4988e7-ae75-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "2529f0b0-3a20-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Машинист крана",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "0cb2e23e-9cfe-11eb-8103-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "84f77cee-bc68-11e2-a2ac-0019d11ffeaf",
                                                    "postName": "Электрослесарь  по ремонту электрооборудования",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb009c-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "8094d88f-717f-11e9-811d-00155d630402",
                                                    "postName": "Подсобный рабочий",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "69c5f441-7198-11e9-811d-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "de94c1a7-3d13-11e2-afa6-0019d11ffeaf",
                                                    "postName": "Директор по развитию",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb0002-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "cb21e6d3-93cf-11ec-810a-1831bfcfda3c",
                                                    "postName": "Специалист",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "dade0036-93cf-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "0782e8b4-77ab-11e9-8123-00155d630402",
                                                    "postName": "Уборщица",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "8f028ec9-abdf-11ea-80fc-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "812f772c-83f9-11ec-8107-1831bfcfda3c",
                                                    "postName": "Курьер",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "a88c49e1-83f9-11ec-8107-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "e186cdb2-b70a-11ec-810a-1831bfcfda3c",
                                                    "postName": "Руководитель проектного отдела",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "bc411fe8-b70c-11ec-810a-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "5c4306bd-2f45-11ed-810b-1831bfcfda3c",
                                                    "postName": "Инженер по безопасности движения",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "528a24e1-2f4a-11ed-810b-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "285ed685-2f46-11ed-810b-1831bfcfda3c",
                                                    "postName": "Помощник руководителя",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "528a24e2-2f4a-11ed-810b-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "dba907d2-80db-11e8-80fb-00155d4c1e00",
                                                    "postName": "Кладовщик",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb006b-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "99884c42-66c3-11e7-a358-50465d8f7441",
                                                    "postName": "Экономист по материально-техническому снабжению",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "19c72c90-2f4d-11ed-810b-1831bfcfda3c"
                                                    },
                                                    {
                                                    "postUID": "51f32938-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Электрогазосварщик",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb0062-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "2529f0b2-3a20-11e2-a4d2-0019d11ffeaf",
                                                    "postName": "Стропальщик",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb004f-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "b1b1b6ae-ed08-11e6-907e-50465d8f7441",
                                                    "postName": "Электросварщик",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "9bfb003b-2338-11e9-8111-00155d630402"
                                                    },
                                                    {
                                                    "postUID": "8094d88f-717f-11e9-811d-00155d630402",
                                                    "postName": "Подсобный рабочий",
                                                    "organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
                                                    "organizationINN": "7807348494",
                                                    "staffListUID": "42ee9055-dae8-11ea-80fe-1831bfcfda3c"
                                                    }
                                                    ]}', false);

        foreach ($posts->data as $post) {
            if (empty($post->postUID))
            {
                continue;
            }

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
