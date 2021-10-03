<?php

namespace App\Tests\Form;

use App\Entity\Tag;
use App\Entity\User;
use App\Form\TagsType;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Security\Core\Security;

class TagsTypeTest extends TypeTestCase
{
    /**
     * @var Security|MockObject
     */
    private $security;

    /**
     * @var TagRepository|MockObject
     */
    private $repository;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->repository = $this->createMock(TagRepository::class);

        parent::setUp();
    }

    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [new TagsType($this->security, $this->repository)],
                [],
            ),
        ];
    }

    public function testTransform(): void
    {
        $tag1 = new Tag();
        $tag1->setName('tag1');
        $tag2 = new Tag();
        $tag2->setName('tag2');

        $form = $this->factory->create(TagsType::class, new ArrayCollection([$tag1, $tag2]));
        self::assertSame('tag1 tag2', $form->createView()->vars['data']);
    }

    public function testReverseTransform(): void
    {
        $collection = new ArrayCollection();
        $form = $this->factory->create(TagsType::class, $collection);
        $user = new User();
        $tag = new Tag();
        $tag->setName('tag');
        $this->security->method('getUser')->will(self::returnValue($user));
        $this->repository->method('findBy')
            ->with(self::equalTo(['user' => $user, 'name' => ['tag', 'tag1']]))
            ->will(self::returnValue([$tag]));
        
        $form->submit('tag tag1');
        self::assertTrue($form->isSynchronized());
        /** @var Tag[] $data */
        $data = $form->getData();
        self::assertCount(2, $data);
        self::assertSame($tag, $data[0]);
        self::assertSame($user, $data[1]->getUser());
        self::assertSame('tag1', $data[1]->getName());
    }
}
