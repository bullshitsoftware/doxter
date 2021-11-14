<?php

namespace App\Validator;

use function count;
use function in_array;
use function is_array;
use function is_float;
use function is_int;
use function is_string;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class WeightsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Weights) {
            throw new UnexpectedTypeException($constraint, Weights::class);
        }

        if (null === $value) {
            return;
        }

        if (!self::onlyKeys($value, ['tag', 'date'])) {
            $this->context->addViolation('Config must contain tag and date property only');

            return;
        }

        $this->validateTag($value['tag']);
        $this->validateDate($value['date']);
    }

    private function validateTag(mixed $value): void
    {
        if (!is_array($value)) {
            $this->context->addViolation('Tag property must be object');

            return;
        }

        foreach ($value as $name => $weight) {
            if (!is_string($name)) {
                $this->context->addViolation('Non string tag name: %name%', ['%name%' => $name]);
            }

            if (!self::isNumeric($weight)) {
                $this->context->addViolation('Non numeric tag weight: %weight%', ['%weight%' => $weight]);
            }
        }
    }

    private function validateDate(mixed $value): void
    {
        if (!is_array($value)) {
            $this->context->addViolation('Date property must be object');

            return;
        }

        $properties = ['age', 'due', 'started'];
        if (!self::onlyKeys($value, $properties)) {
            $this->context->addViolation('Date property must contain age, due and started properties only');
        }

        foreach ($properties as $property) {
            if (!self::isNumeric($value[$property])) {
                $this->context->addViolation(
                    'Property "%name%" must be numeric, "%value%" provided',
                    [
                        '%name%' => $property,
                        '%value%' => $value[$property],
                    ],
                );
            }
        }
    }

    /**
     * @param array<string,mixed> $value
     * @param array<string>       $keys
     */
    private static function onlyKeys(array $value, array $keys): bool
    {
        if (count(array_keys($value)) !== count($keys)) {
            return false;
        }

        foreach (array_keys($value) as $key) {
            if (!in_array($key, $keys)) {
                return false;
            }
        }

        return true;
    }

    private static function isNumeric(mixed $value): bool
    {
        return is_int($value) || is_float($value);
    }
}
