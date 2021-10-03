<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class UserDateTimeType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    
    public function getParent(): string
    {
        return DateTimeType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        if ($user === null) {
            throw new \LogicException('The type should only be used for authorized users');
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
