<?php

namespace App\Exceptions;

use Exception;

class QuestionAlreadyExistsException extends Exception
{
    protected $message = "This Question Already Exist";
}
