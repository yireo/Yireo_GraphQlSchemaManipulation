<?php

declare(strict_types=1);

namespace Yireo\GraphQlSchemaManipulation\Plugin;

use Magento\Framework\GraphQl\Config\ConfigElementInterface;
use Magento\Framework\GraphQl\Config\Element\InterfaceFactory;
use Magento\Framework\GraphQl\Config\Element\InterfaceType;

class AddInterfaceSchemaManipulators
{
    public function __construct(
        private AddSchemaManipulators $addSchemaManipulators
    ) {
    }

    public function afterCreateFromConfigData(
        InterfaceFactory $subject,
        ConfigElementInterface $result,
        array $data
    ): ConfigElementInterface {
        if (false === $result instanceof InterfaceType) {
            return $result;
        }

        $fields = $result->getFields();
        $manipulatedFields = $this->addSchemaManipulators->manipulateFieldsConfig($fields);
        if ($manipulatedFields === $fields) {
            return $result;
        }

        return $subject->create($data, $manipulatedFields);
    }
}
