<?php

declare(strict_types=1);

namespace Yireo\GraphQlSchemaManipulation\Schema;

interface ManipulationInterface
{
    public function manipulateFieldsConfig(array $config): array;
}
