<?php

declare(strict_types=1);

namespace Yireo\GraphQlSchemaManipulation\Test\Unit\Plugin;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Config\Element\InterfaceFactory;
use Magento\Framework\GraphQl\Config\Element\InterfaceType;
use PHPUnit\Framework\TestCase;
use Yireo\GraphQlSchemaManipulation\Plugin\AddInterfaceSchemaManipulators;
use Yireo\GraphQlSchemaManipulation\Plugin\AddSchemaManipulators;
use Yireo\GraphQlSchemaManipulation\Schema\ManipulationInterface;

class AddInterfaceSchemaManipulatorsTest extends TestCase
{
    public function testInterfaceIsRecreatedWithoutRemovedFields(): void
    {
        $fields = [
            'keep' => $this->createField('keep'),
            'remove' => $this->createField('remove'),
        ];
        $data = ['name' => 'ExampleInterface', 'typeResolver' => 'Example\\TypeResolver'];
        $original = new InterfaceType('ExampleInterface', 'Example\\TypeResolver', $fields, '');
        $recreated = new InterfaceType('ExampleInterface', 'Example\\TypeResolver', ['keep' => $fields['keep']], '');

        $subject = $this->createMock(InterfaceFactory::class);
        $subject->expects($this->once())
            ->method('create')
            ->with($data, ['keep' => $fields['keep']])
            ->willReturn($recreated);

        $plugin = new AddInterfaceSchemaManipulators(new AddSchemaManipulators([$this->createRemovingManipulator()]));
        $this->assertSame($recreated, $plugin->afterCreateFromConfigData($subject, $original, $data));
    }

    public function testInterfaceIsKeptWhenNothingIsRemoved(): void
    {
        $fields = ['keep' => $this->createField('keep')];
        $original = new InterfaceType('ExampleInterface', 'Example\\TypeResolver', $fields, '');

        $subject = $this->createMock(InterfaceFactory::class);
        $subject->expects($this->never())->method('create');

        $plugin = new AddInterfaceSchemaManipulators(new AddSchemaManipulators([$this->createRemovingManipulator()]));
        $this->assertSame($original, $plugin->afterCreateFromConfigData($subject, $original, []));
    }

    private function createField(string $name): Field
    {
        return new Field($name, 'String', false, false);
    }

    private function createRemovingManipulator(): ManipulationInterface
    {
        return new class implements ManipulationInterface {
            public function manipulateFieldsConfig(array $config): array
            {
                unset($config['remove']);
                return $config;
            }
        };
    }
}
