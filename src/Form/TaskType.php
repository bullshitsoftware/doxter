<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('tags', TagsType::class)
            ->add('description', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('created', UserDateTimeType::class)
            ->add('wait', UserDateTimeType::class, ['required' => false])
            ->add('started', UserDateTimeType::class, ['required' => false])
            ->add('ended', UserDateTimeType::class, ['required' => false])
            ->add('due', UserDateTimeType::class, ['required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
