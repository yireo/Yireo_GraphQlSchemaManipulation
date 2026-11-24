<?php

declare(strict_types=1);

namespace Yireo\GraphQlSchemaManipulation\Test\Integration\Plugin;

use Magento\Framework\Config\DataInterface;
use Magento\Framework\GraphQl\Config;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader;
use Magento\Framework\ObjectManager\Config\Reader\Dom as DiConfigReader;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use Yireo\GraphQlSchemaManipulation\Plugin\ManipulateSchemaConfig;
use Yireo\GraphQlSchemaManipulation\Schema\SchemaConfigEditor;
use Yireo\GraphQlSchemaManipulation\Schema\SchemaConfigManipulatorInterface;

/**
 * @magentoAppArea graphql
 * @magentoAppIsolation enabled
 */
class ManipulateSchemaConfigTest extends TestCase
{
    protected function tearDown(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        if ($objectManager instanceof ObjectManager) {
            $objectManager->removeSharedInstance(ManipulateSchemaConfig::class);
        }
    }

    public function testPluginIsRegisteredGlobally(): void
    {
        $diConfig = Bootstrap::getObjectManager()->get(DiConfigReader::class)->read('global');
        $plugins = $diConfig[GraphQlReader::class]['plugins'] ?? [];

        $this->assertArrayHasKey('Yireo_GraphQlSchemaManipulation::manipulateSchemaConfig', $plugins);
        $this->assertSame(
            ManipulateSchemaConfig::class,
            $plugins['Yireo_GraphQlSchemaManipulation::manipulateSchemaConfig']['instance']
        );
    }

    public function testFieldsAreRemovedFromTypesAndInterfaces(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $editor = $objectManager->get(SchemaConfigEditor::class);
        $manipulator = new class($editor) implements SchemaConfigManipulatorInterface {
            public function __construct(
                private readonly SchemaConfigEditor $editor
            ) {
            }

            public function manipulate(array $schemaConfig): array
            {
                $schemaConfig = $this->editor->removeField($schemaConfig, 'Query', 'isEmailAvailable');
                return $this->editor->removeField($schemaConfig, 'ProductInterface', 'meta_title');
            }
        };

        $plugin = $objectManager->create(ManipulateSchemaConfig::class, [
            'schemaConfigManipulators' => ['test' => $manipulator],
        ]);
        $objectManager->addSharedInstance($plugin, ManipulateSchemaConfig::class);

        $schemaConfig = $objectManager->create(GraphQlReader::class)->read();

        $this->assertArrayNotHasKey('isEmailAvailable', $schemaConfig['Query']['fields']);
        $this->assertArrayHasKey('products', $schemaConfig['Query']['fields']);
        $this->assertArrayNotHasKey('meta_title', $schemaConfig['ProductInterface']['fields']);
        $this->assertArrayNotHasKey('meta_title', $schemaConfig['SimpleProduct']['fields']);
        $this->assertArrayHasKey('meta_title', $schemaConfig['ProductFilterInput']['fields']);

        $config = $objectManager->create(Config::class, ['data' => $this->createConfigData($schemaConfig)]);
        $this->assertArrayNotHasKey('isEmailAvailable', $config->getConfigElement('Query')->getFields());
        $this->assertArrayNotHasKey('meta_title', $config->getConfigElement('ProductInterface')->getFields());
        $this->assertArrayNotHasKey('meta_title', $config->getConfigElement('SimpleProduct')->getFields());
        $this->assertArrayHasKey('sku', $config->getConfigElement('SimpleProduct')->getFields());
    }

    /**
     * @param array<string, mixed> $schemaConfig
     * @return DataInterface
     */
    private function createConfigData(array $schemaConfig): DataInterface
    {
        return new class($schemaConfig) implements DataInterface {
            /**
             * @param array<string, mixed> $data
             */
            public function __construct(
                private array $data
            ) {
            }

            public function merge(array $config)
            {
                $this->data = array_replace_recursive($this->data, $config);
            }

            public function get($key, $default = null)
            {
                return $key === null ? $this->data : ($this->data[$key] ?? $default);
            }
        };
    }
}
