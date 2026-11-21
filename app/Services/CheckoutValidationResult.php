<?php

namespace App\Services;

class CheckoutValidationResult
{
    public bool $isValid = true;

    /** @var list<string> */
    public array $errors = [];

    public function addError(string $error): void
    {
        $this->errors[] = $error;
        $this->isValid = false;
    }
}
