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

namespace App\Tests\Services\Attachments;

use App\Services\Attachments\AttachmentURLGenerator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AttachmentURLGeneratorTest extends WebTestCase
{
    protected const PUBLIC_DIR = '/public';

    protected static $service;

    public static function setUpBeforeClass() : void
    {
        //Get an service instance.
        self::bootKernel();
        self::$service = self::$container->get(AttachmentURLGenerator::class);
    }

    public function dataProvider()
    {
        return [
            ['/public/test.jpg', 'test.jpg'],
            ['/public/folder/test.jpg', 'folder/test.jpg'],
            ['/not/public/test.jpg', null],
            ['/public/', ''],
            ['not/absolute/test.jpg', null],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param $input
     * @param $expected
     */
    public function testabsolutePathToAssetPath($input, $expected)
    {
        $this->assertEquals($expected, static::$service->absolutePathToAssetPath($input, static::PUBLIC_DIR));
    }
}
