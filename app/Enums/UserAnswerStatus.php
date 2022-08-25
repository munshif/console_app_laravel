<?php

namespace App\Enums;

enum UserAnswerStatus:string
{
    case NOT_ANSWERED = 'not_answered';
    case CORRECT = 'correct';
    case INCORRECT = 'incorrect';
}
