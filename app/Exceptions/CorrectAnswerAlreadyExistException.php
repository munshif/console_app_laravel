<?php

namespace App\Exceptions;

use Exception;

class CorrectAnswerAlreadyExistException extends Exception
{
    protected $message = "Already Answered Correctly For This Question!";
}
