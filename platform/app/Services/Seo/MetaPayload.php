<?php

namespace App\Services\Seo;

class MetaPayload
{
    public function __construct(
        public string $title,
        public ?string $description,
        public string $canonical,
        public ?string $ogTitle = null,
        public ?string $ogDescription = null,
        public ?string $ogImage = null,
        public bool $indexable = true,
    ) {}
}
