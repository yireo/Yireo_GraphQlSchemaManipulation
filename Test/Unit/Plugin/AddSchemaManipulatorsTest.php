<?php

declare(strict_types=1);

namespace Yireo\GraphQlSchemaManipulation\Test\Unit\Plugin;

use InvalidArgumentException;
use LogicException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Config\Element\FieldsFactory;
use PHPUnit\Framework\TestCase;
use stdClass;
use Yireo\GraphQlSchemaManipulation\Plugin\AddSchemaManipulators;
use Yireo\GraphQlSchemaManipulation\Schema\ManipulationInterface;

class AddSchemaManipulatorsTest extends TestCase
{
    public function testFieldsAreRemoved(): void
    {
        $fields = ['keep' => $this->createField('keep'), 'remove' => $this->createField('remove')];
        $plugin = new AddSchemaManipulators([$this->createManipulator(function (array $config): array {
            unset($config['remove']);
            return $config;
        })]);

        $result = $plugin->afterCreateFromConfigData($this->createMock(FieldsFactory::class), $fields);
        $this->assertSame(['keep' => $fields['keep']], $result);
    }

    public function testInvalidManipulatorTypeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('stdClass');
        new AddSchemaManipulators([new stdClass()]);
    }

    public function testInvalidManipulatorOutputIsRejected(): void
    {
        $plugin = new AddSchemaManipulators([
            $this->createManipulator(fn (array $config): array => ['keep' => ['name' => 'keep']]),
        ]);

        $this->expectException(LogicException::class);
        $plugin->afterCreateFromConfigData(
            $this->createMock(FieldsFactory::class),
            ['keep' => $this->createField('keep')]
        );
    }

    private function createField(string $name): Field
    {
        return new Field($name, 'String', false, false);
    }

    private function createManipulator(callable $callback): ManipulationInterface
    {
        return new class($callback) implements ManipulationInterface {
            /**
             * @var callable
             */
            private $callback;

            public function __construct(callable $callback)
            {
                $this->callback = $callback;
            }

            public function manipulateFieldsConfig(array $config): array
            {
                return ($this->callback)($config);
            }
        };
    }
}
