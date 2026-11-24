<?php

declare(strict_types=1);

namespace Yireo\GraphQlSchemaManipulation\Plugin;

use InvalidArgumentException;
use LogicException;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader;
use Yireo\GraphQlSchemaManipulation\Schema\SchemaConfigManipulatorInterface;

class ManipulateSchemaConfig
{
    /**
     * @var SchemaConfigManipulatorInterface[]
     */
    private readonly array $schemaConfigManipulators;

    /**
     * @param SchemaConfigManipulatorInterface[] $schemaConfigManipulators
     */
    public function __construct(
        array $schemaConfigManipulators = []
    ) {
        foreach ($schemaConfigManipulators as $schemaConfigManipulator) {
            if (false === $schemaConfigManipulator instanceof SchemaConfigManipulatorInterface) {
                throw new InvalidArgumentException(sprintf(
                    'Schema config manipulator "%s" should be of type "%s"',
                    get_debug_type($schemaConfigManipulator),
                    SchemaConfigManipulatorInterface::class
                ));
            }
        }

        $this->schemaConfigManipulators = $schemaConfigManipulators;
    }

    /**
     * @param GraphQlReader $subject
     * @param array<string, array<string, mixed>> $result
     * @return array<string, array<string, mixed>>
     */
    public function afterRead(GraphQlReader $subject, array $result): array
    {
        foreach ($this->schemaConfigManipulators as $schemaConfigManipulator) {
            $result = $schemaConfigManipulator->manipulate($result);
            $this->validate($result, $schemaConfigManipulator);
        }

        return $result;
    }

    /**
     * @param mixed[] $schemaConfig
     * @param SchemaConfigManipulatorInterface $schemaConfigManipulator
     * @return void
     */
    private function validate(array $schemaConfig, SchemaConfigManipulatorInterface $schemaConfigManipulator): void
    {
        foreach ($schemaConfig as $typeName => $type) {
            if (!is_string($typeName) || !is_array($type) || !isset($type['name'], $type['type'])) {
                throw $this->createException($schemaConfigManipulator, 'type "' . $typeName . '" is invalid');
            }

            if (!isset($type['fields'])) {
                continue;
            }

            if (!is_array($type['fields'])) {
                throw $this->createException($schemaConfigManipulator, 'fields of "' . $typeName . '" are invalid');
            }

            foreach ($type['fields'] as $fieldName => $field) {
                if (!is_string($fieldName)
                    || !is_array($field)
                    || !isset($field['name'])
                    || !is_array($field['arguments'] ?? null)
                ) {
                    throw $this->createException(
                        $schemaConfigManipulator,
                        'field "' . $typeName . '.' . $fieldName . '" is invalid'
                    );
                }
            }
        }
    }

    private function createException(
        SchemaConfigManipulatorInterface $schemaConfigManipulator,
        string $reason
    ): LogicException {
        return new LogicException(sprintf(
            'Schema config manipulator "%s" returned an invalid schema config: %s',
            get_class($schemaConfigManipulator),
            $reason
        ));
    }
}
