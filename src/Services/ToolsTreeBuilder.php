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

namespace App\Services;

use App\Entity\Attachments\AttachmentType;
use App\Entity\Attachments\PartAttachment;
use App\Entity\Devices\Device;
use App\Entity\Parts\Category;
use App\Entity\Parts\Footprint;
use App\Entity\Parts\Manufacturer;
use App\Entity\Parts\MeasurementUnit;
use App\Entity\Parts\Part;
use App\Entity\Parts\Storelocation;
use App\Entity\Parts\Supplier;
use App\Entity\PriceInformations\Currency;
use App\Entity\UserSystem\Group;
use App\Entity\UserSystem\User;
use App\Helpers\TreeViewNode;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This Service generates the tree structure for the tools.
 * Whenever you change something here, you has to clear the cache, because the results are cached for performance reasons.
 */
class ToolsTreeBuilder
{
    protected $translator;
    protected $urlGenerator;
    protected $keyGenerator;
    protected $cache;
    protected $security;

    public function __construct(TranslatorInterface $translator, UrlGeneratorInterface $urlGenerator,
                                TagAwareCacheInterface $treeCache, UserCacheKeyGenerator $keyGenerator,
                                Security $security)
    {
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;

        $this->cache = $treeCache;

        $this->keyGenerator = $keyGenerator;

        $this->security = $security;
    }

    /**
     * Generates the tree for the tools menu.
     * The result is cached.
     *
     * @return TreeViewNode[] The array containing all Nodes for the tools menu.
     */
    public function getTree(): array
    {
        $key = 'tree_tools_'.$this->keyGenerator->generateKey();

        return $this->cache->get($key, function (ItemInterface $item) {
            //Invalidate tree, whenever group or the user changes
            $item->tag(['tree_tools', 'groups', $this->keyGenerator->generateKey()]);

            $tree = [];
            $tree[] = new TreeViewNode($this->translator->trans('tree.tools.edit'), null, $this->getEditNodes());
            $tree[] = new TreeViewNode($this->translator->trans('tree.tools.show'), null, $this->getShowNodes());
            $tree[] = new TreeViewNode($this->translator->trans('tree.tools.system'), null, $this->getSystemNodes());

            return $tree;
        });
    }

    /**
     * This functions creates a tree entries for the "edit" node of the tool's tree.
     *
     * @return TreeViewNode[]
     */
    protected function getEditNodes(): array
    {
        $nodes = [];

        if ($this->security->isGranted('read', new AttachmentType())) {
            $nodes[] = new TreeViewNode(
                $this->translator->trans('tree.tools.edit.attachment_types'),
                $this->urlGenerator->generate('attachment_type_new')
            );
        }
        if ($this->security->isGranted('read', new Category())) {
            $nodes[] = new TreeViewNode(
                $this->translator->trans('tree.tools.edit.categories'),
                $this->urlGenerator->generate('category_new')
            );
        }
        if ($this->security->isGranted('read', new Device())) {
            $nodes[] = new TreeViewNode(
                $this->translator->trans('tree.tools.edit.devices'),
                $this->urlGenerator->generate('device_new')
            );
        }
        if ($this->security->isGranted('read', new Supplier())) {
            $nodes[] = new TreeViewNode(
                $this->translator->trans('tree.tools.edit.suppliers'),
                $this->urlGenerator->generate('supplier_new')
            );
        }
        if ($this->security->isGranted('read', new Manufacturer())) {
            $nodes[] = new TreeViewNode(
                $this->translator->trans('tree.tools.edit.manufacturer'),
                $this->urlGenerator->generate('manufacturer_new')
            );
        }
        if ($this->security->isGranted('read', new Storelocation())) {
            $nodes[] = new TreeViewNode(
                $this->translator->trans('tree.tools.edit.storelocation'),
                $this->urlGenerator->generate('store_location_new')
            );
        }
        if ($this->security->isGranted('read', new Footprint())) {
            $nodes[] = new TreeViewNode(
                $this->translator->trans('tree.tools.edit.footprint'),
                $this->urlGenerator->generate('footprint_new')
            );
        }
        if ($this->security->isGranted('read', new Currency())) {
            $nodes[] = new TreeViewNode(
                $this->translator->trans('tree.tools.edit.currency'),
                $this->urlGenerator->generate('currency_new')
            );
        }
        if ($this->security->isGranted('read', new MeasurementUnit())) {
            $nodes[] = new TreeViewNode(
                $this->translator->trans('tree.tools.edit.measurement_unit'),
                $this->urlGenerator->generate('measurement_unit_new')
            );
        }
        if ($this->security->isGranted('create', new Part())) {
            $nodes[] = new TreeViewNode(
                $this->translator->trans('tree.tools.edit.part'),
                $this->urlGenerator->generate('part_new')
            );
        }

        return $nodes;
    }

    /**
     * This function creates the tree entries for the "show" node of the tools tree.
     *
     * @return TreeViewNode[]
     */
    protected function getShowNodes(): array
    {
        $show_nodes = [];
        $show_nodes[] = new TreeViewNode(
            $this->translator->trans('tree.tools.show.all_parts'),
            $this->urlGenerator->generate('parts_show_all')
        );

        if ($this->security->isGranted('read', new PartAttachment())) {
            $show_nodes[] = new TreeViewNode(
                $this->translator->trans('tree.tools.show.all_attachments'),
                $this->urlGenerator->generate('attachment_list')
            );
        }

        return $show_nodes;
    }

    /**
     * This function creates the tree entries for the "system" node of the tools tree.
     *
     * @return array
     */
    protected function getSystemNodes(): array
    {
        $nodes = [];

        if ($this->security->isGranted('read', new User())) {
            $nodes[] = new TreeViewNode(
                $this->translator->trans('tree.tools.system.users'),
                $this->urlGenerator->generate('user_new')
            );
        }
        if ($this->security->isGranted('read', new Group())) {
            $nodes[] = new TreeViewNode(
                $this->translator->trans('tree.tools.system.groups'),
                $this->urlGenerator->generate('group_new')
            );
        }

        return $nodes;
    }
}
