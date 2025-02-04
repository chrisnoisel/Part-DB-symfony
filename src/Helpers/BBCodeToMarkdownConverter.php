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

namespace App\Helpers;

use League\HTMLToMarkdown\HtmlConverter;
use s9e\TextFormatter\Bundles\Forum as TextFormatter;
use SebastianBergmann\CodeCoverage\Report\Text;

class BBCodeToMarkdownConverter
{
    protected $html_to_markdown;

    public function __construct()
    {
        $this->html_to_markdown = new HtmlConverter();
    }

    /**
     * Converts the given BBCode to markdown.
     * BBCode tags that does not have a markdown aequivalent are outputed as HTML tags.
     *
     * @param $bbcode string The Markdown that should be converted.
     *
     * @return string The markdown version of the text.
     */
    public function convert(string $bbcode): string
    {
        //Convert BBCode to html
        $xml = TextFormatter::parse($bbcode);
        $html = TextFormatter::render($xml);

        //Now convert the HTML to markdown
        return $this->html_to_markdown->convert($html);
    }
}
