<?php

namespace App\Service;

use Cocur\Slugify\Slugify as CocurSlugify;

class SlugifyService
{
    private $slugify;

    public function __construct()
    {
        $this->slugify = new CocurSlugify();
    }

    public function generate(string $text): string
    {
        return $this->slugify->slugify($text);
    }
}
