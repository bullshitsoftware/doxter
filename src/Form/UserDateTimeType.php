<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserDateTimeType extends AbstractType
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }
    
    public function getParent(): string
    {
        return DateTimeType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            throw new \LogicException('The type should only be used for authorized users');
        }
        $user = $token->getUser();
        if (!$user instanceof User) {
            throw new \LogicException(sprintf(
                'Unsupported user instance: %s', 
                is_object($user) ? get_class($user) : gettype($user),
            ));
        }

        $resolver
            ->setDefault('input', 'datetime_immutable')
            ->setDefault('widget', 'single_text')
            ->setDefault('html5', false)
            ->setDefault('format', 'yyyy-MM-dd HH:mm:ss')
            ->setDefault('attr', ['placeholder' => 'yyyy-MM-dd HH:mm:ss'])
            ->setDefault('view_timezone', $user->getSettings()->getTimezone())
        ;
    }
}
