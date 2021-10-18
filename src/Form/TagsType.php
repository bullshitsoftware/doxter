<?php

namespace App\Form;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LogicException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class TagsType extends AbstractType
{
    public function __construct(private Security $security, private TagRepository $repository)
    {
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('data_class', null)
            ->setDefault('empty_data', '')
            ->setDefault('required', false)
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            fn (Collection $tags): string => implode(' ', $tags->map(fn (Tag $tag) => $tag->getName())->toArray()),
            function (string $tags): Collection {
                $user = $this->security->getUser();
                if (null === $user) {
                    throw new LogicException('The type should only be used for authorized users');
                }

                $tags = array_filter(
                    array_map(
                        fn (string $tag) => mb_strtolower($tag),
                        explode(' ', $tags),
                    ),
                    fn (string $tag) => !empty($tag),
                );
                $knownTags = $this->repository->findBy(['user' => $user, 'name' => $tags]);
                $collection = new ArrayCollection($knownTags);

                $knownTags = array_map(fn (Tag $tag) => $tag->getName(), $knownTags);
                $newTags = array_diff($tags, $knownTags);
                foreach ($newTags as $tagName) {
                    $tag = new Tag();
                    $tag->setUser($user)->setName($tagName);
                    $collection->add($tag);
                }

                return $collection;
            },
        ));
    }
}
