<?php

namespace VCComponent\Laravel\Post\Traits;

use \TOC\MarkupFixer;
use \TOC\TocGenerator;

trait TableOfContentsTrait
{
    public function getTableOfContents(int $top_level = 1, int $depth = 6)
    {
        $markup_fixer = new MarkupFixer();
        $toc_generator = new TocGenerator();

        $fix_content = $markup_fixer->fix($this->content);
        $table_of_contents = $toc_generator->getHtmlMenu($fix_content, $top_level, $depth);

        return $table_of_contents;
    }
}