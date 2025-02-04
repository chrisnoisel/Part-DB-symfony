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

namespace App\Tests\Entity;

use App\Entity\Attachments\AttachmentType;
use App\Entity\Parts\Category;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Tests\A;

/**
 * Test StructuralDBElement entities.
 * Note: Because StructuralDBElement is abstract we use AttachmentType here as a placeholder.
 */
class StructuralDBElementTest extends TestCase
{
    protected $root;
    protected $child1;
    protected $child2;
    protected $child3;
    protected $child1_1;
    protected $child1_2;

    public function setUp() : void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        //Build a simple hierachy
        $this->root = new AttachmentType();
        $this->root->setName('root')->setParent(null);
        $this->child1 = new AttachmentType();
        $this->child1->setParent($this->root)->setName('child1');
        $this->child2 = new AttachmentType();
        $this->child2->setName('child2')->setParent($this->root);
        $this->child3 = new AttachmentType();
        $this->child3->setName('child3')->setParent($this->root);
        $this->child1_1 = new AttachmentType();
        $this->child1_1->setName('child1_1')->setParent($this->child1);
        $this->child1_2 = new AttachmentType();
        $this->child1_2->setName('child1_2')->setParent($this->child1);
    }

    public function testIsRoot()
    {
        $this->assertTrue($this->root->isRoot());
        $this->assertFalse($this->child1->isRoot());
        $this->assertFalse($this->child1_2->isRoot());
    }

    public function testIsChildOf()
    {
        //Root must not be the child of any other node
        $this->assertFalse($this->root->isChildOf($this->child1));
        $this->assertFalse($this->root->isChildOf($this->root));

        //Check for direct parents
        $this->assertTrue($this->child1->isChildOf($this->root));
        $this->assertTrue($this->child1_2->isChildOf($this->child1));

        //Check for inheritance
        $this->assertTrue($this->child1_2->isChildOf($this->root));
    }

    public function testChildOfDifferentClasses()
    {
        $this->expectException(\InvalidArgumentException::class);
        $category = new Category();
        $this->root->isChildOf($category);
    }

    public function testChildOfExtendedClass()
    {
        //Doctrine extends the entities for proxy classes so the isChildOf mus also work for inheritance types
        $inheritance = new class() extends AttachmentType {
        };
        $inheritance->setParent($this->root);
        $this->assertTrue($inheritance->isChildOf($this->root));
        $this->assertFalse($this->root->isChildOf($inheritance));
    }

    public function testGetLevel()
    {
        $this->assertEquals(0, $this->root->getLevel());
        $this->assertEquals(1, $this->child1->getLevel());
        $this->assertSame(1, $this->child2->getLevel());
        $this->assertSame(2, $this->child1_2->getLevel());
        $this->assertSame(2, $this->child1_1->getLevel());
    }

    public function testGetFullPath()
    {
        $this->assertSame('root/child1/child1_1', $this->child1_1->getFullPath('/'));
        $this->assertSame('root#child2', $this->child2->getFullPath('#'));
    }

    public function testGetPathArray()
    {
        $this->assertEquals([$this->root, $this->child1, $this->child1_1], $this->child1_1->getPathArray());
        $this->assertEquals([$this->root, $this->child1], $this->child1->getPathArray());
        $this->assertEquals([$this->root], $this->root->getPathArray());
    }
}
