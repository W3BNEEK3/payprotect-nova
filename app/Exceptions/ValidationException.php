<?php
namespace App\Exceptions;

class ValidationException extends AppException
{
    private array $errors;

    public function __construct(array $errors)
    {
        parent::__construct('Validation failed');
        $this->errors = $errors;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
