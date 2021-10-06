<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PasswordChangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('oldPassword', PasswordType::class, [
                'label' => 'Old password',
                'constraints' => [new UserPassword()],
            ])
            ->add('password', PasswordType::class, ['label' => 'New password'])
            ->add('passwordConfirm', PasswordType::class, [
                'label' => 'Confirm new password',
                'constraints' => [
                    new Callback(function($object, ExecutionContextInterface $context) {
                        if ($object === $context->getRoot()->get('password')->getData()) {
                            return;
                        }

                        $context->buildViolation('Passwords don\'t match')->addViolation();
                    }),
                ],
            ]);
    }
}
