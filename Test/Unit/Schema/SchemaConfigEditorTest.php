<?php

declare(strict_types=1);

namespace Yireo\GraphQlSchemaManipulation\Test\Unit\Schema;

use PHPUnit\Framework\TestCase;
use Yireo\GraphQlSchemaManipulation\Schema\SchemaConfigEditor;

class SchemaConfigEditorTest extends TestCase
{
    public function testRemoveFieldOnlyAffectsTheGivenType(): void
    {
        $result = (new SchemaConfigEditor())->removeField($this->getSchemaConfig(), 'Query', 'isEmailAvailable');

        $this->assertArrayNotHasKey('isEmailAvailable', $result['Query']['fields']);
        $this->assertArrayHasKey('products', $result['Query']['fields']);
        $this->assertArrayHasKey('isEmailAvailable', $result['CustomerInput']['fields']);
    }

    public function testRemoveFieldFromInterfaceAlsoRemovesItFromImplementingTypes(): void
    {
        $result = (new SchemaConfigEditor())->removeField($this->getSchemaConfig(), 'ProductInterface', 'secret');

        $this->assertArrayNotHasKey('secret', $result['ProductInterface']['fields']);
        $this->assertArrayNotHasKey('secret', $result['SimpleProduct']['fields']);
        $this->assertArrayHasKey('secret', $result['OtherType']['fields']);
        $this->assertArrayHasKey('sku', $result['SimpleProduct']['fields']);
    }

    public function testRemoveFieldFromTypeDoesNotTouchItsInterface(): void
    {
        $result = (new SchemaConfigEditor())->removeField($this->getSchemaConfig(), 'SimpleProduct', 'secret');

        $this->assertArrayNotHasKey('secret', $result['SimpleProduct']['fields']);
        $this->assertArrayHasKey('secret', $result['ProductInterface']['fields']);
    }

    public function testRemoveArgumentFromInterfaceAlsoRemovesItFromImplementingTypes(): void
    {
        $result = (new SchemaConfigEditor())->removeArgument(
            $this->getSchemaConfig(),
            'ProductInterface',
            'sku',
            'format'
        );

        $this->assertSame([], $result['ProductInterface']['fields']['sku']['arguments']);
        $this->assertSame([], $result['SimpleProduct']['fields']['sku']['arguments']);
    }

    public function testRemoveEnumValue(): void
    {
        $result = (new SchemaConfigEditor())->removeEnumValue($this->getSchemaConfig(), 'CurrencyEnum', 'EUR');

        $this->assertArrayNotHasKey('EUR', $result['CurrencyEnum']['items']);
        $this->assertArrayHasKey('USD', $result['CurrencyEnum']['items']);
    }

    public function testUnknownNamesAreIgnoredWithoutAddingKeys(): void
    {
        $editor = new SchemaConfigEditor();
        $config = $this->getSchemaConfig();

        $this->assertSame($config, $editor->removeField($config, 'Unknown', 'field'));
        $this->assertSame($config, $editor->removeField($config, 'CurrencyEnum', 'EUR'));
        $this->assertSame($config, $editor->removeArgument($config, 'Query', 'unknown', 'argument'));
        $this->assertSame($config, $editor->removeEnumValue($config, 'Query', 'products'));
        $this->assertSame($config, $editor->removeEnumValue($config, 'Unknown', 'value'));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getSchemaConfig(): array
    {
        $field = fn (string $name, array $arguments = []): array => [
            'name' => $name,
            'type' => 'String',
            'arguments' => $arguments,
        ];
        $skuArguments = ['format' => ['name' => 'format', 'type' => 'String']];

        return [
            'Query' => [
                'name' => 'Query',
                'type' => 'graphql_type',
                'fields' => ['isEmailAvailable' => $field('isEmailAvailable'), 'products' => $field('products')],
            ],
            'CustomerInput' => [
                'name' => 'CustomerInput',
                'type' => 'graphql_input',
                'fields' => ['isEmailAvailable' => $field('isEmailAvailable')],
            ],
            'ProductInterface' => [
                'name' => 'ProductInterface',
                'type' => 'graphql_interface',
                'fields' => ['sku' => $field('sku', $skuArguments), 'secret' => $field('secret')],
            ],
            'SimpleProduct' => [
                'name' => 'SimpleProduct',
                'type' => 'graphql_type',
                'implements' => ['ProductInterface' => ['interface' => 'ProductInterface', 'copyFields' => true]],
                'fields' => ['sku' => $field('sku', $skuArguments), 'secret' => $field('secret')],
            ],
            'OtherType' => [
                'name' => 'OtherType',
                'type' => 'graphql_type',
                'fields' => ['secret' => $field('secret')],
            ],
            'CurrencyEnum' => [
                'name' => 'CurrencyEnum',
                'type' => 'graphql_enum',
                'items' => ['EUR' => ['name' => 'eur'], 'USD' => ['name' => 'usd']],
            ],
        ];
    }
}
