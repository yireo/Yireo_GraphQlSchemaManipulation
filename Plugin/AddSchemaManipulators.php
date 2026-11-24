<?php

declare(strict_types=1);

namespace Yireo\GraphQlSchemaManipulation\Plugin;

use Magento\Framework\GraphQl\Config\Element\FieldsFactory;
use RuntimeException;
use Yireo\GraphQlSchemaManipulation\Schema\ManipulationInterface;

class AddSchemaManipulators
{
    /**
     * @param ManipulationInterface[] $schemaManipulators
     */
    public function __construct(
        private array $schemaManipulators = []
    )
    {
    }

    public function afterCreateFromConfigData(FieldsFactory $subject, array $config): array
    {
        return $this->manipulateFieldsConfig($config);
    }

    public function manipulateFieldsConfig(array $config): array
    {
        foreach ($this->getSchemaManipulators() as $schemaManipulator) {
            $config = $schemaManipulator->manipulateFieldsConfig($config);
        }

        return $config;
    }

    private function getSchemaManipulators(): array
    {
        foreach ($this->schemaManipulators as $schemaManipulator) {
            if (false === $schemaManipulator instanceof ManipulationInterface) {
                throw new RuntimeException('Class "'.get_class($schemaManipulator).'" should be of type "'.ManipulationInterface::class.'"');
            }
        }

        return $this->schemaManipulators;
    }
}
