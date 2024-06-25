<?php

declare(strict_types=1);

namespace App\Domain\DTO;

use Illuminate\Support\Arr;
use ReflectionClass;

class DTO
{

    public static function make(array $data): static
    {
        $class = new ReflectionClass(static::class);

        $parameters = $class->getConstructor()?->getParameters() ?? [];

        $instanceParams = Arr::only($data, array_intersect_key(
                Arr::pluck($parameters, 'name'),
                array_keys($data)
            )
        );

        return new static(...$instanceParams);
    }

    public function toArray(): array
    {
        $class = new ReflectionClass($this);

        $result = [];

        foreach ($class->getProperties() as $property) {
            $result[$property->getName()] = $this->{$property->getName()};
        }

        return $result;
    }

}