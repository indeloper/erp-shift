<?php

namespace Tests\Feature\Manual;

use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialCategoryAttribute;
use App\Models\Manual\ManualMaterialParameter;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ManualMaterialTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_replaces_comma_with_dot_only_in_numbers(): void
    {
        $this->actingAs(User::find(19));

        $attr = ManualMaterialCategoryAttribute::factory()->count(4)->create();
        $mat_name = $this->faker()->words(3, true);
        $request = [
            'name' => $mat_name,
            'description' => $this->faker()->sentence,
            'use_cost' => 0,
            'buy_cost' => 0,
            'attrs' => [
                $attr[0]->id => '000,3',
                $attr[1]->id => '193ы,3d',
                $attr[2]->id => 'Прочный, быстрый',
                $attr[3]->id => '13,3e-1',
            ],
        ];

        $this->post(route('building::materials::store', $attr[0]->category_id), $request);

        $params = ManualMaterial::latest()->first()->parameters;
        $this->assertEquals('0.3', $params[0]->value);
        $this->assertEquals('193ы,3d', $params[1]->value);
        $this->assertEquals('Прочный, быстрый', $params[2]->value);
        $this->assertEquals('1.33', $params[3]->value);

        $this->assertEquals(ManualMaterial::latest()->first()->name, $mat_name);
    }

    /** @test */
    public function it_replaces_comme_even_when_stored_in_code(): void
    {
        $param = ManualMaterialParameter::factory()->create(['value' => '123,4']);

        $this->assertEquals(123.4, $param->value);
    }
}
