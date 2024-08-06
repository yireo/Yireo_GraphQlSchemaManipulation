<?php

declare(strict_types=1);

namespace Yireo\GraphQlSchemaManipulation\Schema;

interface ManipulationInterface
{
    public function manipulateResolvedTypes(array $resolvedTypes): array;
}
