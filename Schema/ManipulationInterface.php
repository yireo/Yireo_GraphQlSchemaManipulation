<?php

declare(strict_types=1);

namespace Yireo\GraphQlSchemaManipulation\Schema;

use Magento\Framework\GraphQl\Config\Element\Field;

/**
 * @deprecated This receives the fields of any type, input and interface without the type name
 * @see SchemaConfigManipulatorInterface
 */
interface ManipulationInterface
{
    /**
     * Manipulate the fields of a single type, input or interface (the type name is not available)
     *
     * @param Field[] $config Field objects keyed by field name
     * @return Field[]
     */
    public function manipulateFieldsConfig(array $config): array;
}
