<?php

declare(strict_types=1);

namespace Yireo\GraphQlSchemaManipulation\Plugin;

use GraphQL\Type\Schema;
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
    
    public function afterGetTypeMap(Schema $subject, array $resolvedTypes): array
    {
        foreach ($this->getSchemaManipulators() as $schemaManipulator) {
            $resolvedTypes = $schemaManipulator->manipulateResolvedTypes($resolvedTypes);
        }
        
        return $resolvedTypes;
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