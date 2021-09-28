<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('created', DateTimeType::class, [
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd HH:mm:ss',
            ])
            ->add('wait', DateTimeType::class, [
                'required' => false,
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd HH:mm:ss',
            ])
            ->add('started', DateTimeType::class, [
                'required' => false,
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd HH:mm:ss',
            ])
            ->add('ended', DateTimeType::class, [
                'required' => false,
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd HH:mm:ss',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
