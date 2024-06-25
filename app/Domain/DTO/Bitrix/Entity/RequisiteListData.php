<?php

declare(strict_types=1);

namespace App\Domain\DTO\Bitrix\Entity;

use Illuminate\Support\Collection;

final class RequisiteListData
{

    /**
     * @var Collection|array<RequisiteItemData>
     */
    public Collection $result;

    public int $total;

    public function __construct(
        array $result,
        int $total
    ) {
        $this->result = collect();

        foreach ($result as $value) {
            $this->result->push(RequisiteItemData::make($value));
        }

        $this->total = $total;
    }

}