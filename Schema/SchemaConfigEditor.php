<?php

declare(strict_types=1);

namespace Yireo\GraphQlSchemaManipulation\Schema;

class SchemaConfigEditor
{
    private const GRAPHQL_INTERFACE = 'graphql_interface';

    /**
     * Remove a field from a type; when the type is an interface, also remove it from all types implementing it
     *
     * @param array<string, array<string, mixed>> $schemaConfig
     * @return array<string, array<string, mixed>>
     */
    public function removeField(array $schemaConfig, string $typeName, string $fieldName): array
    {
        foreach ($this->getAffectedTypeNames($schemaConfig, $typeName) as $affectedTypeName) {
            unset($schemaConfig[$affectedTypeName]['fields'][$fieldName]);
        }

        return $schemaConfig;
    }

    /**
     * Remove an argument from a field; when the type is an interface, also remove it from all types implementing it
     *
     * @param array<string, array<string, mixed>> $schemaConfig
     * @return array<string, array<string, mixed>>
     */
    public function removeArgument(
        array $schemaConfig,
        string $typeName,
        string $fieldName,
        string $argumentName
    ): array {
        foreach ($this->getAffectedTypeNames($schemaConfig, $typeName) as $affectedTypeName) {
            if (isset($schemaConfig[$affectedTypeName]['fields'][$fieldName]['arguments'])) {
                unset($schemaConfig[$affectedTypeName]['fields'][$fieldName]['arguments'][$argumentName]);
            }
        }

        return $schemaConfig;
    }

    /**
     * @param array<string, array<string, mixed>> $schemaConfig
     * @return array<string, array<string, mixed>>
     */
    public function removeEnumValue(array $schemaConfig, string $enumName, string $value): array
    {
        if (isset($schemaConfig[$enumName]['items'])) {
            unset($schemaConfig[$enumName]['items'][$value]);
        }

        return $schemaConfig;
    }

    /**
     * @param array<string, array<string, mixed>> $schemaConfig
     * @return string[]
     */
    private function getAffectedTypeNames(array $schemaConfig, string $typeName): array
    {
        if (!isset($schemaConfig[$typeName]['fields'])) {
            return [];
        }

        $typeNames = [$typeName];
        if (($schemaConfig[$typeName]['type'] ?? '') !== self::GRAPHQL_INTERFACE) {
            return $typeNames;
        }

        foreach ($schemaConfig as $otherTypeName => $otherType) {
            if (isset($otherType['implements'][$typeName], $otherType['fields'])) {
                $typeNames[] = (string)$otherTypeName;
            }
        }

        return $typeNames;
    }
}
