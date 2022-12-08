<?php

use App\Models\Company\Company;
use App\Models\Employees\Employees1cpost;
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
        $posts = json_decode('{"data": [
{
"postUID": "2529f0b0-3a20-11e2-a4d2-0019d11ffeaf",
"postName": "Машинист крана",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfaff87-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Машинист крана",
"genitive": "Машиниста крана",
"dative": "Машинисту крана",
"accusative": "Машиниста крана",
"ablative": "Машинистом крана",
"prepositional": "Машинисте крана"
}
},
{
"postUID": "8094d88f-717f-11e9-811d-00155d630402",
"postName": "Подсобный рабочий",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "69c5f441-7198-11e9-811d-00155d630402",
"inflection": {
"nominative": "Подсобный рабочий",
"genitive": "Подсобного рабочего",
"dative": "Подсобному рабочему",
"accusative": "Подсобного рабочего",
"ablative": "Подсобным рабочим",
"prepositional": "Подсобном рабочем"
}
},
{
"postUID": "51f32938-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Электрогазосварщик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "0ba8b0a8-dadf-11ea-80fe-1831bfcfda3c",
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
"postUID": "bb85b835-c4b3-11ec-810a-1831bfcfda3c",
"postName": "Заместитель главного бухгалтера",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "e52cc107-c4c5-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Заместитель главного бухгалтера",
"genitive": "Заместителя главного бухгалтера",
"dative": "Заместителю главного бухгалтера",
"accusative": "Заместителя главного бухгалтера",
"ablative": "Заместителем главного бухгалтера",
"prepositional": "Заместителе главного бухгалтера"
}
},
{
"postUID": "99884c42-66c3-11e7-a358-50465d8f7441",
"postName": "Экономист по материально-техническому снабжению",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "19c72c90-2f4d-11ed-810b-1831bfcfda3c",
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
"postUID": "f6f6ad65-c4c0-11ec-810a-1831bfcfda3c",
"postName": "Аналитик структуры управления и оптимизации бизнес-процессов",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "6a95047c-d296-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Аналитик структуры управления и оптимизации бизнес-процессов",
"genitive": "Аналитика структуры управления и оптимизаций бизнес-процессов",
"dative": "Аналитику структуры управления и оптимизациям бизнес-процессов",
"accusative": "Аналитика структуры управления и оптимизации бизнес-процессов",
"ablative": "Аналитиком структуры управления и оптимизациями бизнес-процессов",
"prepositional": "Аналитике структуры управления и оптимизациях бизнес-процессов"
}
},
{
"postUID": "1cbbcd90-248b-11e9-8111-00155d630402",
"postName": "Агент по снабжению",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "4d2b87f0-25de-11e9-8111-00155d630402",
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
"postUID": "0782e8b4-77ab-11e9-8123-00155d630402",
"postName": "Уборщица",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "0782e8b2-77ab-11e9-8123-00155d630402",
"inflection": {
"nominative": "Уборщица",
"genitive": "Уборщицы",
"dative": "Уборщице",
"accusative": "Уборщицу",
"ablative": "Уборщицей",
"prepositional": "Уборщице"
}
},
{
"postUID": "cc6e434b-3541-11e7-8dc8-50465d8f7441",
"postName": "Геодезист",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "8f8cf5e1-f271-11ea-80fe-1831bfcfda3c",
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
"postUID": "733c7934-34ce-11e9-8111-00155d630402",
"postName": "Начальник производства",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "733c7935-34ce-11e9-8111-00155d630402",
"inflection": {
"nominative": "Начальник производства",
"genitive": "Начальник производства",
"dative": "Начальник производства",
"accusative": "Начальник производства",
"ablative": "Начальник производства",
"prepositional": "Начальник производства"
}
},
{
"postUID": "5783b8ab-415d-11ec-8107-1831bfcfda3c",
"postName": "Руководитель отдела персонала",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "cac9b926-415d-11ec-8107-1831bfcfda3c",
"inflection": {
"nominative": "Руководитель отдела персонала",
"genitive": "Руководителя отдела персонала",
"dative": "Руководителю отдела персонала",
"accusative": "Руководителя отдела персонала",
"ablative": "Руководителем отдела персонала",
"prepositional": "Руководителе отдела персонала"
}
},
{
"postUID": "1cbbcd90-248b-11e9-8111-00155d630402",
"postName": "Агент по снабжению",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "1c9981aa-7631-11e9-8123-00155d630402",
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
"postUID": "5c4306bd-2f45-11ed-810b-1831bfcfda3c",
"postName": "Инженер по безопасности движения",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "528a24e1-2f4a-11ed-810b-1831bfcfda3c",
"inflection": {
"nominative": "Инженер по безопасности движения",
"genitive": "Инженера по безопасности движения",
"dative": "Инженеру по безопасности движения",
"accusative": "Инженера по безопасности движения",
"ablative": "Инженером по безопасности движения",
"prepositional": "Инженере по безопасности движения"
}
},
{
"postUID": "2529f0b0-3a20-11e2-a4d2-0019d11ffeaf",
"postName": "Машинист крана",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb00b0-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Машинист крана",
"genitive": "Машиниста крана",
"dative": "Машинисту крана",
"accusative": "Машиниста крана",
"ablative": "Машинистом крана",
"prepositional": "Машинисте крана"
}
},
{
"postUID": "cc6e434b-3541-11e7-8dc8-50465d8f7441",
"postName": "Геодезист",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "85ec5f9b-abe1-11ea-80fc-1831bfcfda3c",
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
"postUID": "e98cfae4-79e6-11ec-8107-1831bfcfda3c",
"postName": "Бухгалтер по учету материально-производственных запасов",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "01183832-79e7-11ec-8107-1831bfcfda3c",
"inflection": {
"nominative": "Бухгалтер по учету материально-производственных запасов",
"genitive": "Бухгалтера по учету материально-производственных запасов",
"dative": "Бухгалтеру по учету материально-производственных запасов",
"accusative": "Бухгалтера по учету материально-производственных запасов",
"ablative": "Бухгалтером по учету материально-производственных запасов",
"prepositional": "Бухгалтере по учету материально-производственных запасов"
}
},
{
"postUID": "cc6e434b-3541-11e7-8dc8-50465d8f7441",
"postName": "Геодезист",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfaff9a-2338-11e9-8111-00155d630402",
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
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb003a-2338-11e9-8111-00155d630402",
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
"postUID": "b06fbb5e-e6d9-11e7-80c4-00155d4c1e00",
"postName": "Специалист по договорной и претензионной работе",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfaffe6-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Специалист по договорной и претензионной работе",
"genitive": "Специалиста по договорной и претензионной работе",
"dative": "Специалисту по договорной и претензионной работе",
"accusative": "Специалиста по договорной и претензионной работе",
"ablative": "Специалистом по договорной и претензионной работе",
"prepositional": "Специалисте по договорной и претензионной работе"
}
},
{
"postUID": "a0eefd3f-8819-11e9-8123-00155d630402",
"postName": "Инженер ПТО",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "a0eefd40-8819-11e9-8123-00155d630402",
"inflection": {
"nominative": "Инженер ПТО",
"genitive": "Инженера ПТО",
"dative": "Инженеру ПТО",
"accusative": "Инженера ПТО",
"ablative": "Инженером ПТО",
"prepositional": "Инженере ПТО"
}
},
{
"postUID": "51f32938-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Электрогазосварщик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "7f8e86e8-2338-11e9-8111-00155d630402",
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
"postUID": "cc5a890d-b7e4-11e7-80bf-00155d4c1e00",
"postName": "Менеджер по персоналу",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "85181e64-fde8-11ec-810a-1831bfcfda3c",
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
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "d1569b76-d75a-11ec-810a-1831bfcfda3c",
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
"postUID": "",
"postName": "",
"organizationUID": "2803b065-65a3-11e5-84a7-50465d8f7441",
"organizationINN": "7842528806",
"staffListUID": "7f8e86c3-2338-11e9-8111-00155d630402",
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
"postUID": "8094d88f-717f-11e9-811d-00155d630402",
"postName": "Подсобный рабочий",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "42ee9052-dae8-11ea-80fe-1831bfcfda3c",
"inflection": {
"nominative": "Подсобный рабочий",
"genitive": "Подсобного рабочего",
"dative": "Подсобному рабочему",
"accusative": "Подсобного рабочего",
"ablative": "Подсобным рабочим",
"prepositional": "Подсобном рабочем"
}
},
{
"postUID": "48b87ee7-3666-11e9-8111-00155d630402",
"postName": "Архивариус",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "42ee904b-dae8-11ea-80fe-1831bfcfda3c",
"inflection": {
"nominative": "Архивариус",
"genitive": "Архивариуса",
"dative": "Архивариусу",
"accusative": "Архивариуса",
"ablative": "Архивариусом",
"prepositional": "Архивариусе"
}
},
{
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb0074-2338-11e9-8111-00155d630402",
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
"postUID": "c2d10949-79e6-11ec-8107-1831bfcfda3c",
"postName": "Бухгалтер по расчету заработной платы",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "01183831-79e7-11ec-8107-1831bfcfda3c",
"inflection": {
"nominative": "Бухгалтер по расчету заработной платы",
"genitive": "Бухгалтера по расчету заработной платы",
"dative": "Бухгалтеру по расчету заработной платы",
"accusative": "Бухгалтера по расчету заработной платы",
"ablative": "Бухгалтером по расчету заработной платы",
"prepositional": "Бухгалтере по расчету заработной платы"
}
},
{
"postUID": "4d2b87de-25de-11e9-8111-00155d630402",
"postName": "Специалист по охране труда",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "d1569b78-d75a-11ec-810a-1831bfcfda3c",
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
"postUID": "190c115a-fdd2-11ec-810a-1831bfcfda3c",
"postName": "Начальник проектного отдела",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "85181e6a-fde8-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Начальник проектного отдела",
"genitive": "Начальника проектного отдела",
"dative": "Начальнику проектного отдела",
"accusative": "Начальника проектного отдела",
"ablative": "Начальником проектного отдела",
"prepositional": "Начальнике проектного отдела"
}
},
{
"postUID": "51f3294a-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Производитель работ",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "5905608a-abe1-11ea-80fc-1831bfcfda3c",
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
"postUID": "733c7934-34ce-11e9-8111-00155d630402",
"postName": "Начальник производства",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "42ee9054-dae8-11ea-80fe-1831bfcfda3c",
"inflection": {
"nominative": "Начальник производства",
"genitive": "Начальник производства",
"dative": "Начальник производства",
"accusative": "Начальник производства",
"ablative": "Начальник производства",
"prepositional": "Начальник производства"
}
},
{
"postUID": "4d2b87de-25de-11e9-8111-00155d630402",
"postName": "Специалист по охране труда",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "4d2b87e1-25de-11e9-8111-00155d630402",
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
"postUID": "2529f0af-3a20-11e2-a4d2-0019d11ffeaf",
"postName": "Генеральный директор",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb001f-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Генеральный директор",
"genitive": "Генерального директора",
"dative": "Генеральному директору",
"accusative": "Генерального директора",
"ablative": "Генеральным директором",
"prepositional": "Генеральном директоре"
}
},
{
"postUID": "199cb9ef-3408-11e9-8111-00155d630402",
"postName": "Техник",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "42ee9051-dae8-11ea-80fe-1831bfcfda3c",
"inflection": {
"nominative": "Техник",
"genitive": "Техника",
"dative": "Технику",
"accusative": "Техника",
"ablative": "Техником",
"prepositional": "Технике"
}
},
{
"postUID": "99884c42-66c3-11e7-a358-50465d8f7441",
"postName": "Экономист по материально-техническому снабжению",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "42ee9056-dae8-11ea-80fe-1831bfcfda3c",
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
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "b447e180-2338-11e9-8111-00155d630402",
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
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "85181e65-fde8-11ec-810a-1831bfcfda3c",
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
"postUID": "b1b1b6ae-ed08-11e6-907e-50465d8f7441",
"postName": "Электросварщик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfaff7d-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Электросварщик",
"genitive": "Электросварщика",
"dative": "Электросварщику",
"accusative": "Электросварщика",
"ablative": "Электросварщиком",
"prepositional": "Электросварщике"
}
},
{
"postUID": "1649b96d-9319-11e9-812d-00155d630402",
"postName": "Специалист по управленческому учёту",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "92d5e446-f26a-11ea-80fe-1831bfcfda3c",
"inflection": {
"nominative": "Специалист по управленческому учёту",
"genitive": "Специалист по управленческому учёту",
"dative": "Специалист по управленческому учёту",
"accusative": "Специалист по управленческому учёту",
"ablative": "Специалист по управленческому учёту",
"prepositional": "Специалист по управленческому учёту"
}
},
{
"postUID": "de94c1ab-3d13-11e2-afa6-0019d11ffeaf",
"postName": "Мастер строительно-монтажных работ (СМР)",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9fb66143-8037-11ec-8107-1831bfcfda3c",
"inflection": {
"nominative": "Мастер строительно-монтажных работ (СМР)",
"genitive": "Мастера строительно-монтажных работ (СМР)",
"dative": "Мастеру строительно-монтажных работ (СМР)",
"accusative": "Мастера строительно-монтажных работ (СМР)",
"ablative": "Мастером строительно-монтажных работ (СМР)",
"prepositional": "Мастере строительно-монтажных работ (СМР)"
}
},
{
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfaffd2-2338-11e9-8111-00155d630402",
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
"postUID": "0782e8b4-77ab-11e9-8123-00155d630402",
"postName": "Уборщица",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "42ee904e-dae8-11ea-80fe-1831bfcfda3c",
"inflection": {
"nominative": "Уборщица",
"genitive": "Уборщицы",
"dative": "Уборщице",
"accusative": "Уборщицу",
"ablative": "Уборщицей",
"prepositional": "Уборщице"
}
},
{
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb007e-2338-11e9-8111-00155d630402",
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
"postUID": "322c3e2c-09ce-11e5-a5b3-50465d8f7441",
"postName": "Руководитель проектов",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "42ee905b-dae8-11ea-80fe-1831bfcfda3c",
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
"postUID": "7047d402-9c32-11e7-bc88-50465d8f7441",
"postName": "Машинист копра",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb00e0-2338-11e9-8111-00155d630402",
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
"postUID": "b06fbb5e-e6d9-11e7-80c4-00155d4c1e00",
"postName": "Специалист по договорной и претензионной работе",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9fb66142-8037-11ec-8107-1831bfcfda3c",
"inflection": {
"nominative": "Специалист по договорной и претензионной работе",
"genitive": "Специалиста по договорной и претензионной работе",
"dative": "Специалисту по договорной и претензионной работе",
"accusative": "Специалиста по договорной и претензионной работе",
"ablative": "Специалистом по договорной и претензионной работе",
"prepositional": "Специалисте по договорной и претензионной работе"
}
},
{
"postUID": "64230fa8-fde2-11ec-810a-1831bfcfda3c",
"postName": "Ведущий экономист планово-экономического отдела",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "85181e68-fde8-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Ведущий экономист планово-экономического отдела",
"genitive": "Ведущего экономиста планово-экономического отдела",
"dative": "Ведущему экономисту планово-экономического отдела",
"accusative": "Ведущего экономиста планово-экономического отдела",
"ablative": "Ведущим экономистом планово-экономического отдела",
"prepositional": "Ведущем экономисте планово-экономического отдела"
}
},
{
"postUID": "b06fbb5e-e6d9-11e7-80c4-00155d4c1e00",
"postName": "Специалист по договорной и претензионной работе",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "8006ff0b-2ce8-11ec-8107-1831bfcfda3c",
"inflection": {
"nominative": "Специалист по договорной и претензионной работе",
"genitive": "Специалиста по договорной и претензионной работе",
"dative": "Специалисту по договорной и претензионной работе",
"accusative": "Специалиста по договорной и претензионной работе",
"ablative": "Специалистом по договорной и претензионной работе",
"prepositional": "Специалисте по договорной и претензионной работе"
}
},
{
"postUID": "322c3e2c-09ce-11e5-a5b3-50465d8f7441",
"postName": "Руководитель проектов",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb00c4-2338-11e9-8111-00155d630402",
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
"postUID": "cc6e434b-3541-11e7-8dc8-50465d8f7441",
"postName": "Геодезист",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "0d4f7d8c-24b0-11eb-80ff-1831bfcfda3c",
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
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfaffbf-2338-11e9-8111-00155d630402",
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
"postUID": "cc6e434b-3541-11e7-8dc8-50465d8f7441",
"postName": "Геодезист",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb00e9-2338-11e9-8111-00155d630402",
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
"postUID": "285ed685-2f46-11ed-810b-1831bfcfda3c",
"postName": "Помощник руководителя",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "528a24e2-2f4a-11ed-810b-1831bfcfda3c",
"inflection": {
"nominative": "Помощник руководителя",
"genitive": "Помощника руководителя",
"dative": "Помощнику руководителя",
"accusative": "Помощника руководителя",
"ablative": "Помощником руководителя",
"prepositional": "Помощнике руководителя"
}
},
{
"postUID": "18f4e141-8d2e-11e6-b1e8-50465d8f7441",
"postName": "Заведующий складом",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb0058-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Заведующий складом",
"genitive": "Заведующего складом",
"dative": "Заведующему складом",
"accusative": "Заведующего складом",
"ablative": "Заведующим складом",
"prepositional": "Заведующем складом"
}
},
{
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb0092-2338-11e9-8111-00155d630402",
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
"postUID": "2ede92e2-6302-11e8-80fb-00155d4c1e00",
"postName": "Инженер по строительному контролю",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb0075-2338-11e9-8111-00155d630402",
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
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb00af-2338-11e9-8111-00155d630402",
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
"postUID": "84f77cee-bc68-11e2-a2ac-0019d11ffeaf",
"postName": "Электрослесарь  по ремонту электрооборудования",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb009c-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Электрослесарь по ремонту электрооборудования",
"genitive": "Электрослесаря по ремонту электрооборудования",
"dative": "Электрослесарю по ремонту электрооборудования",
"accusative": "Электрослесаря по ремонту электрооборудования",
"ablative": "Электрослесарем по ремонту электрооборудования",
"prepositional": "Электрослесаре по ремонту электрооборудования"
}
},
{
"postUID": "1bb5b483-da92-11e3-8bdf-50465d8f7441",
"postName": "Главный механик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb00a5-2338-11e9-8111-00155d630402",
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
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "5e512eef-a8f4-11ec-810a-1831bfcfda3c",
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
"postUID": "51f32938-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Электрогазосварщик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb00bb-2338-11e9-8111-00155d630402",
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
"postUID": "51f3294a-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Производитель работ",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "42ee9059-dae8-11ea-80fe-1831bfcfda3c",
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
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "b447e17f-2338-11e9-8111-00155d630402",
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
"postUID": "51f32938-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Электрогазосварщик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb0062-2338-11e9-8111-00155d630402",
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
"postUID": "cc6e434b-3541-11e7-8dc8-50465d8f7441",
"postName": "Геодезист",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "ccd7a0e2-cd48-11e9-8130-00155d630402",
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
"postUID": "7ed9795e-00e5-11e4-8bb5-50465d8f7441",
"postName": "Финансовый директор",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfaffc9-2338-11e9-8111-00155d630402",
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
"postUID": "322c3e2c-09ce-11e5-a5b3-50465d8f7441",
"postName": "Руководитель проектов",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfaffb6-2338-11e9-8111-00155d630402",
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
"postUID": "74ab7b14-c308-11e9-8130-00155d630402",
"postName": "Специалист по промышленной безопасности",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "42ee904d-dae8-11ea-80fe-1831bfcfda3c",
"inflection": {
"nominative": "Специалист по промышленной безопасности",
"genitive": "Специалист по промышленной безопасности",
"dative": "Специалист по промышленной безопасности",
"accusative": "Специалист по промышленной безопасности",
"ablative": "Специалист по промышленной безопасности",
"prepositional": "Специалист по промышленной безопасности"
}
},
{
"postUID": "2529f0b0-3a20-11e2-a4d2-0019d11ffeaf",
"postName": "Машинист крана",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "0cb2e23e-9cfe-11eb-8103-1831bfcfda3c",
"inflection": {
"nominative": "Машинист крана",
"genitive": "Машиниста крана",
"dative": "Машинисту крана",
"accusative": "Машиниста крана",
"ablative": "Машинистом крана",
"prepositional": "Машинисте крана"
}
},
{
"postUID": "289ab6dd-fdc4-11ec-810a-1831bfcfda3c",
"postName": "Начальник тендерного отдела",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "85181e66-fde8-11ec-810a-1831bfcfda3c",
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
"postUID": "2529f0b2-3a20-11e2-a4d2-0019d11ffeaf",
"postName": "Стропальщик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "0ba8b0aa-dadf-11ea-80fe-1831bfcfda3c",
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
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "8006ff09-2ce8-11ec-8107-1831bfcfda3c",
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
"postUID": "2529f0b0-3a20-11e2-a4d2-0019d11ffeaf",
"postName": "Машинист крана",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "78824f7c-0a50-11ec-8104-1831bfcfda3c",
"inflection": {
"nominative": "Машинист крана",
"genitive": "Машиниста крана",
"dative": "Машинисту крана",
"accusative": "Машиниста крана",
"ablative": "Машинистом крана",
"prepositional": "Машинисте крана"
}
},
{
"postUID": "1c9981b0-7631-11e9-8123-00155d630402",
"postName": "Специалист по логистике",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "42ee9058-dae8-11ea-80fe-1831bfcfda3c",
"inflection": {
"nominative": "Специалист по логистике",
"genitive": "Специалиста по логистике",
"dative": "Специалисту по логистике",
"accusative": "Специалиста по логистике",
"ablative": "Специалистом по логистике",
"prepositional": "Специалисте по логистике"
}
},
{
"postUID": "75910d7c-f8dd-11e2-bec1-0019d11ffeaf",
"postName": "Технический директор",
"organizationUID": "2eab7a61-7bfe-11e6-b771-50465d8f7441",
"organizationINN": "7810950525",
"staffListUID": "a21cfc54-2338-11e9-8111-00155d630402",
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
"postUID": "51f3294e-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Бухгалтер",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb000c-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Бухгалтер",
"genitive": "Бухгалтера",
"dative": "Бухгалтеру",
"accusative": "Бухгалтера",
"ablative": "Бухгалтером",
"prepositional": "Бухгалтере"
}
},
{
"postUID": "51f32951-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Механик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb0093-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Механик",
"genitive": "Механика",
"dative": "Механику",
"accusative": "Механика",
"ablative": "Механиком",
"prepositional": "Механике"
}
},
{
"postUID": "51f3295a-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Инженер ПТО",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfaffdd-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Инженер ПТО",
"genitive": "Инженера ПТО",
"dative": "Инженеру ПТО",
"accusative": "Инженера ПТО",
"ablative": "Инженером ПТО",
"prepositional": "Инженере ПТО"
}
},
{
"postUID": "1649b96d-9319-11e9-812d-00155d630402",
"postName": "Специалист по управленческому учёту",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "1649b96c-9319-11e9-812d-00155d630402",
"inflection": {
"nominative": "Специалист по управленческому учёту",
"genitive": "Специалист по управленческому учёту",
"dative": "Специалист по управленческому учёту",
"accusative": "Специалист по управленческому учёту",
"ablative": "Специалист по управленческому учёту",
"prepositional": "Специалист по управленческому учёту"
}
},
{
"postUID": "1cb37285-ae72-11ec-810a-1831bfcfda3c",
"postName": "Начальник отдела материально-технического снабжения и логистики (ОМТС и логистики)",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9c4988e7-ae75-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Начальник отдела материально-технического снабжения и логистики (ОМТС и логистики)",
"genitive": "Начальника отдела материально-технического снабжения и логистики (ОМТС и логистики)",
"dative": "Начальнику отдела материально-технического снабжения и логистики (ОМТС и логистики)",
"accusative": "Начальника отдела материально-технического снабжения и логистики (ОМТС и логистики)",
"ablative": "Начальником отдела материально-технического снабжения и логистики (ОМТС и логистики)",
"prepositional": "Начальнике отдела материально-технического снабжения и логистики (ОМТС и логистики)"
}
},
{
"postUID": "1cbbcd90-248b-11e9-8111-00155d630402",
"postName": "Агент по снабжению",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "42ee9057-dae8-11ea-80fe-1831bfcfda3c",
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
"postUID": "142e9460-c4b5-11ec-810a-1831bfcfda3c",
"postName": "Водитель-экспедитор",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "6a95047b-d296-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Водитель-экспедитор",
"genitive": "Водителя-экспедитора",
"dative": "Водителю-экспедитору",
"accusative": "Водителя-экспедитора",
"ablative": "Водителем-экспедитором",
"prepositional": "Водителе-экспедиторе"
}
},
{
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfafff8-2338-11e9-8111-00155d630402",
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
"postUID": "3c0af6f9-0bd9-11e8-80c5-00155d4c1e00",
"postName": "Юрист",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "85181e62-fde8-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Юрист",
"genitive": "Юриста",
"dative": "Юристу",
"accusative": "Юриста",
"ablative": "Юристом",
"prepositional": "Юристе"
}
},
{
"postUID": "99884c42-66c3-11e7-a358-50465d8f7441",
"postName": "Экономист по материально-техническому снабжению",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "e64e5494-abde-11ea-80fc-1831bfcfda3c",
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
"postUID": "812f772c-83f9-11ec-8107-1831bfcfda3c",
"postName": "Курьер",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "a88c49e1-83f9-11ec-8107-1831bfcfda3c",
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
"postUID": "cc6e434b-3541-11e7-8dc8-50465d8f7441",
"postName": "Геодезист",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "42ee9050-dae8-11ea-80fe-1831bfcfda3c",
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
"postUID": "bf0dc297-7106-11e7-a850-50465d8f7441",
"postName": "Начальник ПТО",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfaffef-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Начальник ПТО",
"genitive": "Начальника ПТО",
"dative": "Начальнику ПТО",
"accusative": "Начальника ПТО",
"ablative": "Начальником ПТО",
"prepositional": "Начальнике ПТО"
}
},
{
"postUID": "51f3294a-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Производитель работ",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb00cd-2338-11e9-8111-00155d630402",
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
"postUID": "322c3e2c-09ce-11e5-a5b3-50465d8f7441",
"postName": "Руководитель проектов",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb0089-2338-11e9-8111-00155d630402",
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
"postUID": "2529f0af-3a20-11e2-a4d2-0019d11ffeaf",
"postName": "Генеральный директор",
"organizationUID": "2803b065-65a3-11e5-84a7-50465d8f7441",
"organizationINN": "7842528806",
"staffListUID": "7f8e86c4-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Генеральный директор",
"genitive": "Генерального директора",
"dative": "Генеральному директору",
"accusative": "Генерального директора",
"ablative": "Генеральным директором",
"prepositional": "Генеральном директоре"
}
},
{
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "5889fc29-7606-11e9-8123-00155d630402",
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
"postUID": "51f3293b-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Главный инженер",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb0031-2338-11e9-8111-00155d630402",
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
"postUID": "a78efbde-fdd0-11ec-810a-1831bfcfda3c",
"postName": "Ведущий инженер ПТО",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "85181e69-fde8-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Ведущий инженер ПТО",
"genitive": "Ведущего инженера ПТО",
"dative": "Ведущему инженеру ПТО",
"accusative": "Ведущего инженера ПТО",
"ablative": "Ведущим инженером ПТО",
"prepositional": "Ведущем инженере ПТО"
}
},
{
"postUID": "4d2b87e0-25de-11e9-8111-00155d630402",
"postName": "Специалист по охране труда",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "42ee904c-dae8-11ea-80fe-1831bfcfda3c",
"inflection": {
"nominative": "Специалист по охране труда",
"genitive": "Специалист по охране труда",
"dative": "Специалист по охране труда",
"accusative": "Специалист по охране труда",
"ablative": "Специалист по охране труда",
"prepositional": "Специалист по охране труда"
}
},
{
"postUID": "1cbbcd90-248b-11e9-8111-00155d630402",
"postName": "Агент по снабжению",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "8f028ec8-abdf-11ea-80fc-1831bfcfda3c",
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
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "0782e8bd-77ab-11e9-8123-00155d630402",
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
"postUID": "dba907d4-80db-11e8-80fb-00155d4c1e00",
"postName": "Инженер-проектировщик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb007f-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Инженер-проектировщик",
"genitive": "Инженера-проектировщика",
"dative": "Инженеру-проектировщику",
"accusative": "Инженера-проектировщика",
"ablative": "Инженером-проектировщиком",
"prepositional": "Инженере-проектировщике"
}
},
{
"postUID": "51f3294a-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Производитель работ",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "4d2b87d2-25de-11e9-8111-00155d630402",
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
"postUID": "",
"postName": "",
"organizationUID": "2eab7a61-7bfe-11e6-b771-50465d8f7441",
"organizationINN": "7810950525",
"staffListUID": "a21cfc53-2338-11e9-8111-00155d630402",
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
"postUID": "2529f0ae-3a20-11e2-a4d2-0019d11ffeaf",
"postName": "Копровщик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfaff91-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Копровщик",
"genitive": "Копровщика",
"dative": "Копровщику",
"accusative": "Копровщика",
"ablative": "Копровщиком",
"prepositional": "Копровщике"
}
},
{
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb000b-2338-11e9-8111-00155d630402",
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
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb0088-2338-11e9-8111-00155d630402",
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
"postUID": "b5eb9d97-eed7-11eb-8104-1831bfcfda3c",
"postName": "Мастер погрузо-разгрузочных работ",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "6b4f78fc-eed8-11eb-8104-1831bfcfda3c",
"inflection": {
"nominative": "Мастер погрузо-разгрузочных работ",
"genitive": "Мастера погрузо-разгрузочных работ",
"dative": "Мастеру погрузо-разгрузочных работ",
"accusative": "Мастера погрузо-разгрузочных работ",
"ablative": "Мастером погрузо-разгрузочных работ",
"prepositional": "Мастере погрузо-разгрузочных работ"
}
},
{
"postUID": "7047d402-9c32-11e7-bc88-50465d8f7441",
"postName": "Машинист копра",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "0cb2e23f-9cfe-11eb-8103-1831bfcfda3c",
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
"postUID": "51f3295a-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Инженер ПТО",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "0ba8b0a9-dadf-11ea-80fe-1831bfcfda3c",
"inflection": {
"nominative": "Инженер ПТО",
"genitive": "Инженера ПТО",
"dative": "Инженеру ПТО",
"accusative": "Инженера ПТО",
"ablative": "Инженером ПТО",
"prepositional": "Инженере ПТО"
}
},
{
"postUID": "",
"postName": "",
"organizationUID": "2803b065-65a3-11e5-84a7-50465d8f7441",
"organizationINN": "7842528806",
"staffListUID": "7f8e86db-2338-11e9-8111-00155d630402",
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
"postUID": "de94c1a7-3d13-11e2-afa6-0019d11ffeaf",
"postName": "Директор по развитию",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb0002-2338-11e9-8111-00155d630402",
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
"postUID": "733c7934-34ce-11e9-8111-00155d630402",
"postName": "Начальник производства",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "dc052c40-abe0-11ea-80fc-1831bfcfda3c",
"inflection": {
"nominative": "Начальник производства",
"genitive": "Начальник производства",
"dative": "Начальник производства",
"accusative": "Начальник производства",
"ablative": "Начальник производства",
"prepositional": "Начальник производства"
}
},
{
"postUID": "51f3294a-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Производитель работ",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "210f4629-d388-11e9-8130-00155d630402",
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
"postUID": "b1b1b6ae-ed08-11e6-907e-50465d8f7441",
"postName": "Электросварщик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb003b-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Электросварщик",
"genitive": "Электросварщика",
"dative": "Электросварщику",
"accusative": "Электросварщика",
"ablative": "Электросварщиком",
"prepositional": "Электросварщике"
}
},
{
"postUID": "322c3e2c-09ce-11e5-a5b3-50465d8f7441",
"postName": "Руководитель проектов",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "210f4633-d388-11e9-8130-00155d630402",
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
"postUID": "3b0223f2-d751-11e8-8110-00155d630402",
"postName": "Начальник участка",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfaffad-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Начальник участка",
"genitive": "Начальника участка",
"dative": "Начальнику участка",
"accusative": "Начальника участка",
"ablative": "Начальником участка",
"prepositional": "Начальнике участка"
}
},
{
"postUID": "9237d26d-79e2-11ec-8107-1831bfcfda3c",
"postName": "Экономист по материально-техническому учёту",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "edc24bb8-79e2-11ec-8107-1831bfcfda3c",
"inflection": {
"nominative": "Экономист по материально-техническому учёту",
"genitive": "Экономиста по материально-техническому учёту",
"dative": "Экономисту по материально-техническому учёту",
"accusative": "Экономиста по материально-техническому учёту",
"ablative": "Экономистом по материально-техническому учёту",
"prepositional": "Экономисте по материально-техническому учёту"
}
},
{
"postUID": "75ef8094-8a67-11ec-8107-1831bfcfda3c",
"postName": "Программист",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "85181e63-fde8-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Программист",
"genitive": "Программиста",
"dative": "Программисту",
"accusative": "Программиста",
"ablative": "Программистом",
"prepositional": "Программисте"
}
},
{
"postUID": "2529f0b2-3a20-11e2-a4d2-0019d11ffeaf",
"postName": "Стропальщик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb004f-2338-11e9-8111-00155d630402",
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
"postUID": "2529f0b2-3a20-11e2-a4d2-0019d11ffeaf",
"postName": "Стропальщик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfaff74-2338-11e9-8111-00155d630402",
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
"postUID": "6d645f40-ee0b-11ec-810a-1831bfcfda3c",
"postName": "Ведущий специалист по кадровому делопроизводству",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "5b091b8c-ee0e-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Ведущий специалист по кадровому делопроизводству",
"genitive": "Ведущего специалиста по кадровому делопроизводству",
"dative": "Ведущему специалисту по кадровому делопроизводству",
"accusative": "Ведущего специалиста по кадровому делопроизводству",
"ablative": "Ведущим специалистом по кадровому делопроизводству",
"prepositional": "Ведущем специалисте по кадровому делопроизводству"
}
},
{
"postUID": "77de8b3d-d752-11ec-810a-1831bfcfda3c",
"postName": "Начальник отдела охраны труда и промышленной безопасности",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "d1569b79-d75a-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Начальник отдела охраны труда и промышленной безопасности",
"genitive": "Начальника отдела охраны труда и промышленной безопасности",
"dative": "Начальнику отдела охраны труда и промышленной безопасности",
"accusative": "Начальника отдела охраны труда и промышленной безопасности",
"ablative": "Начальником отдела охраны труда и промышленной безопасности",
"prepositional": "Начальнике отдела охраны труда и промышленной безопасности"
}
},
{
"postUID": "1f8c1118-2ce4-11ec-8107-1831bfcfda3c",
"postName": "Начальник отдела",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "8006ff0a-2ce8-11ec-8107-1831bfcfda3c",
"inflection": {
"nominative": "Начальник отдела",
"genitive": "Начальника отдела",
"dative": "Начальнику отдела",
"accusative": "Начальника отдела",
"ablative": "Начальником отдела",
"prepositional": "Начальнике отдела"
}
},
{
"postUID": "9ec59bd7-a8f3-11ec-810a-1831bfcfda3c",
"postName": "Специалист планово-экономического отдела",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "5e512ef1-a8f4-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Специалист планово-экономического отдела",
"genitive": "Специалиста планово-экономического отдела",
"dative": "Специалисту планово-экономического отдела",
"accusative": "Специалиста планово-экономического отдела",
"ablative": "Специалистом планово-экономического отдела",
"prepositional": "Специалисте планово-экономического отдела"
}
},
{
"postUID": "51f3294c-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Главный бухгалтер",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb0015-2338-11e9-8111-00155d630402",
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
"postUID": "74ab7b14-c308-11e9-8130-00155d630402",
"postName": "Специалист по промышленной безопасности",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "b4656084-abe0-11ea-80fc-1831bfcfda3c",
"inflection": {
"nominative": "Специалист по промышленной безопасности",
"genitive": "Специалист по промышленной безопасности",
"dative": "Специалист по промышленной безопасности",
"accusative": "Специалист по промышленной безопасности",
"ablative": "Специалист по промышленной безопасности",
"prepositional": "Специалист по промышленной безопасности"
}
},
{
"postUID": "2529f0ae-3a20-11e2-a4d2-0019d11ffeaf",
"postName": "Копровщик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "d1569b7a-d75a-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Копровщик",
"genitive": "Копровщика",
"dative": "Копровщику",
"accusative": "Копровщика",
"ablative": "Копровщиком",
"prepositional": "Копровщике"
}
},
{
"postUID": "b6661b8d-d74c-11ec-810a-1831bfcfda3c",
"postName": "Специалист по охране труда и промышленной безопасности",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "d1569b77-d75a-11ec-810a-1831bfcfda3c",
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
"postUID": "1c9981b0-7631-11e9-8123-00155d630402",
"postName": "Специалист по логистике",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "1c9981b1-7631-11e9-8123-00155d630402",
"inflection": {
"nominative": "Специалист по логистике",
"genitive": "Специалиста по логистике",
"dative": "Специалисту по логистике",
"accusative": "Специалиста по логистике",
"ablative": "Специалистом по логистике",
"prepositional": "Специалисте по логистике"
}
},
{
"postUID": "1c9981b0-7631-11e9-8123-00155d630402",
"postName": "Специалист по логистике",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "8f028ec7-abdf-11ea-80fc-1831bfcfda3c",
"inflection": {
"nominative": "Специалист по логистике",
"genitive": "Специалиста по логистике",
"dative": "Специалисту по логистике",
"accusative": "Специалиста по логистике",
"ablative": "Специалистом по логистике",
"prepositional": "Специалисте по логистике"
}
},
{
"postUID": "75910d7c-f8dd-11e2-bec1-0019d11ffeaf",
"postName": "Технический директор",
"organizationUID": "2803b065-65a3-11e5-84a7-50465d8f7441",
"organizationINN": "7842528806",
"staffListUID": "7f8e86cd-2338-11e9-8111-00155d630402",
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
"postUID": "75ef8094-8a67-11ec-8107-1831bfcfda3c",
"postName": "Программист",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "899624c4-8a67-11ec-8107-1831bfcfda3c",
"inflection": {
"nominative": "Программист",
"genitive": "Программиста",
"dative": "Программисту",
"accusative": "Программиста",
"ablative": "Программистом",
"prepositional": "Программисте"
}
},
{
"postUID": "142e9460-c4b5-11ec-810a-1831bfcfda3c",
"postName": "Водитель-экспедитор",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "e52cc108-c4c5-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Водитель-экспедитор",
"genitive": "Водителя-экспедитора",
"dative": "Водителю-экспедитору",
"accusative": "Водителя-экспедитора",
"ablative": "Водителем-экспедитором",
"prepositional": "Водителе-экспедиторе"
}
},
{
"postUID": "0782e8b4-77ab-11e9-8123-00155d630402",
"postName": "Уборщица",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "8f028ec9-abdf-11ea-80fc-1831bfcfda3c",
"inflection": {
"nominative": "Уборщица",
"genitive": "Уборщицы",
"dative": "Уборщице",
"accusative": "Уборщицу",
"ablative": "Уборщицей",
"prepositional": "Уборщице"
}
},
{
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "899624c3-8a67-11ec-8107-1831bfcfda3c",
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
"postUID": "99884c42-66c3-11e7-a358-50465d8f7441",
"postName": "Экономист по материально-техническому снабжению",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfaffc0-2338-11e9-8111-00155d630402",
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
"postUID": "e186cdb2-b70a-11ec-810a-1831bfcfda3c",
"postName": "Руководитель проектного отдела",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "bc411fe8-b70c-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Руководитель проектного отдела",
"genitive": "Руководителя проектного отдела",
"dative": "Руководителю проектного отдела",
"accusative": "Руководителя проектного отдела",
"ablative": "Руководителем проектного отдела",
"prepositional": "Руководителе проектного отдела"
}
},
{
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "7f8e86e7-2338-11e9-8111-00155d630402",
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
"postUID": "51f32951-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Механик",
"organizationUID": "2803b065-65a3-11e5-84a7-50465d8f7441",
"organizationINN": "7842528806",
"staffListUID": "7f8e86dc-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Механик",
"genitive": "Механика",
"dative": "Механику",
"accusative": "Механика",
"ablative": "Механиком",
"prepositional": "Механике"
}
},
{
"postUID": "74ab7b14-c308-11e9-8130-00155d630402",
"postName": "Специалист по промышленной безопасности",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "74ab7b18-c308-11e9-8130-00155d630402",
"inflection": {
"nominative": "Специалист по промышленной безопасности",
"genitive": "Специалист по промышленной безопасности",
"dative": "Специалист по промышленной безопасности",
"accusative": "Специалист по промышленной безопасности",
"ablative": "Специалист по промышленной безопасности",
"prepositional": "Специалист по промышленной безопасности"
}
},
{
"postUID": "b2e33c9b-fdc4-11ec-810a-1831bfcfda3c",
"postName": "Специалист тендерного отдела",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "85181e67-fde8-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Специалист тендерного отдела",
"genitive": "Специалиста тендерного отдела",
"dative": "Специалисту тендерного отдела",
"accusative": "Специалиста тендерного отдела",
"ablative": "Специалистом тендерного отдела",
"prepositional": "Специалисте тендерного отдела"
}
},
{
"postUID": "d484535d-191b-11e7-9c90-50465d8f7441",
"postName": "Секретарь руководителя",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb0028-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Секретарь руководителя",
"genitive": "Секретаря руководителя",
"dative": "Секретарю руководителя",
"accusative": "Секретаря руководителя",
"ablative": "Секретарём руководителя",
"prepositional": "Секретаре руководителя"
}
},
{
"postUID": "bb85b835-c4b3-11ec-810a-1831bfcfda3c",
"postName": "Заместитель главного бухгалтера",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "6a95047d-d296-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Заместитель главного бухгалтера",
"genitive": "Заместителя главного бухгалтера",
"dative": "Заместителю главного бухгалтера",
"accusative": "Заместителя главного бухгалтера",
"ablative": "Заместителем главного бухгалтера",
"prepositional": "Заместителе главного бухгалтера"
}
},
{
"postUID": "08e235ab-a8f0-11ec-810a-1831bfcfda3c",
"postName": "Начальник планово-экономического отдела",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "5e512ef0-a8f4-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Начальник планово-экономического отдела",
"genitive": "Начальника планово-экономического отдела",
"dative": "Начальнику планово-экономического отдела",
"accusative": "Начальника планово-экономического отдела",
"ablative": "Начальником планово-экономического отдела",
"prepositional": "Начальнике планово-экономического отдела"
}
},
{
"postUID": "2529f0b0-3a20-11e2-a4d2-0019d11ffeaf",
"postName": "Машинист крана",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb0045-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Машинист крана",
"genitive": "Машиниста крана",
"dative": "Машинисту крана",
"accusative": "Машиниста крана",
"ablative": "Машинистом крана",
"prepositional": "Машинисте крана"
}
},
{
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb001e-2338-11e9-8111-00155d630402",
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
"postUID": "cc5a890d-b7e4-11e7-80bf-00155d4c1e00",
"postName": "Менеджер по персоналу",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfaffd3-2338-11e9-8111-00155d630402",
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
"postUID": "f6f6ad65-c4c0-11ec-810a-1831bfcfda3c",
"postName": "Аналитик структуры управления и оптимизации бизнес-процессов",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "e52cc109-c4c5-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Аналитик структуры управления и оптимизации бизнес-процессов",
"genitive": "Аналитика структуры управления и оптимизаций бизнес-процессов",
"dative": "Аналитику структуры управления и оптимизациям бизнес-процессов",
"accusative": "Аналитика структуры управления и оптимизации бизнес-процессов",
"ablative": "Аналитиком структуры управления и оптимизациями бизнес-процессов",
"prepositional": "Аналитике структуры управления и оптимизациях бизнес-процессов"
}
},
{
"postUID": "98f03d0c-79e6-11ec-8107-1831bfcfda3c",
"postName": "Ведущий бухгалтер",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "01183830-79e7-11ec-8107-1831bfcfda3c",
"inflection": {
"nominative": "Ведущий бухгалтер",
"genitive": "Ведущего бухгалтера",
"dative": "Ведущему бухгалтеру",
"accusative": "Ведущего бухгалтера",
"ablative": "Ведущим бухгалтером",
"prepositional": "Ведущем бухгалтере"
}
},
{
"postUID": "cb21e6d3-93cf-11ec-810a-1831bfcfda3c",
"postName": "Специалист",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "dade0036-93cf-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Специалист",
"genitive": "Специалиста",
"dative": "Специалисту",
"accusative": "Специалиста",
"ablative": "Специалистом",
"prepositional": "Специалисте"
}
},
{
"postUID": "3c0af6f9-0bd9-11e8-80c5-00155d4c1e00",
"postName": "Юрист",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfafff9-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Юрист",
"genitive": "Юриста",
"dative": "Юристу",
"accusative": "Юриста",
"ablative": "Юристом",
"prepositional": "Юристе"
}
},
{
"postUID": "2529f0b2-3a20-11e2-a4d2-0019d11ffeaf",
"postName": "Стропальщик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb00d7-2338-11e9-8111-00155d630402",
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
"postUID": "99884c42-66c3-11e7-a358-50465d8f7441",
"postName": "Экономист по материально-техническому снабжению",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "5889fc24-7606-11e9-8123-00155d630402",
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
"postUID": "dba907d2-80db-11e8-80fb-00155d4c1e00",
"postName": "Кладовщик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfb006b-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Кладовщик",
"genitive": "Кладовщика",
"dative": "Кладовщику",
"accusative": "Кладовщика",
"ablative": "Кладовщиком",
"prepositional": "Кладовщике"
}
},
{
"postUID": "de94c1ab-3d13-11e2-afa6-0019d11ffeaf",
"postName": "Мастер строительно-монтажных работ (СМР)",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfaffa4-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Мастер строительно-монтажных работ (СМР)",
"genitive": "Мастера строительно-монтажных работ (СМР)",
"dative": "Мастеру строительно-монтажных работ (СМР)",
"accusative": "Мастера строительно-монтажных работ (СМР)",
"ablative": "Мастером строительно-монтажных работ (СМР)",
"prepositional": "Мастере строительно-монтажных работ (СМР)"
}
},
{
"postUID": "8094d88f-717f-11e9-811d-00155d630402",
"postName": "Подсобный рабочий",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "42ee9055-dae8-11ea-80fe-1831bfcfda3c",
"inflection": {
"nominative": "Подсобный рабочий",
"genitive": "Подсобного рабочего",
"dative": "Подсобному рабочему",
"accusative": "Подсобного рабочего",
"ablative": "Подсобным рабочим",
"prepositional": "Подсобном рабочем"
}
},
{
"postUID": "199cb9ef-3408-11e9-8111-00155d630402",
"postName": "Техник",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "199cb9ee-3408-11e9-8111-00155d630402",
"inflection": {
"nominative": "Техник",
"genitive": "Техника",
"dative": "Технику",
"accusative": "Техника",
"ablative": "Техником",
"prepositional": "Технике"
}
},
{
"postUID": "3c0af6f9-0bd9-11e8-80c5-00155d4c1e00",
"postName": "Юрист",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "def6e7bc-e33a-11ec-810a-1831bfcfda3c",
"inflection": {
"nominative": "Юрист",
"genitive": "Юриста",
"dative": "Юристу",
"accusative": "Юриста",
"ablative": "Юристом",
"prepositional": "Юристе"
}
},
{
"postUID": "",
"postName": "",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfaffdc-2338-11e9-8111-00155d630402",
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
"postUID": "51f3294a-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Производитель работ",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"staffListUID": "9bfaff6a-2338-11e9-8111-00155d630402",
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
"postUID": "1bb5b483-da92-11e3-8bdf-50465d8f7441",
"postName": "Главный механик",
"organizationUID": "2803b065-65a3-11e5-84a7-50465d8f7441",
"organizationINN": "7842528806",
"staffListUID": "7f8e86d6-2338-11e9-8111-00155d630402",
"inflection": {
"nominative": "Главный механик",
"genitive": "Главного механика",
"dative": "Главному механику",
"accusative": "Главного механика",
"ablative": "Главным механиком",
"prepositional": "Главном механике"
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
                $employeePost = Employees1cpost::updateOrCreate(
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
