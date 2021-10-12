<?php

namespace App\Twig;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\MarkdownConverterInterface;
use Twig\Extra\Markdown\MarkdownInterface;

class Markdown implements MarkdownInterface
{
    private MarkdownConverterInterface $converter;

    public function __construct()
    {
        $this->converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function convert(string $body): string
    {
        return $this->converter->convertToHtml($body);
    }
}
