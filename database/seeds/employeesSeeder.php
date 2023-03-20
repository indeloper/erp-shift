<?php

use App\Models\Company\Company;
use App\Models\Employees\Employee;
use App\Models\Employees\Employees1cPost;
use App\Models\Employees\Employees1cSubdivision;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\Environment\Console;
use Telegram\Bot\Laravel\Facades\Telegram;

class employeesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $employees = json_decode('{"data": [

{
"employeeUID": "75491d18-23c2-11e9-8111-00155d630402",
"personnelNumber": "0000-00012",
"employeeINN": "470103203431",
"employeeGender": "M",
"employeeName": "Баранов Вадим Михайлович",
"employeeLastName": "Баранов",
"employeeFirstName": "Вадим",
"employeePatronymic": "Михайлович",
"employeePhone": "8-911-160-26-49",
"employee1CPostUID": "db46bb1b-23a8-11e9-8111-00155d630402",
"employee1CSubdivisionUID": "b5600078-59fb-11e9-811b-00155d630402",
"individual1CCode": "00-0000009",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806",
"birthday": "1985-05-12",
"dateReceived": "2018-10-16",
"dismissalDate": "2022-09-26",
"inflection": {
"nominative": "Баранов Вадим Михайлович",
"genitive": "Баранова Вадима Михайловича",
"dative": "Баранову Вадиму Михайловичу",
"accusative": "Баранова Вадима Михайловича",
"ablative": "Барановым Вадимом Михайловичем",
"prepositional": "Баранове Вадиме Михайловиче"
}
},
{
"employeeUID": "36072308-86eb-11eb-814e-00155d630402",
"personnelNumber": "00ЗК-00040",
"employeeINN": "600401213524",
"employeeGender": "M",
"employeeName": "Денисенко Сергей Викторович",
"employeeLastName": "Денисенко",
"employeeFirstName": "Сергей",
"employeePatronymic": "Викторович",
"employeePhone": "+7 (921) 3806341",
"employee1CPostUID": "db46bb1b-23a8-11e9-8111-00155d630402",
"employee1CSubdivisionUID": "b5600078-59fb-11e9-811b-00155d630402",
"individual1CCode": "ЗК-0000010",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806",
"birthday": "1982-02-07",
"dateReceived": "2021-03-17",
"dismissalDate": "2022-08-04",
"inflection": {
"nominative": "Денисенко Сергей Викторович",
"genitive": "Денисенко Сергея Викторовича",
"dative": "Денисенко Сергею Викторовичу",
"accusative": "Денисенко Сергея Викторовича",
"ablative": "Денисенко Сергеем Викторовичем",
"prepositional": "Денисенко Сергее Викторовиче"
}
},
{
"employeeUID": "75491d1b-23c2-11e9-8111-00155d630402",
"personnelNumber": "0000-00013",
"employeeINN": "781431274840",
"employeeGender": "M",
"employeeName": "Добриян Александр Владимирович",
"employeeLastName": "Добриян",
"employeeFirstName": "Александр",
"employeePatronymic": "Владимирович",
"employeePhone": "8-921-927-82-82",
"employee1CPostUID": "db46bb1b-23a8-11e9-8111-00155d630402",
"employee1CSubdivisionUID": "b5600078-59fb-11e9-811b-00155d630402",
"individual1CCode": "00-0000010",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806",
"birthday": "1985-01-11",
"dateReceived": "2018-10-16",
"dismissalDate": "2022-08-17",
"inflection": {
"nominative": "Добриян Александр Владимирович",
"genitive": "Добрияна Александра Владимировича",
"dative": "Добрияну Александру Владимировичу",
"accusative": "Добрияна Александра Владимировича",
"ablative": "Добрияном Александром Владимировичем",
"prepositional": "Добрияне Александре Владимировиче"
}
},
{
"employeeUID": "75491d0c-23c2-11e9-8111-00155d630402",
"personnelNumber": "0000-00007",
"employeeINN": "027101207864",
"employeeGender": "F",
"employeeName": "Зайцева Дина Ильгизовна",
"employeeLastName": "Зайцева",
"employeeFirstName": "Дина",
"employeePatronymic": "Ильгизовна",
"employeePhone": "89112443930",
"employee1CPostUID": "db46bb19-23a8-11e9-8111-00155d630402",
"employee1CSubdivisionUID": "db46bb1f-23a8-11e9-8111-00155d630402",
"individual1CCode": "00-0000005",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806",
"birthday": "1990-03-30",
"dateReceived": "2017-12-01",
"dismissalDate": "",
"inflection": {
"nominative": "Зайцева Дина Ильгизовна",
"genitive": "Зайцевой Дины Ильгизовны",
"dative": "Зайцевой Дине Ильгизовне",
"accusative": "Зайцеву Дину Ильгизовну",
"ablative": "Зайцевой Диной Ильгизовной",
"prepositional": "Зайцевой Дине Ильгизовне"
}
},
{
"employeeUID": "5e1fdb9f-77dd-11ea-8141-00155d630402",
"personnelNumber": "00ЗК-0006",
"employeeINN": "026824244129",
"employeeGender": "M",
"employeeName": "Ильин Ян Викторович",
"employeeLastName": "Ильин",
"employeeFirstName": "Ян",
"employeePatronymic": "Викторович",
"employeePhone": "",
"employee1CPostUID": "",
"employee1CSubdivisionUID": "",
"individual1CCode": "ЗК-0000002",
"organizationUID": "96c4b46b-2848-11e9-8111-00155d630402",
"organizationINN": "7810950525",
"birthday": "1989-06-14",
"dateReceived": "",
"dismissalDate": "",
"inflection": {
"nominative": "Ильин Ян Викторович",
"genitive": "Ильина Яна Викторовича",
"dative": "Ильину Яну Викторовичу",
"accusative": "Ильина Яна Викторовича",
"ablative": "Ильиным Яном Викторовичем",
"prepositional": "Ильине Яне Викторовиче"
}
},
{
"employeeUID": "96c4b470-2848-11e9-8111-00155d630402",
"personnelNumber": "0000-0001",
"employeeINN": "780721972500",
"employeeGender": "M",
"employeeName": "Исмагилов Александр Данилович",
"employeeLastName": "Исмагилов",
"employeeFirstName": "Александр",
"employeePatronymic": "Данилович",
"employeePhone": "8 (911) 903-33-99",
"employee1CPostUID": "db46bb16-23a8-11e9-8111-00155d630402",
"employee1CSubdivisionUID": "96c4b485-2848-11e9-8111-00155d630402",
"individual1CCode": "00-0000001",
"organizationUID": "96c4b46b-2848-11e9-8111-00155d630402",
"organizationINN": "7810950525",
"birthday": "1985-05-04",
"dateReceived": "2014-12-05",
"dismissalDate": "",
"inflection": {
"nominative": "Исмагилов Александр Данилович",
"genitive": "Исмагилова Александра Даниловича",
"dative": "Исмагилову Александру Даниловичу",
"accusative": "Исмагилова Александра Даниловича",
"ablative": "Исмагиловым Александром Даниловичем",
"prepositional": "Исмагилове Александре Даниловиче"
}
},
{
"employeeUID": "8a343c04-23c0-11e9-8111-00155d630402",
"personnelNumber": "0000-00001",
"employeeINN": "780721972500",
"employeeGender": "M",
"employeeName": "Исмагилов Александр Данилович",
"employeeLastName": "Исмагилов",
"employeeFirstName": "Александр",
"employeePatronymic": "Данилович",
"employeePhone": "8 (911) 903-33-99",
"employee1CPostUID": "db46bb16-23a8-11e9-8111-00155d630402",
"employee1CSubdivisionUID": "db46bb1f-23a8-11e9-8111-00155d630402",
"individual1CCode": "00-0000001",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806",
"birthday": "1985-05-04",
"dateReceived": "2015-07-16",
"dismissalDate": "",
"inflection": {
"nominative": "Исмагилов Александр Данилович",
"genitive": "Исмагилова Александра Даниловича",
"dative": "Исмагилову Александру Даниловичу",
"accusative": "Исмагилова Александра Даниловича",
"ablative": "Исмагиловым Александром Даниловичем",
"prepositional": "Исмагилове Александре Даниловиче"
}
},
{
"employeeUID": "75491d03-23c2-11e9-8111-00155d630402",
"personnelNumber": "0000-00004",
"employeeINN": "780707496810",
"employeeGender": "M",
"employeeName": "Исмагилов Данил Михайлович",
"employeeLastName": "Исмагилов",
"employeeFirstName": "Данил",
"employeePatronymic": "Михайлович",
"employeePhone": "",
"employee1CPostUID": "db46bb18-23a8-11e9-8111-00155d630402",
"employee1CSubdivisionUID": "db46bb1f-23a8-11e9-8111-00155d630402",
"individual1CCode": "00-0000002",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806",
"birthday": "1955-10-25",
"dateReceived": "2016-05-23",
"dismissalDate": "",
"inflection": {
"nominative": "Исмагилов Данил Михайлович",
"genitive": "Исмагилова Данила Михайловича",
"dative": "Исмагилову Данилу Михайловичу",
"accusative": "Исмагилова Данила Михайловича",
"ablative": "Исмагиловым Данилом Михайловичем",
"prepositional": "Исмагилове Даниле Михайловиче"
}
},
{
"employeeUID": "8fba8539-3b3c-11e9-8111-00155d630402",
"personnelNumber": "00ЗК-00029",
"employeeINN": "471803919208",
"employeeGender": "M",
"employeeName": "Калмыков Виталий Борисович",
"employeeLastName": "Калмыков",
"employeeFirstName": "Виталий",
"employeePatronymic": "Борисович",
"employeePhone": "8-921-979-98-96",
"employee1CPostUID": "db46bb1b-23a8-11e9-8111-00155d630402",
"employee1CSubdivisionUID": "b5600078-59fb-11e9-811b-00155d630402",
"individual1CCode": "00-0000030",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806",
"birthday": "1979-01-01",
"dateReceived": "2019-03-01",
"dismissalDate": "2022-08-23",
"inflection": {
"nominative": "Калмыков Виталий Борисович",
"genitive": "Калмыкова Виталия Борисовича",
"dative": "Калмыкову Виталию Борисовичу",
"accusative": "Калмыкова Виталия Борисовича",
"ablative": "Калмыковым Виталием Борисовичем",
"prepositional": "Калмыкове Виталии Борисовиче"
}
},
{
"employeeUID": "31ec6872-785b-11ec-816a-00155d630402",
"personnelNumber": "00ЗК-0010",
"employeeINN": "780530148958",
"employeeGender": "M",
"employeeName": "Карпов Александр Валерьевич",
"employeeLastName": "Карпов",
"employeeFirstName": "Александр",
"employeePatronymic": "Валерьевич",
"employeePhone": "",
"employee1CPostUID": "db46bb1b-23a8-11e9-8111-00155d630402",
"employee1CSubdivisionUID": "b5600078-59fb-11e9-811b-00155d630402",
"individual1CCode": "ЗК-0000012",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806",
"birthday": "1980-08-03",
"dateReceived": "2022-01-17",
"dismissalDate": "2022-09-29",
"inflection": {
"nominative": "Карпов Александр Валерьевич",
"genitive": "Карпова Александра Валерьевича",
"dative": "Карпову Александру Валерьевичу",
"accusative": "Карпова Александра Валерьевича",
"ablative": "Карповым Александром Валерьевичем",
"prepositional": "Карпове Александре Валерьевиче"
}
},
{
"employeeUID": "326cf960-a0e4-11ea-8141-00155d630402",
"personnelNumber": "00ЗК-00036",
"employeeINN": "781413587689",
"employeeGender": "M",
"employeeName": "Колескин Дмитрий Игоревич",
"employeeLastName": "Колескин",
"employeeFirstName": "Дмитрий",
"employeePatronymic": "Игоревич",
"employeePhone": "8-921-357-88-38",
"employee1CPostUID": "db46bb1b-23a8-11e9-8111-00155d630402",
"employee1CSubdivisionUID": "b5600078-59fb-11e9-811b-00155d630402",
"individual1CCode": "ЗК-0000004",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806",
"birthday": "1983-04-19",
"dateReceived": "2020-06-11",
"dismissalDate": "2022-09-05",
"inflection": {
"nominative": "Колескин Дмитрий Игоревич",
"genitive": "Колескина Дмитрия Игоревича",
"dative": "Колескину Дмитрию Игоревичу",
"accusative": "Колескина Дмитрия Игоревича",
"ablative": "Колескиным Дмитрием Игоревичем",
"prepositional": "Колескине Дмитрии Игоревиче"
}
},
{
"employeeUID": "e325dd5a-2467-11e9-8111-00155d630402",
"personnelNumber": "0000-00026",
"employeeINN": "784205305850",
"employeeGender": "M",
"employeeName": "Мирзаев Азиз Холмахаммадович",
"employeeLastName": "Мирзаев",
"employeeFirstName": "Азиз",
"employeePatronymic": "Холмахаммадович",
"employeePhone": "8-964-333-90-06",
"employee1CPostUID": "db46bb1e-23a8-11e9-8111-00155d630402",
"employee1CSubdivisionUID": "b5600078-59fb-11e9-811b-00155d630402",
"individual1CCode": "00-0000023",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806",
"birthday": "1989-04-03",
"dateReceived": "2019-01-25",
"dismissalDate": "",
"inflection": {
"nominative": "Мирзаев Азиз Холмахаммадович",
"genitive": "Мирзаева Азиза Холмахаммадовича",
"dative": "Мирзаеву Азизу Холмахаммадовичу",
"accusative": "Мирзаева Азиза Холмахаммадовича",
"ablative": "Мирзаевым Азизом Холмахаммадовичем",
"prepositional": "Мирзаеве Азизе Холмахаммадовиче"
}
},
{
"employeeUID": "450cb109-dffc-11eb-8154-00155d630402",
"personnelNumber": "00ЗК-0009",
"employeeINN": "291401744795",
"employeeGender": "M",
"employeeName": "Мишарин Андрей Васильевич",
"employeeLastName": "Мишарин",
"employeeFirstName": "Андрей",
"employeePatronymic": "Васильевич",
"employeePhone": "8-952-262-01-67",
"employee1CPostUID": "",
"employee1CSubdivisionUID": "",
"individual1CCode": "00-0000006",
"organizationUID": "96c4b46b-2848-11e9-8111-00155d630402",
"organizationINN": "7810950525",
"birthday": "1988-01-08",
"dateReceived": "",
"dismissalDate": "",
"inflection": {
"nominative": "Мишарин Андрей Васильевич",
"genitive": "Мишарина Андрея Васильевича",
"dative": "Мишарину Андрею Васильевичу",
"accusative": "Мишарина Андрея Васильевича",
"ablative": "Мишариным Андреем Васильевичем",
"prepositional": "Мишарине Андрее Васильевиче"
}
},
{
"employeeUID": "75491d0f-23c2-11e9-8111-00155d630402",
"personnelNumber": "0000-00009",
"employeeINN": "291401744795",
"employeeGender": "M",
"employeeName": "Мишарин Андрей Васильевич",
"employeeLastName": "Мишарин",
"employeeFirstName": "Андрей",
"employeePatronymic": "Васильевич",
"employeePhone": "8-952-262-01-67",
"employee1CPostUID": "db46bb1a-23a8-11e9-8111-00155d630402",
"employee1CSubdivisionUID": "b560007b-59fb-11e9-811b-00155d630402",
"individual1CCode": "00-0000006",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806",
"birthday": "1988-01-08",
"dateReceived": "2018-06-01",
"dismissalDate": "",
"inflection": {
"nominative": "Мишарин Андрей Васильевич",
"genitive": "Мишарина Андрея Васильевича",
"dative": "Мишарину Андрею Васильевичу",
"accusative": "Мишарина Андрея Васильевича",
"ablative": "Мишариным Андреем Васильевичем",
"prepositional": "Мишарине Андрее Васильевиче"
}
},
{
"employeeUID": "73a4bcc0-a90c-11ed-8179-00155d630402",
"personnelNumber": "00ЗК-0012",
"employeeINN": "551405972298",
"employeeGender": "M",
"employeeName": "Моренец Денис Сергеевич",
"employeeLastName": "Моренец",
"employeeFirstName": "Денис",
"employeePatronymic": "Сергеевич",
"employeePhone": "+7-951-654-62-03",
"employee1CPostUID": "",
"employee1CSubdivisionUID": "",
"individual1CCode": "ЗК-0000013",
"organizationUID": "96c4b46b-2848-11e9-8111-00155d630402",
"organizationINN": "7810950525",
"birthday": "1990-09-04",
"dateReceived": "",
"dismissalDate": "",
"inflection": {
"nominative": "Моренец Денис Сергеевич",
"genitive": "Моренеца Дениса Сергеевича",
"dative": "Моренецу Денису Сергеевичу",
"accusative": "Моренеца Дениса Сергеевича",
"ablative": "Моренецем Денисом Сергеевичем",
"prepositional": "Моренеце Денисе Сергеевиче"
}
},
{
"employeeUID": "c7652566-6d0c-11ea-8141-00155d630402",
"personnelNumber": "00ЗК-0005",
"employeeINN": "292202057006",
"employeeGender": "M",
"employeeName": "Павлов Михаил Викторович",
"employeeLastName": "Павлов",
"employeeFirstName": "Михаил",
"employeePatronymic": "Викторович",
"employeePhone": "+7921 898 41 46",
"employee1CPostUID": "",
"employee1CSubdivisionUID": "",
"individual1CCode": "00-0000003",
"organizationUID": "96c4b46b-2848-11e9-8111-00155d630402",
"organizationINN": "7810950525",
"birthday": "1984-12-19",
"dateReceived": "",
"dismissalDate": "",
"inflection": {
"nominative": "Павлов Михаил Викторович",
"genitive": "Павлова Михаила Викторовича",
"dative": "Павлову Михаилу Викторовичу",
"accusative": "Павлова Михаила Викторовича",
"ablative": "Павловым Михаилом Викторовичем",
"prepositional": "Павлове Михаиле Викторовиче"
}
},
{
"employeeUID": "75491d21-23c2-11e9-8111-00155d630402",
"personnelNumber": "0000-00015",
"employeeINN": "782570864253",
"employeeGender": "M",
"employeeName": "Рассчетов Александр Александрович",
"employeeLastName": "Рассчетов",
"employeeFirstName": "Александр",
"employeePatronymic": "Александрович",
"employeePhone": "8-962-709-43-12",
"employee1CPostUID": "db46bb1b-23a8-11e9-8111-00155d630402",
"employee1CSubdivisionUID": "b5600078-59fb-11e9-811b-00155d630402",
"individual1CCode": "00-0000012",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806",
"birthday": "1976-05-30",
"dateReceived": "2018-10-16",
"dismissalDate": "2022-08-09",
"inflection": {
"nominative": "Рассчетов Александр Александрович",
"genitive": "Рассчетова Александра Александровича",
"dative": "Рассчетову Александру Александровичу",
"accusative": "Рассчетова Александра Александровича",
"ablative": "Рассчетовым Александром Александровичем",
"prepositional": "Рассчетове Александре Александровиче"
}
},
{
"employeeUID": "d4e87948-285b-11e9-8111-00155d630402",
"personnelNumber": "00ЗК-0003",
"employeeINN": "470414889319",
"employeeGender": "M",
"employeeName": "Русин Николай Олегович",
"employeeLastName": "Русин",
"employeeFirstName": "Николай",
"employeePatronymic": "Олегович",
"employeePhone": "8 (921) 641-89-97",
"employee1CPostUID": "db46bb1a-23a8-11e9-8111-00155d630402",
"employee1CSubdivisionUID": "96c4b484-2848-11e9-8111-00155d630402",
"individual1CCode": "00-0000027",
"organizationUID": "96c4b46b-2848-11e9-8111-00155d630402",
"organizationINN": "7810950525",
"birthday": "1985-12-19",
"dateReceived": "2016-11-28",
"dismissalDate": "",
"inflection": {
"nominative": "Русин Николай Олегович",
"genitive": "Русина Николая Олеговича",
"dative": "Русину Николаю Олеговичу",
"accusative": "Русина Николая Олеговича",
"ablative": "Русиным Николаем Олеговичем",
"prepositional": "Русине Николае Олеговиче"
}
},
{
"employeeUID": "bc234918-5b28-11eb-814e-00155d630402",
"personnelNumber": "00ЗК-00039",
"employeeINN": "781696633774",
"employeeGender": "M",
"employeeName": "Савченко Анатолий Андреевич",
"employeeLastName": "Савченко",
"employeeFirstName": "Анатолий",
"employeePatronymic": "Андреевич",
"employeePhone": "+79213886510",
"employee1CPostUID": "5ada13de-bcd2-11e9-8130-00155d630402",
"employee1CSubdivisionUID": "b5600078-59fb-11e9-811b-00155d630402",
"individual1CCode": "ЗК-0000009",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806",
"birthday": "1988-06-06",
"dateReceived": "2021-01-27",
"dismissalDate": "",
"inflection": {
"nominative": "Савченко Анатолий Андреевич",
"genitive": "Савченко Анатолия Андреевича",
"dative": "Савченко Анатолию Андреевичу",
"accusative": "Савченко Анатолия Андреевича",
"ablative": "Савченко Анатолием Андреевичем",
"prepositional": "Савченко Анатолии Андреевиче"
}
},
{
"employeeUID": "3f770c35-95da-11eb-8152-00155d630402",
"personnelNumber": "00ЗК-00041",
"employeeINN": "781696633774",
"employeeGender": "M",
"employeeName": "Савченко Анатолий Андреевич",
"employeeLastName": "Савченко",
"employeeFirstName": "Анатолий",
"employeePatronymic": "Андреевич",
"employeePhone": "+79213886510",
"employee1CPostUID": "109705ce-836d-11e9-8123-00155d630402",
"employee1CSubdivisionUID": "109705c9-836d-11e9-8123-00155d630402",
"individual1CCode": "ЗК-0000009",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806",
"birthday": "1988-06-06",
"dateReceived": "2021-04-08",
"dismissalDate": "",
"inflection": {
"nominative": "Савченко Анатолий Андреевич",
"genitive": "Савченко Анатолия Андреевича",
"dative": "Савченко Анатолию Андреевичу",
"accusative": "Савченко Анатолия Андреевича",
"ablative": "Савченко Анатолием Андреевичем",
"prepositional": "Савченко Анатолии Андреевиче"
}
},
{
"employeeUID": "d4e87945-285b-11e9-8111-00155d630402",
"personnelNumber": "0000-0002",
"employeeINN": "781300572473",
"employeeGender": "M",
"employeeName": "Таранов Андрей Александрович",
"employeeLastName": "Таранов",
"employeeFirstName": "Андрей",
"employeePatronymic": "Александрович",
"employeePhone": "8 (911) 814-26-13",
"employee1CPostUID": "db46bb18-23a8-11e9-8111-00155d630402",
"employee1CSubdivisionUID": "96c4b485-2848-11e9-8111-00155d630402",
"individual1CCode": "00-0000026",
"organizationUID": "96c4b46b-2848-11e9-8111-00155d630402",
"organizationINN": "7810950525",
"birthday": "1985-05-11",
"dateReceived": "2016-09-07",
"dismissalDate": "",
"inflection": {
"nominative": "Таранов Андрей Александрович",
"genitive": "Таранова Андрея Александровича",
"dative": "Таранову Андрею Александровичу",
"accusative": "Таранова Андрея Александровича",
"ablative": "Тарановым Андреем Александровичем",
"prepositional": "Таранове Андрее Александровиче"
}
},
{
"employeeUID": "f56c978e-9a66-11ea-8141-00155d630402",
"personnelNumber": "00ЗК-00035",
"employeeINN": "552300470366",
"employeeGender": "F",
"employeeName": "Федорова Надежда Анатольевна",
"employeeLastName": "Федорова",
"employeeFirstName": "Надежда",
"employeePatronymic": "Анатольевна",
"employeePhone": "+7 (968) 1018095",
"employee1CPostUID": "c9497085-f2c1-11ec-8173-00155d630402",
"employee1CSubdivisionUID": "db46bb1f-23a8-11e9-8111-00155d630402",
"individual1CCode": "ЗК-0000003",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806",
"birthday": "1976-02-20",
"dateReceived": "2020-05-18",
"dismissalDate": "",
"inflection": {
"nominative": "Федорова Надежда Анатольевна",
"genitive": "Федоровой Надежды Анатольевны",
"dative": "Федоровой Надежде Анатольевне",
"accusative": "Федорову Надежду Анатольевну",
"ablative": "Федоровой Надеждой Анатольевной",
"prepositional": "Федоровой Надежде Анатольевне"
}
},
{
"employeeUID": "9c429155-5e40-11ec-8165-00155d630402",
"personnelNumber": "00ЗК-00042",
"employeeINN": "550514700510",
"employeeGender": "F",
"employeeName": "Шевченко Ольга Валентиновна",
"employeeLastName": "Шевченко",
"employeeFirstName": "Ольга",
"employeePatronymic": "Валентиновна",
"employeePhone": "+79620306773",
"employee1CPostUID": "84923926-5151-11ed-8179-00155d630402",
"employee1CSubdivisionUID": "db46bb1f-23a8-11e9-8111-00155d630402",
"individual1CCode": "ЗК-0000011",
"organizationUID": "db46bb0f-23a8-11e9-8111-00155d630402",
"organizationINN": "7842528806",
"birthday": "1981-08-16",
"dateReceived": "2021-12-20",
"dismissalDate": "",
"inflection": {
"nominative": "Шевченко Ольга Валентиновна",
"genitive": "Шевченко Ольги Валентиновны",
"dative": "Шевченко Ольге Валентиновне",
"accusative": "Шевченко Ольгу Валентиновну",
"ablative": "Шевченко Ольгой Валентиновной",
"prepositional": "Шевченко Ольге Валентиновне"
}
},
{
"employeeUID": "03bb056b-5432-11ed-8179-00155d630402",
"personnelNumber": "00ЗК-0011",
"employeeINN": "550514700510",
"employeeGender": "F",
"employeeName": "Шевченко Ольга Валентиновна",
"employeeLastName": "Шевченко",
"employeeFirstName": "Ольга",
"employeePatronymic": "Валентиновна",
"employeePhone": "+79620306773",
"employee1CPostUID": "84923926-5151-11ed-8179-00155d630402",
"employee1CSubdivisionUID": "96c4b485-2848-11e9-8111-00155d630402",
"individual1CCode": "ЗК-0000011",
"organizationUID": "96c4b46b-2848-11e9-8111-00155d630402",
"organizationINN": "7810950525",
"birthday": "1981-08-16",
"dateReceived": "2022-10-03",
"dismissalDate": "",
"inflection": {
"nominative": "Шевченко Ольга Валентиновна",
"genitive": "Шевченко Ольги Валентиновны",
"dative": "Шевченко Ольге Валентиновне",
"accusative": "Шевченко Ольгу Валентиновну",
"ablative": "Шевченко Ольгой Валентиновной",
"prepositional": "Шевченко Ольге Валентиновне"
}
}
]
}', false);

        foreach ($employees->data as $employee) {
            $company = Company::where('company_1c_uid', $employee->organizationUID)->get()->first();
            $employeePost = Employees1cPost::where('post_1c_uid', '=', $employee->employee1CPostUID)->get()->first();
            $employeeSubdivision = Employees1cSubdivision::where('subdivision_1c_uid', '=', $employee->employee1CSubdivisionUID)->get()->first();

            $userStatus = 1;
            if (!empty($employee->dismissalDate) && Carbon::parse($employee->dismissalDate) < Carbon::now()->addDay())
            {
                $userStatus = 0;
            }

            $formattedBirthday = Carbon::parse($employee->birthday)->format('d.m.Y');
            $formattedPhone = preg_replace("/[^0-9]/", '', $employee->employeePhone);
            if (substr($formattedPhone, 0, 1) == 8) {
                $formattedPhone = substr_replace($formattedPhone, '7', 0, 1);
            }

            if (isset($company)) {
                $user = User::withoutGlobalScopes()
                    ->where('first_name', '=', trim($employee->employeeFirstName))
                    ->where('last_name', '=', trim($employee->employeeLastName))
                    ->where('patronymic', '=', trim($employee->employeePatronymic))
                    ->where('birthday', '=', trim($formattedBirthday))
                    ->get()
                    ->first();

                // Need to use that after first sync
                /*$user = User::withoutGlobalScopes()
                    ->where('inn', '=', trim($employee->employeeINN))
                    ->first()
                    ->get();*/

                    if (isset($user))
                    {
                        //if ($user->status != 0) { Think about that condition when employee has been already dismissed
                            DB::statement("update users set " .
                                "first_name = '" . trim($employee->employeeFirstName) . "', " .
                                "last_name = '" . trim($employee->employeeLastName) . "', " .
                                "patronymic = '" . trim($employee->employeePatronymic) . "', " .
                                "person_phone = '" . $formattedPhone . "', " .
                                "inn = '" . trim($employee->employeeINN) . "', " .
                                "gender = '" . trim($employee->employeeGender) . "', " .
                                "person_phone = '" . $formattedPhone . "', " .
                                "company = " . $company->id . ", " .
                                "updated_at = NOW(), " .
                                "status = " . $userStatus . " " .
                                "where id = '" . $user->id . "'");
                            Log::channel('stderr')->info('[info] Обновлен пользователь: ' . $employee->employeeLastName . ' ' . $employee->employeeFirstName . ' ' . trim($employee->employeePatronymic));
                        //}
                    } else {
                        DB::statement('insert into users (first_name,' .
                                                                'last_name,' .
                                                                'patronymic,' .
                                                                'inn,' .
                                                                'gender,' .
                                                                'birthday,' .
                                                                'person_phone,' .
                                                                'company,' .
                                                                'created_at,' .
                                                                'updated_at,' .
                                                                'status,' .
                                                                'is_su) ' .
                        'values (' .
                            "'" . trim($employee->employeeFirstName) . "'," .
                            "'" . trim($employee->employeeLastName) . "'," .
                            "'" . trim($employee->employeePatronymic) . "'," .
                            "'" . trim($employee->employeeINN) . "'," .
                            "'" . trim($employee->employeeGender) . "'," .
                            "'" . $formattedBirthday . "'," .
                            "'" . $formattedPhone . "'," .
                                  $company->id . "," .
                                  'NOW()' . "," .
                                  'NOW()' . "," .
                                  '0' . "," .
                                  '0' .
                        ')');

                        Log::channel('stderr')->info('[info] Добавлен новый пользователь: ' . $employee->employeeLastName . ' ' . $employee->employeeFirstName . ' ' . trim($employee->employeePatronymic));

                        /*Telegram::sendMessage([
                            'chat_id' =>  config('app.env') == 'production' ? '-1001505547789' : '-1001558926749',
                            'text' => "[info] Добавлен новый пользователь: " . $employee->employeeLastName . ' ' . $employee->employeeFirstName . ' ' . trim($employee->employeePatronymic)
                        ]);*/
                    }

                $user = User::withoutGlobalScopes()
                    ->where('inn', '=', trim($employee->employeeINN))
                    ->get()
                    ->first();

                Employee::updateOrCreate(
                    [
                        'employee_1c_uid' => $employee->employeeUID,
                    ],
                    [
                        'user_id' => $user->id,
                        'employee_1c_name' => $employee->employeeName,
                        'personnel_number' => $employee->personnelNumber,
                        'employee_1c_post_id' => $employeePost->id,
                        'employee_1c_subdivision_id' => $employeeSubdivision->id,
                        'company_id' => $company->id,
                        'employment_date' => $employee->dateReceived,
                        'dismissal_date' => $employee->dismissalDate,
                        'report_group_id' => null
                    ]
                );
            }
        }
    }
}
