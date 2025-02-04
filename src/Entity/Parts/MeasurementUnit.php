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

namespace App\Entity\Parts;

use App\Entity\Attachments\MeasurementUnitAttachment;
use App\Entity\Base\PartsContainingDBElement;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This unit represents the unit in which the amount of parts in stock are measured.
 * This could be something like N, grams, meters, etc...
 *
 * @ORM\Entity(repositoryClass="App\Repository\StructuralDBElementRepository")
 * @ORM\Table(name="`measurement_units`")
 * @UniqueEntity("unit")
 */
class MeasurementUnit extends PartsContainingDBElement
{
    /**
     * @var Collection|MeasurementUnitAttachment[]
     * @ORM\OneToMany(targetEntity="App\Entity\Attachments\MeasurementUnitAttachment", mappedBy="element", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $attachments;

    /**
     * @var string The unit symbol that should be used for the Unit. This could be something like "", g (for grams)
     *             or m (for meters).
     * @ORM\Column(type="string", name="unit", nullable=true)
     * @Assert\Length(max=10)
     */
    protected $unit;

    /**
     * @var bool Determines if the amount value associated with this unit should be treated as integer.
     *           Set to false, to measure continuous sizes likes masses or lengths.
     * @ORM\Column(type="boolean", name="is_integer")
     */
    protected $is_integer = false;

    /**
     * @var bool Determines if the unit can be used with SI Prefixes (kilo, giga, milli, etc.).
     *           Useful for sizes like meters.
     * @ORM\Column(type="boolean", name="use_si_prefix")
     */
    protected $use_si_prefix = false;

    /**
     * @ORM\OneToMany(targetEntity="MeasurementUnit", mappedBy="parent", cascade={"persist"})
     */
    protected $children;

    /**
     * @ORM\ManyToOne(targetEntity="MeasurementUnit", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Part", mappedBy="partUnit", fetch="EXTRA_LAZY")
     */
    protected $parts;

    /**
     * Returns the ID as an string, defined by the element class.
     * This should have a form like P000014, for a part with ID 14.
     *
     * @return string The ID as a string;
     */
    public function getIDString(): string
    {
        return 'MU'.$this->getID();
    }

    /**
     * @return string
     */
    public function getUnit(): ?string
    {
        return $this->unit;
    }

    /**
     * @param string $unit
     *
     * @return MeasurementUnit
     */
    public function setUnit(?string $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @return bool
     */
    public function isInteger(): bool
    {
        return $this->is_integer;
    }

    /**
     * @param bool $isInteger
     * @return MeasurementUnit
     */
    public function setIsInteger(bool $isInteger): self
    {
        $this->is_integer = $isInteger;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUseSIPrefix(): bool
    {
        return $this->use_si_prefix;
    }

    /**
     * @param bool $usesSIPrefixes
     * @return MeasurementUnit
     */
    public function setUseSIPrefix(bool $usesSIPrefixes): self
    {
        $this->use_si_prefix = $usesSIPrefixes;

        return $this;
    }
}
