<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator;

use App\Validator\NotCircularReference;
use App\Validator\NotCircularReferenceValidator;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class NotCircularReferenceValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): NotCircularReferenceValidator
    {
        return new NotCircularReferenceValidator(new PropertyAccessor());
    }

    public function testNotCircular(): void
    {
        $constraint = new NotCircularReference(
            [
                'propertyName' => 'parent',
                'message' => 'myMessage',
            ]
        );

        $value1 = new \StdClass();
        $value1->parent = null;
        $this->validator->validate($value1, $constraint);

        $this->assertNoViolation();

        $value2 = new \StdClass();
        $value2->parent = $value1;
        $this->validator->validate($value2, $constraint);

        $this->assertNoViolation();
    }

    public function testCircularToSelf(): void
    {
        $constraint = new NotCircularReference(
            [
                'propertyName' => 'parent',
                'message' => 'myMessage',
            ]
        );

        $value = new \StdClass();
        $value->parent = $value;
        $this->validator->validate($value, $constraint);

        $this->buildViolation('myMessage')
            ->setCode(NotCircularReference::NOT_CIRCULAR_REFERENCE)
            ->assertRaised();
    }

    public function testCircularBetween2(): void
    {
        $constraint = new NotCircularReference(
            [
                'propertyName' => 'parent',
                'message' => 'myMessage',
            ]
        );

        $value1 = new \StdClass();
        $value2 = new \StdClass();
        $value1->parent = $value2;
        $value2->parent = $value1;
        $this->validator->validate($value1, $constraint);

        $this->buildViolation('myMessage')
            ->setCode(NotCircularReference::NOT_CIRCULAR_REFERENCE)
            ->assertRaised();
    }
}
