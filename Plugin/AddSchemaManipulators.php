<?php

declare(strict_types=1);

namespace Yireo\GraphQlSchemaManipulation\Plugin;

use Magento\Framework\GraphQl\Config\Element\FieldsFactory;
use Magento\Framework\GraphQl\Schema;
use Magento\Framework\GraphQl\Schema\SchemaGeneratorInterface;
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
