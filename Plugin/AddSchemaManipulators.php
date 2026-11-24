<?php

declare(strict_types=1);

namespace Yireo\GraphQlSchemaManipulation\Plugin;

use InvalidArgumentException;
use LogicException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Config\Element\FieldsFactory;
use Yireo\GraphQlSchemaManipulation\Schema\ManipulationInterface;

/**
 * @deprecated Use a \Yireo\GraphQlSchemaManipulation\Schema\SchemaConfigManipulatorInterface instead
 * @see ManipulateSchemaConfig
 */
class AddSchemaManipulators
{
    /**
     * @var ManipulationInterface[]
     */
    private readonly array $schemaManipulators;

    /**
     * @param ManipulationInterface[] $schemaManipulators
     */
    public function __construct(
        array $schemaManipulators = []
    ) {
        foreach ($schemaManipulators as $schemaManipulator) {
            if (false === $schemaManipulator instanceof ManipulationInterface) {
                throw new InvalidArgumentException(sprintf(
                    'Schema manipulator "%s" should be of type "%s"',
                    get_debug_type($schemaManipulator),
                    ManipulationInterface::class
                ));
            }
        }

        $this->schemaManipulators = $schemaManipulators;
    }

    /**
     * @param FieldsFactory $subject
     * @param Field[] $config
     * @return Field[]
     */
    public function afterCreateFromConfigData(FieldsFactory $subject, array $config): array
    {
        return $this->manipulateFieldsConfig($config);
    }

    /**
     * @param Field[] $config
     * @return Field[]
     */
    public function manipulateFieldsConfig(array $config): array
    {
        foreach ($this->schemaManipulators as $schemaManipulator) {
            $config = $schemaManipulator->manipulateFieldsConfig($config);
            $this->validate($config, $schemaManipulator);
        }

        return $config;
    }

    /**
     * @param mixed[] $fields
     * @param ManipulationInterface $schemaManipulator
     * @return void
     */
    private function validate(array $fields, ManipulationInterface $schemaManipulator): void
    {
        foreach ($fields as $fieldName => $field) {
            if (!is_string($fieldName) || false === $field instanceof Field) {
                throw new LogicException(sprintf(
                    'Schema manipulator "%s" should return an array of "%s" keyed by field name',
                    get_class($schemaManipulator),
                    Field::class
                ));
            }
        }
    }
}
