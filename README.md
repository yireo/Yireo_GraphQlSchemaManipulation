# Yireo GraphQlSchemaManipulation

<!-- badges.specs.start -->
<div class="github-specs-badges">

![Magento version](https://img.shields.io/badge/Magento-2.4.6%20%7C%202.4.9-orange)
![PHP version](https://img.shields.io/badge/PHP-8.2%E2%80%938.5-777BB4)
![License](https://img.shields.io/badge/License-OSL--3.0-blue)
![Latest Version](https://img.shields.io/packagist/v/yireo/magento2-graph-ql-schema-manipulation)

</div>
<!-- badges.specs.end -->


**Magento 2 module to manipulate the GraphQL schema before it is being used. This can be useful when trying to remove endpoints that are added by monolithic extensions and perhaps other use-cases.**

### Installation
```bash
composer require yireo/magento2-graph-ql-schema-manipulation
bin/magento module:enable Yireo_GraphQlSchemaManipulation
```

### Usage
To manipulate the schema, create a class that implements `SchemaConfigManipulatorInterface`. It receives the full schema config (as read from all `schema.graphqls` files), keyed by type name. Use the `SchemaConfigEditor` to remove fields, arguments and enum values of a specific type:
```php
use Yireo\GraphQlSchemaManipulation\Schema\SchemaConfigEditor;
use Yireo\GraphQlSchemaManipulation\Schema\SchemaConfigManipulatorInterface;

class ExampleSchemaManipulator implements SchemaConfigManipulatorInterface
{
    public function __construct(
        private readonly SchemaConfigEditor $schemaConfigEditor
    ) {
    }

    public function manipulate(array $schemaConfig): array
    {
        $schemaConfig = $this->schemaConfigEditor->removeField($schemaConfig, 'Query', 'isEmailAvailable');
        $schemaConfig = $this->schemaConfigEditor->removeArgument($schemaConfig, 'Query', 'products', 'search');
        return $this->schemaConfigEditor->removeEnumValue($schemaConfig, 'CurrencyEnum', 'EUR');
    }
}
```

When a field or argument is removed from an interface (like `ProductInterface`), it is also removed from all types implementing that interface (like `SimpleProduct`), so the schema stays consistent. A field with the same name in another type or input is left alone.

Next, add this class to this module with a global `etc/di.xml` file (not `etc/graphql/di.xml`, because the schema config is cached once for all areas):
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Yireo\GraphQlSchemaManipulation\Plugin\ManipulateSchemaConfig">
        <arguments>
            <argument name="schemaConfigManipulators" xsi:type="array">
                <item name="example" xsi:type="object">ExampleSchemaManipulator</item>
            </argument>
        </arguments>
    </type>
</config>
```

The manipulated schema config is stored in the `config` cache. After changing a manipulator, run `bin/magento cache:clean config`. If a manipulator returns an invalid schema config, a `LogicException` naming the manipulator is thrown.

### Limitations
Removing a field from the GraphQL schema only hides it from GraphQL: queries for it fail validation and introspection no longer shows it. It does not disable the underlying feature. Resolver classes, REST endpoints (like `V1/customers/isEmailAvailable`) and controllers offering the same functionality are still available. Do not rely on this module as your only access control.

### Deprecated: `ManipulationInterface`
The older `ManipulationInterface` (added to `Plugin\AddSchemaManipulators` via its `schemaManipulators` argument) still works, but is deprecated. Its `manipulateFieldsConfig()` method receives the `Magento\Framework\GraphQl\Config\Element\Field` objects of every single type, input and interface, without the type name. Removing a field by name therefore removes it from every type and input that has a field with that name.

## Current status

<!-- badges.test.start -->
<div class="github-pipeline-badges">

![Static Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_GraphQlSchemaManipulation/static-tests.yml?label=static-tests)
![Unit Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_GraphQlSchemaManipulation/unit-tests.yml?label=unit-tests)
![Integration Tests](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_GraphQlSchemaManipulation/integration-tests.yml?label=integration-tests)
![Playwright](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_GraphQlSchemaManipulation/playwright.yml?label=playwright)
![DI Compilation](https://img.shields.io/github/actions/workflow/status/yireo/Yireo_GraphQlSchemaManipulation/compile.yml?label=compile)

</div>
<!-- badges.test.end -->
