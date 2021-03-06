<?php

namespace Spatie\Sheets\Tests;

use ArrayAccess;
use ReflectionClass;
use Spatie\Sheets\Sheet;
use PHPUnit\Framework\TestCase;

class SheetTest extends TestCase
{
    /** @test */
    public function it_can_create_a_sheet_with_attributes()
    {
        $attributes = [
            'foo' => 'bar',
            'hello' => 'world',
        ];
        $sheet = new Sheet($attributes);

        $reflection = (new ReflectionClass($sheet))->getProperty('attributes');
        $reflection->setAccessible(true);

        $this->assertEquals($attributes, $reflection->getValue($sheet));
    }

    /** @test */
    public function it_can_get_a_specific_attribute()
    {
        $sheet = new Sheet(['foo' => 'bar']);
        
        $this->assertEquals('bar', $sheet->foo);
    }

    /** @test */
    public function it_can_get_null_for_a_non_existing_attribute()
    {
        $sheet = new Sheet();
        
        $this->assertNull($sheet->unknown);
    }

    /** @test */
    public function it_can_set_a_specific_attribute()
    {
        $sheet = new Sheet();

        $sheet->foo = 'bar';

        $reflection = (new ReflectionClass($sheet))->getProperty('attributes');
        $reflection->setAccessible(true);

        $this->assertEquals(['foo' => 'bar'], $reflection->getValue($sheet));
    }

    /** @test */
    public function it_can_be_extended_with_accessor()
    {
        $child = new class extends Sheet {
            public function getFooAttribute($original)
            {
                return 'baz';
            }
        };

        $sheet = new $child(['foo' => 'bar']);

        $this->assertNotEquals('bar', $sheet->foo);
        $this->assertEquals('baz', $sheet->foo);
    }

    /** @test */
    public function it_implements_array_access()
    {
        $sheet = new Sheet(['foo' => 'bar']);

        $this->assertInstanceOf(ArrayAccess::class, $sheet);
        $this->assertEquals('bar', $sheet['foo']);
        $this->assertNull($sheet['unknown']);

        unset($sheet['foo']);
        $this->assertNull($sheet['foo']);

        $sheet['foo'] = 'baz';
        $this->assertEquals('baz', $sheet['foo']);
    }
}
