<?php

namespace VCComponent\Laravel\Post\Traits;

use \TOC\MarkupFixer;
use \TOC\TocGenerator;

trait TableOfContentTrait
{
    public function getTableOfContent(int $topLevel = 1, int $depth = 6)
    {
        $markup_fixer = new MarkupFixer();
        $toc_generator = new TocGenerator();

        $fix_content = $markup_fixer->fix($this->content);
        $table_of_content = $toc_generator->getHtmlMenu($fix_content, $topLevel, $depth);

        return $table_of_content;
    }
}