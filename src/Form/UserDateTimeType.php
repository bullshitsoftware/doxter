<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\UserSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
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
        return TextType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function (?\DateTimeImmutable $date): string {
                if ($date === null) {
                    return '';
                }
                $settings = $this->getUserSettings();
                $date = $date->setTimezone(new \DateTimeZone($settings->getTimezone()));

                return $date->format($settings->getDateTimeFormat());
            },
            function (string $date): ?\DateTimeImmutable {
                if (trim($date) === '') {
                    return null;
                }
                $settings = $this->getUserSettings();
                $date = \DateTimeImmutable::createFromFormat(
                    $settings->getDateTimeFormat(),
                    $date,
                    new \DateTimeZone($settings->getTimezone()),
                );
                if ($date === false) {
                    throw new TransformationFailedException('Invalid datetime string');
                }
                $date = $date->setTimezone(new \DateTimeZone(date_default_timezone_get()));

                return $date;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $settings = $this->getUserSettings();

        $resolver
            ->setDefault('empty_data', '')
            ->setDefault('attr', ['placeholder' => $settings->getDateTimeFormat()])
            ->setDefault('view_timezone', $settings->getTimezone());
    }

    private function getUserSettings(): UserSettings
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        if ($user === null) {
            throw new \LogicException('The type should only be used for authorized users');
        }

        return $user->getSettings();
    }
}
