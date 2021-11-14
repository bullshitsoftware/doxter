<?php

namespace App\Form;

use function is_array;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use JsonException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class JsonObjectType extends AbstractType
{
    public function getParent(): string
    {
        return TextareaType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            fn (array $json) => json_encode($json, JSON_PRETTY_PRINT),
            function (string $json): mixed {
                try {
                    $result = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    throw new TransformationFailedException('Invalid json');
                }

                if (!is_array($result)) {
                    throw new TransformationFailedException('Not an object');
                }

                return $result;
            },
        ));
    }
}
