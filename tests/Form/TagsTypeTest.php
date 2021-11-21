<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Form\TagsType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class TagsTypeTest extends TypeTestCase
{
    /**
     * @return array<PreloadedExtension>
     */
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [new TagsType()],
                [],
            ),
        ];
    }

    public function testTransform(): void
    {
        $form = $this->factory->create(TagsType::class, ['tag1', 'tag2']);
        self::assertSame('tag1 tag2', $form->createView()->vars['data']);
    }

    public function testReverseTransform(): void
    {
        $form = $this->factory->create(TagsType::class, []);
        $form->submit('tag tag1');
        self::assertTrue($form->isSynchronized());
        $data = $form->getData();
        self::assertCount(2, $data);
        self::assertSame('tag', $data[0]);
        self::assertSame('tag1', $data[1]);
    }
}
