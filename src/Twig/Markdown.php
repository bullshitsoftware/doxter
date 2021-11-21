<?php

declare(strict_types=1);

namespace App\Twig;

use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\MarkdownConverterInterface;
use Twig\Extra\Markdown\MarkdownInterface;

class Markdown implements MarkdownInterface
{
    private MarkdownConverterInterface $converter;

    public function __construct()
    {
        $this->converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    public function convert(string $body): string
    {
        return $this->converter->convertToHtml($body)->getContent();
    }
}
