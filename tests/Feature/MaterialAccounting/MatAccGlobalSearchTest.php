<?php

namespace Tests\Feature\MaterialAccounting;

use App\Models\User;
use Tests\TestCase;

class MatAccGlobalSearchTest extends TestCase
{
    /** @test */
    public function it_can_return_complex_data_for_request(): void
    {
        $this->actingAs(User::first());
        $payload = [
            'search' => 'Л5',
        ];
        $route = route('building::mat_acc::report_card::get_search_values', $payload);

        $response = $this->post($route)->json('data');
        dd($response);
        [
            [
                'type_name' => 'Материал',
                'type_id' => 1,
                'value' => [
                    [
                        'result_id' => 7,
                        'result_name' => 'Шпунт',
                    ],
                ],
            ],
            [
                'type_name' => 'Объект',
                'type_id' => 2,
                'value' => [
                    [
                        'result_id' => 71,
                        'resuls_name' => 'База',
                    ],
                    [
                        'result_id' => 13,
                        'resuls_name' => 'Лиговский',
                    ],
                ],
            ],
            [
                'type_name' => 'Пользователь',
                'type_id' => 3,
                'value' => [
                    [
                        'result_id' => 13,
                        'result_name' => 'Самсонов',
                    ],
                ],
            ],
            [
                'type_name' => 'Эталон',
                'type_id' => '4',
                'value' => [
                    [
                        'result_id' => 34,
                        'result_name' => 'Балка Ш2',
                        'attr_id' => [
                            188 => 'Длина',
                        ],
                    ],
                ],
            ],
        ];
    }
}
