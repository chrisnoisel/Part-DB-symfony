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

namespace App\Entity\UserSystem;

use App\Entity\Attachments\GroupAttachment;
use App\Entity\Base\StructuralDBElement;
use App\Security\Interfaces\HasPermissionsInterface;
use App\Validator\Constraints\ValidPermission;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * This entity represents an user group.
 *
 * @ORM\Entity()
 * @ORM\Table("`groups`")
 */
class Group extends StructuralDBElement implements HasPermissionsInterface
{
    /**
     * @var Collection|GroupAttachment[]
     * @ORM\OneToMany(targetEntity="App\Entity\Attachments\ManufacturerAttachment", mappedBy="element", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $attachments;

    /**
     * @ORM\OneToMany(targetEntity="Group", mappedBy="parent")
     */
    protected $children;

    /**
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="group")
     */
    protected $users;

    /** @var PermissionsEmbed
     * @ORM\Embedded(class="PermissionsEmbed", columnPrefix="perms_")
     * @ValidPermission()
     */
    protected $permissions;

    public function __construct()
    {
        parent::__construct();
        $this->permissions = new PermissionsEmbed();
    }

    /**
     * Returns the ID as an string, defined by the element class.
     * This should have a form like P000014, for a part with ID 14.
     *
     * @return string The ID as a string;
     */
    public function getIDString(): string
    {
        return 'G'.sprintf('%06d', $this->getID());
    }

    public function getPermissions(): PermissionsEmbed
    {
        return $this->permissions;
    }
}
