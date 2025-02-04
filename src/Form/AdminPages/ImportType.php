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

namespace App\Form\AdminPages;

use App\Entity\Base\StructuralDBElement;
use App\Form\Type\StructuralEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class ImportType extends AbstractType
{
    protected $security;
    protected $trans;

    public function __construct(Security $security, TranslatorInterface $trans)
    {
        $this->security = $security;
        $this->trans = $trans;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'];

        //Disable import if user is not allowed to create elements.
        $entity = new $data['entity_class']();
        $perm_name = 'create';
        $disabled = !$this->security->isGranted($perm_name, $entity);

        $builder

            ->add('format', ChoiceType::class, [
                'choices' => ['JSON' => 'json', 'XML' => 'xml', 'CSV' => 'csv', 'YAML' => 'yaml'],
                'label' => $this->trans->trans('export.format'),
                'disabled' => $disabled, ])
            ->add('csv_separator', TextType::class, ['data' => ';',
                'label' => $this->trans->trans('import.csv_separator'),
                'disabled' => $disabled, ]);

        if ($entity instanceof StructuralDBElement) {
            $builder->add('parent', StructuralEntityType::class, [
                'class' => $data['entity_class'],
                'required' => false,
                'label' => $this->trans->trans('parent.label'),
                'disabled' => $disabled,
            ]);
        }

        $builder->add('file', FileType::class, [
            'label' => $this->trans->trans('import.file'),
            'attr' => ['class' => 'file', 'data-show-preview' => 'false', 'data-show-upload' => 'false'],
            'disabled' => $disabled,
        ])

            ->add('preserve_children', CheckboxType::class, ['data' => true, 'required' => false,
                'label' => $this->trans->trans('import.preserve_children'),
                'label_attr' => ['class' => 'checkbox-custom'], 'disabled' => $disabled, ])
            ->add('abort_on_validation_error', CheckboxType::class, ['data' => true, 'required' => false,
                'label' => $this->trans->trans('import.abort_on_validation'),
                'help' => $this->trans->trans('import.abort_on_validation.help'),
                'label_attr' => ['class' => 'checkbox-custom'], 'disabled' => $disabled, ])

            //Buttons
            ->add('import', SubmitType::class, ['label' => 'import.btn', 'disabled' => $disabled]);
    }
}
