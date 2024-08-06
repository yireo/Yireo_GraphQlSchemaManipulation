<?php
declare(strict_types=1);

namespace Yireo\GraphQlSchemaManipulation\Schema;

use Magento\Framework\GraphQl\SchemaFactory as OriginalSchemaFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\GraphQl\Schema;

/**
 * This new factory preference is needed to manipulate the Schema class with DI
 */
class SchemaFactory extends OriginalSchemaFactory
{
    public function __construct(
        private ObjectManagerInterface $objectManager
    ){
    }
    
    public function create(array $config) : Schema
    {
        return $this->objectManager->create(Schema::class, ['config' => $config]);
    }
}