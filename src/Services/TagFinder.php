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

use App\Entity\Parts\Part;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A service related for searching for tags. Mostly useful for autocomplete reasons.
 */
class TagFinder
{
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'query_limit' => 75,
            'return_limit' => 25,
            'min_keyword_length' => 3,
        ]);
    }

    /**
     * Search tags that begins with the certain keyword.
     *
     * @param string $keyword The keyword the tag must begin with
     * @param array  $options Some options specifying the search behavior. See configureOptions for possible options.
     *
     * @return string[] An array containing the tags that match the given keyword.
     */
    public function searchTags(string $keyword, array $options = [])
    {
        $results = [];
        $keyword_regex = '/^'.preg_quote($keyword, '/').'/';

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $options = $resolver->resolve($options);

        //If the keyword is too short we will get to much results, which takes too much time...
        if (mb_strlen($keyword) < $options['min_keyword_length']) {
            return [];
        }

        //Build a query to get all
        $qb = $this->em->createQueryBuilder();

        $qb->select('p.tags')
            ->from(Part::class, 'p')
            ->where('p.tags LIKE ?1')
            ->setMaxResults($options['query_limit'])
            //->orderBy('RAND()')
            ->setParameter(1, '%'.$keyword.'%');

        $possible_tags = $qb->getQuery()->getArrayResult();

        //Iterate over each possible tags (which are comma separated) and extract tags which match our keyword
        foreach ($possible_tags as $tags) {
            $tags = explode(',', $tags['tags']);
            $results = array_merge($results, preg_grep($keyword_regex, $tags));
        }

        $results = array_unique($results);
        //Limit the returned tag count to specified value.
        return \array_slice($results, 0, $options['return_limit']);
    }
}
