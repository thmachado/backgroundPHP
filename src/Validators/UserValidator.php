<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exceptions\ValidationException;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;

class UserValidator
{
    public function validate(array $data, bool $notPartial): void
    {
        $validator = Validator::arrayType()
            ->key('firstname', Validator::stringType()->notEmpty()->length(1, 100), $notPartial)
            ->key('lastname', Validator::stringType()->notEmpty()->length(1, 100), $notPartial)
            ->key('email', Validator::email()->notEmpty(), $notPartial)
            ->key('password', Validator::stringType()->notEmpty(), $notPartial);

        try {
            $validator->assert($data);
        } catch (NestedValidationException $exception) {
            throw new ValidationException($exception->getMessages());
        }
    }
}
