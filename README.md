# Yireo GraphQlSchemaManipulation

**Magento 2 module to manipulate the GraphQL schema before it is being used. This can be useful when trying to remove endpoints that are added by monolithic extensions and perhaps other use-cases.**

### Installation
```bash
composer require yireo/magento2-graph-ql-schema-manipulation
bin/magento module:enable Yireo_GraphQlSchemaManipulation
```

### Usage
To manipulate the schema yourself, you will need to create a class similar to the following:
```php
class ExampleSchemaManipulator implements \Yireo\GraphQlSchemaManipulation\Schema\ManipulationInterface
{
    public function manipulateResolvedTypes(array $resolvedTypes): array
    {
        foreach ($resolvedTypes as $resolvedTypeIndex => $type) {
            if (stristr($type->name(), 'Foobar')) {
                unset($resolvedTypes[$resolvedTypeIndex]);
            }
        }
        
        return $resolvedTypes;
    }
}
```

Next, create a `di.xml` file that adds this class to this module:
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Yireo\GraphQlSchemaManipulation\Plugin\AddSchemaManipulators">
        <arguments>
            <argument name="schemaManipulators" xsi:type="array">
                <item name="example" xsi:type="object">ExampleSchemaManipulator</item>
            </argument>
        </arguments>
    </type>
</config>
```