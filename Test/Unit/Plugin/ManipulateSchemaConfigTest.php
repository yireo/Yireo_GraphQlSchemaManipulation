<?php

declare(strict_types=1);

namespace Yireo\GraphQlSchemaManipulation\Test\Unit\Plugin;

use InvalidArgumentException;
use LogicException;
use Magento\Framework\GraphQlSchemaStitching\GraphQlReader;
use PHPUnit\Framework\TestCase;
use stdClass;
use Yireo\GraphQlSchemaManipulation\Plugin\ManipulateSchemaConfig;
use Yireo\GraphQlSchemaManipulation\Schema\SchemaConfigManipulatorInterface;

class ManipulateSchemaConfigTest extends TestCase
{
    public function testWithoutManipulatorsTheConfigIsUnchanged(): void
    {
        $config = $this->getSchemaConfig();
        $plugin = new ManipulateSchemaConfig();
        $this->assertSame($config, $plugin->afterRead($this->createMock(GraphQlReader::class), $config));
    }

    public function testManipulatorsAreAppliedInOrder(): void
    {
        $first = $this->createManipulator(function (array $config): array {
            unset($config['Query']['fields']['isEmailAvailable']);
            $config['Query']['description'] = 'first';
            return $config;
        });
        $second = $this->createManipulator(function (array $config): array {
            $config['Query']['description'] .= ',second';
            return $config;
        });

        $plugin = new ManipulateSchemaConfig(['first' => $first, 'second' => $second]);
        $result = $plugin->afterRead($this->createMock(GraphQlReader::class), $this->getSchemaConfig());

        $this->assertArrayNotHasKey('isEmailAvailable', $result['Query']['fields']);
        $this->assertArrayHasKey('products', $result['Query']['fields']);
        $this->assertSame('first,second', $result['Query']['description']);
    }

    public function testInvalidManipulatorTypeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('stdClass');
        new ManipulateSchemaConfig(['wrong' => new stdClass()]);
    }

    /**
     * @dataProvider getInvalidResults
     * @param mixed[] $invalidResult
     */
    public function testInvalidManipulatorOutputIsRejected(array $invalidResult): void
    {
        $manipulator = $this->createManipulator(fn (array $config): array => $invalidResult);
        $plugin = new ManipulateSchemaConfig([$manipulator]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(get_class($manipulator));
        $plugin->afterRead($this->createMock(GraphQlReader::class), $this->getSchemaConfig());
    }

    /**
     * @return array<string, array<int, mixed[]>>
     */
    public static function getInvalidResults(): array
    {
        $field = ['name' => 'products', 'arguments' => []];

        return [
            'numeric type key' => [[0 => ['name' => 'Query', 'type' => 'graphql_type']]],
            'type is not an array' => [['Query' => null]],
            'type without name' => [['Query' => ['type' => 'graphql_type']]],
            'fields not an array' => [['Query' => ['name' => 'Query', 'type' => 'graphql_type', 'fields' => 'x']]],
            'numeric field key' => [['Query' => ['name' => 'Query', 'type' => 'graphql_type', 'fields' => [$field]]]],
            'field is null' => [['Query' => ['name' => 'Query', 'type' => 'graphql_type', 'fields' => ['a' => null]]]],
            'field without arguments' => [
                ['Query' => ['name' => 'Query', 'type' => 'graphql_type', 'fields' => ['a' => ['name' => 'a']]]],
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getSchemaConfig(): array
    {
        return [
            'Query' => [
                'name' => 'Query',
                'type' => 'graphql_type',
                'fields' => [
                    'isEmailAvailable' => ['name' => 'isEmailAvailable', 'type' => 'Boolean', 'arguments' => []],
                    'products' => ['name' => 'products', 'type' => 'Products', 'arguments' => []],
                ],
            ],
            'CurrencyEnum' => [
                'name' => 'CurrencyEnum',
                'type' => 'graphql_enum',
                'items' => ['EUR' => ['name' => 'eur', '_value' => 'EUR']],
            ],
        ];
    }

    private function createManipulator(callable $callback): SchemaConfigManipulatorInterface
    {
        return new class($callback) implements SchemaConfigManipulatorInterface {
            /**
             * @var callable
             */
            private $callback;

            public function __construct(callable $callback)
            {
                $this->callback = $callback;
            }

            public function manipulate(array $schemaConfig): array
            {
                return ($this->callback)($schemaConfig);
            }
        };
    }
}
