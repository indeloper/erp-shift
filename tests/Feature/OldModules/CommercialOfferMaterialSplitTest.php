<?php

namespace Tests\Feature\OldModules;

use Illuminate\Database\Eloquent\Model;
use App\Models\CommercialOffer\CommercialOffer;
use App\Models\Manual\ManualMaterial;
use App\Services\Commerce\SplitService;
use Tests\TestCase;

class CommercialOfferMaterialSplitTest extends TestCase
{
    /** @var CommercialOffer */
    private $commercial_offer;

    /** @var SplitService */
    private $split_service;

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createBasicSplit($count = null): Model
    {
        return $this->commercial_offer->mat_splits()->create([
            'count' => $count ?? $this->faker()->randomNumber(3),
            'unit' => 'т',
            'time' => null,
            'man_mat_id' => ManualMaterial::first()->id,
            'type' => 1,
            'price_per_one' => 0,
            'result_price' => 0,
            'material_type' => 'regular',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->commercial_offer = CommercialOffer::factory()->create();
        $this->split_service = new SplitService();
    }

    /** @test */
    public function it_converts_type_to_numeric(): void
    {
        $sale_type = $this->split_service->convertTypeToNumeric('sale');
        $given_type = $this->split_service->convertTypeToNumeric('given');

        $this->assertEquals(1, $sale_type);
        $this->assertEquals(5, $given_type);
    }

    /** @test */
    public function it_can_split_a_split(): void
    {
        $split = $this->createBasicSplit();

        $new_split = $this->split_service->splitMore($split, 10, 2);

        $this->split_service->splitMore($new_split, 10, 2);

        $this->assertEquals(3, $this->commercial_offer->mat_splits()->count());
    }

    /** @test */
    public function it_correctly_change_count(): void
    {
        $split = $this->createBasicSplit(45);

        $new_split = $this->split_service->splitMore($split, 10, 'rent', 12);

        $this->assertEquals(35, $split->count);
        $this->assertEquals(10, $new_split->count);
    }

    /** @test */
    public function it_creates_modification_split(): void
    {
        $split = $this->createBasicSplit(45);

        $new_split = $this->split_service->splitMore($split, 10, 2);

        $this->assertEquals(45, $split->count);
        $this->assertEquals(10, $new_split->count);
    }

    /** @test */
    public function it_joins_same_splits(): void
    {
        $split = $this->createBasicSplit(40);

        $new_split = $this->split_service->splitMore($split, 10, 'buyback');
        $another_one = $this->split_service->mergeRelatedSplitWithOldOne($split, 10, $new_split);

        $new_split->refresh();
        $this->assertEquals(2, $this->commercial_offer->mat_splits()->count());
        $this->assertEquals($new_split->id, $another_one->id);
        $this->assertEquals(20, $new_split->count);
    }

    /** @test */
    public function it_creates_modification_on_modification(): void
    {
        $split = $this->createBasicSplit(45);

        $new_split = $this->split_service->splitMore($split, 10, 'buyback');
        $another_one = $this->split_service->splitMore($split, 10, 'security');

        $this->assertEquals(45, $split->count);
        $this->assertEquals(10, $new_split->count);
        $this->assertEquals(10, $another_one->count);
    }

    /** @test */
    public function it_deletes_split_when_count_become_zero(): void
    {
        $split = $this->createBasicSplit(45);

        $new_split = $this->split_service->splitMore($split, 45, 'given');

        $this->assertEquals(1, $this->commercial_offer->mat_splits()->count());
    }

    /** @test */
    public function it_can_split_and_than_merge_back_everything(): void
    {
        $split = $this->createBasicSplit(100);
        $rent_split = $this->split_service->splitMore($split, 30, 'rent', '12');
        $given_split = $this->split_service->splitMore($split, 40, 'given');
        $buyback_one = $this->split_service->splitMore($given_split, 20, 'buyback');
        $security_split = $this->split_service->splitMore($rent_split, 20, 'security');

        $this->split_service->mergeRelatedSplitWithOldOne($given_split, 40, $split);
        $this->split_service->mergeRelatedSplitWithOldOne($rent_split, 30, $split);

        $this->assertEquals(1, $this->commercial_offer->mat_splits()->count());
        $this->assertEquals(100, $this->commercial_offer->mat_splits()->first()->count);

    }

    /** @test */
    public function it_can_fix_parent_child_relation(): void
    {
        //given split and it's child
        $split = $this->createBasicSplit();
        $child_split = $this->split_service->splitMore($split, 10, 2);
        //and another split with child
        $another_split = $this->createBasicSplit();
        $another_split->man_mat_id = 2;
        $another_split->save();
        $another_child = $this->split_service->splitMore($another_split, 10, 2);

        //remove relations
        $child_split->parent_id = null;
        $child_split->save();
        $another_child->parent_id = null;
        $another_child->save();

        //pass one pair and one child without parent
        $splits = collect([$split, $child_split, $another_child]);
        $splits = $this->split_service->fixParentChildRelations($splits);
        $this->assertNotNull($child_split->parent_id);
        $this->assertEquals(2, $splits->count());
    }
}
