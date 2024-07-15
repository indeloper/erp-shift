<?php

namespace Database\Seeders;

use App\Models\Company\Company;
use App\Models\Employees\Employees1cPost;
use App\Models\Employees\Employees1cPostInflection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class employeePostsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = json_decode('{"data": [
{
"postUID": "b06fbb5e-e6d9-11e7-80c4-00155d4c1e00",
"postName": "Специалист по договорной и претензионной работе",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "e186cdb2-b70a-11ec-810a-1831bfcfda3c",
"postName": "Руководитель проектного отдела",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "cb21e6d3-93cf-11ec-810a-1831bfcfda3c",
"postName": "Специалист",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "2ede92e2-6302-11e8-80fb-00155d4c1e00",
"postName": "Инженер по строительному контролю",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "199cb9ef-3408-11e9-8111-00155d630402",
"postName": "Техник",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "84f77cee-bc68-11e2-a2ac-0019d11ffeaf",
"postName": "Электрослесарь  по ремонту электрооборудования",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "2529f0af-3a20-11e2-a4d2-0019d11ffeaf",
"postName": "Генеральный директор",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "d447404a-d777-11ed-80bc-000c29565159",
"postName": "Помощник специалиста по логистике",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"inflection": {
"nominative": "Помощник специалиста по логистике",
"genitive": "Помощника специалиста по логистике",
"dative": "Помощнику специалиста по логистике",
"accusative": "Помощника специалиста по логистике",
"ablative": "Помощником специалиста по логистике",
"prepositional": "Помощнике специалиста по логистике"
}
},
{
"postUID": "289ab6dd-fdc4-11ec-810a-1831bfcfda3c",
"postName": "Начальник тендерного отдела",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "64230fa8-fde2-11ec-810a-1831bfcfda3c",
"postName": "Ведущий экономист планово-экономического отдела",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "2529f0b0-3a20-11e2-a4d2-0019d11ffeaf",
"postName": "Машинист крана",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "cc5a890d-b7e4-11e7-80bf-00155d4c1e00",
"postName": "Менеджер по персоналу",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "de94c1a7-3d13-11e2-afa6-0019d11ffeaf",
"postName": "Директор по развитию",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "9ec59bd7-a8f3-11ec-810a-1831bfcfda3c",
"postName": "Специалист планово-экономического отдела",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "51f32951-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Механик",
"organizationUID": "2803b065-65a3-11e5-84a7-50465d8f7441",
"organizationINN": "7842528806",
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
"postUID": "a0eefd3f-8819-11e9-8123-00155d630402",
"postName": "Инженер ПТО",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "b6661b8d-d74c-11ec-810a-1831bfcfda3c",
"postName": "Специалист по охране труда и промышленной безопасности",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "18f4e141-8d2e-11e6-b1e8-50465d8f7441",
"postName": "Заведующий складом",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "7ed9795e-00e5-11e4-8bb5-50465d8f7441",
"postName": "Финансовый директор",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "4d2b87e0-25de-11e9-8111-00155d630402",
"postName": "Специалист по охране труда",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "7fc0bc2e-8fea-11ed-80bb-000c29565159",
"postName": "Машинист крана автомобильного",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"inflection": {
"nominative": "Машинист крана автомобильного",
"genitive": "Машиниста крана автомобильного",
"dative": "Машинисту крана автомобильного",
"accusative": "Машиниста крана автомобильного",
"ablative": "Машинистом крана автомобильного",
"prepositional": "Машинисте крана автомобильного"
}
},
{
"postUID": "75910d7c-f8dd-11e2-bec1-0019d11ffeaf",
"postName": "Технический директор",
"organizationUID": "2803b065-65a3-11e5-84a7-50465d8f7441",
"organizationINN": "7842528806",
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
"postUID": "5783b8ab-415d-11ec-8107-1831bfcfda3c",
"postName": "Руководитель отдела персонала",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "75ef8094-8a67-11ec-8107-1831bfcfda3c",
"postName": "Программист",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "51f3294e-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Бухгалтер",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "51f32938-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Электрогазосварщик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "1cbbcd90-248b-11e9-8111-00155d630402",
"postName": "Агент по снабжению",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "394c2bc0-c7ce-11ed-80bb-000c29565159",
"postName": "Секретарь",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"inflection": {
"nominative": "Секретарь",
"genitive": "Секретаря",
"dative": "Секретарю",
"accusative": "Секретаря",
"ablative": "Секретарём",
"prepositional": "Секретаре"
}
},
{
"postUID": "190c115a-fdd2-11ec-810a-1831bfcfda3c",
"postName": "Начальник проектного отдела",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "",
"postName": "",
"organizationUID": "2eab7a61-7bfe-11e6-b771-50465d8f7441",
"organizationINN": "7810950525",
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
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "1bb5b483-da92-11e3-8bdf-50465d8f7441",
"postName": "Главный механик",
"organizationUID": "2803b065-65a3-11e5-84a7-50465d8f7441",
"organizationINN": "7842528806",
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
"postUID": "1bb5b483-da92-11e3-8bdf-50465d8f7441",
"postName": "Главный механик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "08e235ab-a8f0-11ec-810a-1831bfcfda3c",
"postName": "Начальник планово-экономического отдела",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "b1b1b6ae-ed08-11e6-907e-50465d8f7441",
"postName": "Электросварщик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "98f03d0c-79e6-11ec-8107-1831bfcfda3c",
"postName": "Ведущий бухгалтер",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "9237d26d-79e2-11ec-8107-1831bfcfda3c",
"postName": "Экономист по материально-техническому учёту",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "5c4306bd-2f45-11ed-810b-1831bfcfda3c",
"postName": "Инженер по безопасности движения",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "dba907d2-80db-11e8-80fb-00155d4c1e00",
"postName": "Кладовщик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "8094d88f-717f-11e9-811d-00155d630402",
"postName": "Подсобный рабочий",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "",
"postName": "",
"organizationUID": "2803b065-65a3-11e5-84a7-50465d8f7441",
"organizationINN": "7842528806",
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
"postUID": "a78efbde-fdd0-11ec-810a-1831bfcfda3c",
"postName": "Ведущий инженер ПТО",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "3b0223f2-d751-11e8-8110-00155d630402",
"postName": "Начальник участка",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "77de8b3d-d752-11ec-810a-1831bfcfda3c",
"postName": "Начальник отдела охраны труда и промышленной безопасности",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "c2d10949-79e6-11ec-8107-1831bfcfda3c",
"postName": "Бухгалтер по расчету заработной платы",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "48b87ee7-3666-11e9-8111-00155d630402",
"postName": "Архивариус",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "142e9460-c4b5-11ec-810a-1831bfcfda3c",
"postName": "Водитель-экспедитор",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "6d645f40-ee0b-11ec-810a-1831bfcfda3c",
"postName": "Ведущий специалист по кадровому делопроизводству",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "0782e8b4-77ab-11e9-8123-00155d630402",
"postName": "Уборщица",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "7047d402-9c32-11e7-bc88-50465d8f7441",
"postName": "Машинист копра",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "2529f0af-3a20-11e2-a4d2-0019d11ffeaf",
"postName": "Генеральный директор",
"organizationUID": "2803b065-65a3-11e5-84a7-50465d8f7441",
"organizationINN": "7842528806",
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
"postUID": "51f3294a-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Производитель работ",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "1f8c1118-2ce4-11ec-8107-1831bfcfda3c",
"postName": "Начальник отдела",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "f6c8ad83-a62c-11ed-80bb-000c29565159",
"postName": "Специалист по административно-хозяйственному обеспечению",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"inflection": {
"nominative": "Специалист по административно-хозяйственному обеспечению",
"genitive": "Специалиста по административно-хозяйственному обеспечению",
"dative": "Специалисту по административно-хозяйственному обеспечению",
"accusative": "Специалиста по административно-хозяйственному обеспечению",
"ablative": "Специалистом по административно-хозяйственному обеспечению",
"prepositional": "Специалисте по административно-хозяйственному обеспечению"
}
},
{
"postUID": "1cb37285-ae72-11ec-810a-1831bfcfda3c",
"postName": "Начальник отдела материально-технического снабжения и логистики (ОМТС и логистики)",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "b2e33c9b-fdc4-11ec-810a-1831bfcfda3c",
"postName": "Специалист тендерного отдела",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "dba907d4-80db-11e8-80fb-00155d4c1e00",
"postName": "Инженер-проектировщик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "4d2b87de-25de-11e9-8111-00155d630402",
"postName": "Специалист по охране труда",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "75910d7c-f8dd-11e2-bec1-0019d11ffeaf",
"postName": "Технический директор",
"organizationUID": "2eab7a61-7bfe-11e6-b771-50465d8f7441",
"organizationINN": "7810950525",
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
"postUID": "285ed685-2f46-11ed-810b-1831bfcfda3c",
"postName": "Помощник руководителя",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "de94c1ab-3d13-11e2-afa6-0019d11ffeaf",
"postName": "Мастер строительно-монтажных работ (СМР)",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "99884c42-66c3-11e7-a358-50465d8f7441",
"postName": "Экономист по материально-техническому снабжению",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "e98cfae4-79e6-11ec-8107-1831bfcfda3c",
"postName": "Бухгалтер по учету материально-производственных запасов",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "97ab6fa3-a22c-11ed-80bb-000c29565159",
"postName": "Заместитель начальника ПТО",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"inflection": {
"nominative": "Заместитель начальника ПТО",
"genitive": "Заместителя начальника ПТО",
"dative": "Заместителю начальника ПТО",
"accusative": "Заместителя начальника ПТО",
"ablative": "Заместителем начальника ПТО",
"prepositional": "Заместителе начальника ПТО"
}
},
{
"postUID": "d484535d-191b-11e7-9c90-50465d8f7441",
"postName": "Секретарь руководителя",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "b5eb9d97-eed7-11eb-8104-1831bfcfda3c",
"postName": "Мастер погрузо-разгрузочных работ",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "51f3295a-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Инженер ПТО",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "bf0dc297-7106-11e7-a850-50465d8f7441",
"postName": "Начальник ПТО",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "51f3293b-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Главный инженер",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "812f772c-83f9-11ec-8107-1831bfcfda3c",
"postName": "Курьер",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "1649b96d-9319-11e9-812d-00155d630402",
"postName": "Специалист по управленческому учёту",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "f6f6ad65-c4c0-11ec-810a-1831bfcfda3c",
"postName": "Аналитик структуры управления и оптимизации бизнес-процессов",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "74ab7b14-c308-11e9-8130-00155d630402",
"postName": "Специалист по промышленной безопасности",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "2529f0b2-3a20-11e2-a4d2-0019d11ffeaf",
"postName": "Стропальщик",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "cc6e434b-3541-11e7-8dc8-50465d8f7441",
"postName": "Геодезист",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "1c9981b0-7631-11e9-8123-00155d630402",
"postName": "Специалист по логистике",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
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
"postUID": "51f3294c-3a11-11e2-a4d2-0019d11ffeaf",
"postName": "Главный бухгалтер",
"organizationUID": "4be56ff8-3a11-11e2-a4d2-0019d11ffeaf",
"organizationINN": "7807348494",
"inflection": {
"nominative": "Главный бухгалтер",
"genitive": "Главного бухгалтера",
"dative": "Главному бухгалтеру",
"accusative": "Главного бухгалтера",
"ablative": "Главным бухгалтером",
"prepositional": "Главном бухгалтере"
}
}
]
        }', false);

        foreach ($posts->data as $post) {
            if (empty($post->postUID)) {
                continue;
            }

            $company = Company::where('company_1c_uid', $post->organizationUID)->get()->first();
            if (isset($company)) {
                $employeePost = Employees1cPost::updateOrCreate(
                    [
                        'post_1c_uid' => $post->postUID,
                    ],
                    [
                        'name' => $post->postName,
                        'company_id' => $company->id,
                    ]
                );

                Log::channel('stderr')->info('[info] '.var_dump($post->inflection->nominative));
                Employees1cPostInflection::updateOrCreate(
                    [
                        'post_id' => $employeePost->id,
                    ],
                    [
                        'nominative' => $post->inflection->nominative,
                        'genitive' => $post->inflection->genitive,
                        'dative' => $post->inflection->dative,
                        'accusative' => $post->inflection->accusative,
                        'ablative' => $post->inflection->ablative,
                        'prepositional' => $post->inflection->prepositional,
                    ]
                );

            }
        }
    }
}
