<?php

declare(strict_types=1);

namespace Yireo\GraphQlSchemaManipulation\Schema;

interface SchemaConfigManipulatorInterface
{
    /**
     * Manipulate the GraphQL schema config, as read by the GraphQL reader and before it is cached
     *
     * The config is keyed by type name (for instance `Query`, `ProductInterface` or `CustomerInput`). Each type is an
     * array with at least `name` and `type`, plus `fields` (types, inputs and interfaces), `implements` (types) or
     * `items` (enums). Use SchemaConfigEditor to remove fields, arguments and enum values safely.
     *
     * @param array<string, array<string, mixed>> $schemaConfig
     * @return array<string, array<string, mixed>>
     */
    public function manipulate(array $schemaConfig): array;
}
