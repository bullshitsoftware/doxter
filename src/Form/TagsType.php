<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagsType extends AbstractType
{
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
            fn (array $tags): string => implode(' ', $tags),
            function (string $tags): array {
                $tags = array_filter(
                    array_map(
                        fn (string $tag) => mb_strtolower($tag),
                        explode(' ', $tags),
                    ),
                    fn (string $tag) => !empty($tag),
                );
                usort($tags, fn (string $s1, string $s2) => strcmp($s1, $s2));

                return $tags;
            },
        ));
    }
}
