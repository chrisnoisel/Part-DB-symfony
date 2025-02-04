<?php
/**
 * This file is part of Part-DB (https://github.com/Part-DB/Part-DB-symfony).
 *
 * Copyright (C) 2019 Jan Böhmer (https://github.com/jbtronics)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
 */

namespace App\Tests\Entity\UserSystem;

use App\Entity\UserSystem\PermissionsEmbed;
use Doctrine\ORM\Mapping\Embedded;
use PHPUnit\Framework\TestCase;

class PermissionsEmbedTest extends TestCase
{

    public function testGetPermissionValue()
    {
        $embed = new PermissionsEmbed();
        //For newly created embedded, all things should be set to inherit => null
        //Test both normal name and constants

        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::PARTS, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::CONFIG, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::ATTACHMENT_TYPES, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::CATEGORIES, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::DATABASE, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::DEVICE_PARTS, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::DEVICES, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::FOOTRPINTS, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::GROUPS, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::DATABASE, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::LABELS, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::MANUFACTURERS, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::PARTS_ATTACHMENTS, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::PARTS_COMMENT, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::PARTS_DESCRIPTION, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::PARTS_FOOTPRINT, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::PARTS_MANUFACTURER, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::PARTS_MINAMOUNT, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::PARTS_NAME, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::PARTS_ORDER, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::PARTS_ORDERDETAILS, 0));
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::USERS, 0));

        //Set a value for testing to the part property
        $reflection = new \ReflectionClass($embed);
        $property = $reflection->getProperty('parts');
        $property->setAccessible(true);

        $property->setValue($embed, 0b11011000); // 11 01 10 00

        //Test if function is working correctly
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::PARTS, 0));
        $this->assertFalse($embed->getPermissionValue(PermissionsEmbed::PARTS, 2));
        $this->assertTrue($embed->getPermissionValue(PermissionsEmbed::PARTS, 4));
        // 11 is reserved, but it should be also treat as INHERIT.
        $this->assertNull($embed->getPermissionValue(PermissionsEmbed::PARTS, 6));
    }

    public function testGetBitValue()
    {
        $embed = new PermissionsEmbed();

        //Set a value for testing to the part property
        $reflection = new \ReflectionClass($embed);
        $property = $reflection->getProperty('parts');
        $property->setAccessible(true);

        $property->setValue($embed, 0b11011000); // 11 01 10 00

        //Test if function is working correctly
        $this->assertEquals(PermissionsEmbed::INHERIT, $embed->getBitValue(PermissionsEmbed::PARTS, 0));
        $this->assertEquals(PermissionsEmbed::DISALLOW, $embed->getBitValue(PermissionsEmbed::PARTS, 2));
        $this->assertEquals(PermissionsEmbed::ALLOW, $embed->getBitValue(PermissionsEmbed::PARTS, 4));
        // 11 is reserved, but it should be also treat as INHERIT.
        $this->assertEquals(0b11, $embed->getBitValue(PermissionsEmbed::PARTS, 6));
    }

    public function testInvalidPermissionName()
    {
        $embed = new PermissionsEmbed();
        //When encoutering an unknown permission name the class must throw an exception
        $this->expectException(\InvalidArgumentException::class);
        $embed->getPermissionValue('invalid', 0);
    }

    public function testInvalidBit1()
    {
        $embed = new PermissionsEmbed();
        //When encoutering an negative bit the class must throw an exception
        $this->expectException(\InvalidArgumentException::class);
        $embed->getPermissionValue('parts', -1);
    }

    public function testInvalidBit2()
    {
        $embed = new PermissionsEmbed();
        //When encoutering an odd bit number it must throw an error.
        $this->expectException(\InvalidArgumentException::class);
        $embed->getPermissionValue('parts', 1);
    }

    public function testInvalidBit3()
    {
        $embed = new PermissionsEmbed();
        //When encoutering an too high bit number it must throw an error.
        $this->expectException(\InvalidArgumentException::class);
        $embed->getPermissionValue('parts', 32);
    }

    public function getStatesBINARY()
    {
        return [
            'ALLOW' => [PermissionsEmbed::ALLOW],
            'DISALLOW' => [PermissionsEmbed::DISALLOW],
            'INHERIT' => [PermissionsEmbed::INHERIT],
            '0b11' => [0b11],
        ];
    }

    public function getStatesBOOL()
    {
        return [
            'ALLOW' => [true],
            'DISALLOW' => [false],
            'INHERIT' => [null],
            '0b11' => [null],
        ];
    }

    /**
     * @dataProvider getStatesBINARY
     */
    public function testsetBitValue($value)
    {
        $embed = new PermissionsEmbed();
        //Check if it returns itself, for chaining.
        $this->assertEquals($embed, $embed->setBitValue(PermissionsEmbed::PARTS, 0, $value));
        $this->assertEquals($value, $embed->getBitValue(PermissionsEmbed::PARTS, 0));
    }

    /**
     * @dataProvider getStatesBOOL
     */
    public function testSetPermissionValue($value)
    {
        $embed = new PermissionsEmbed();
        //Check if it returns itself, for chaining.
        $this->assertEquals($embed, $embed->setPermissionValue(PermissionsEmbed::PARTS, 0, $value));
        $this->assertEquals($value, $embed->getPermissionValue(PermissionsEmbed::PARTS, 0));
    }

    public function testSetRawPermissionValue()
    {
        $embed = new PermissionsEmbed();
        $embed->setRawPermissionValue(PermissionsEmbed::PARTS, 10);
        $this->assertEquals(10, $embed->getRawPermissionValue(PermissionsEmbed::PARTS));
    }

    public function testSetRawPermissionValues()
    {
        $embed = new PermissionsEmbed();
        $embed->setRawPermissionValues([
            PermissionsEmbed::PARTS => 0,
            PermissionsEmbed::USERS => 100,
            PermissionsEmbed::CATEGORIES => 1304,
        ]);

        $this->assertEquals(0, $embed->getRawPermissionValue(PermissionsEmbed::PARTS));
        $this->assertEquals(100, $embed->getRawPermissionValue(PermissionsEmbed::USERS));
        $this->assertEquals(1304, $embed->getRawPermissionValue(PermissionsEmbed::CATEGORIES));

        //Test second method to pass perm names and values
        $embed->setRawPermissionValues(
            [PermissionsEmbed::PARTS, PermissionsEmbed::USERS, PermissionsEmbed::CATEGORIES],
            [0, 100, 1304]
        );

        $this->assertEquals(0, $embed->getRawPermissionValue(PermissionsEmbed::PARTS));
        $this->assertEquals(100, $embed->getRawPermissionValue(PermissionsEmbed::USERS));
        $this->assertEquals(1304, $embed->getRawPermissionValue(PermissionsEmbed::CATEGORIES));
    }
}
