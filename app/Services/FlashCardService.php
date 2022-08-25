<?php

namespace App\Services;

use App\Enums\UserAnswerStatus;
use App\Models\Flashcard;
use App\Models\UserAnswer;
use App\Exceptions\QuestionAlreadyExistsException;
use Illuminate\Database\Query\JoinClause;

class FlashCardService
{

    /**
     * @param string $question
     * @param string $answer
     * @return void
     * @throws \Throwable
     * @author mohamedmunshif
     * @startOn 2022-08-24
     */
    public static function createFlashCard(string $question, string $answer): void
    {
        //Checking if question is already exist
        if (self::checkQuestionAlreadyExist($question)) {
            throw new QuestionAlreadyExistsException();
        }

        $flashcard = Flashcard::make(['question' => $question, 'answer' => $answer]);
        $flashcard->saveOrFail();
    }


    /**
     * @param string $question
     * @return bool
     * @author mohamedmunshif
     * @description check if question is already exist in the db
     * @startOn 2022-08-24
     */
    private static function checkQuestionAlreadyExist(string $question): bool
    {
      return Flashcard::where('question', $question)->exists();
    }


    /**
     * @return \Illuminate\Database\Eloquent\Collection
     * @author mohamedmunshif
     * @description get all flashcards
     * @startOn 2022-08-24
     * @endOn 2022-08-24
     */
    public static function getFlashCards(): \Illuminate\Database\Eloquent\Collection
    {
        return Flashcard::all();
    }

    /**
     * @param $user
     * @return array
     * @author mohamedmunshif
     * @description mapping answered questions withs status
     * @startOn 2022-08-25
     * @endOn 2022-08-25
     */
    public static function getPracticeStatus($user): array
    {

        $flashCardWithAnswers = self::getFlashCardsWithAnswers();
        $userAnswers = self::getUserAnswers($user->id);
        $flashCardsWithStatus = [];

        $flashCardWithAnswers->map(static function($d) use ($userAnswers, &$flashCardsWithStatus){
            $answer = $userAnswers->firstWhere('flashcard_id', $d->id);
            if($answer) {
                $flashCardsWithStatus[] = [
                    'Question' => $d->question,
                    'Answer' => UserAnswerStatus::from($answer['status'])->name,
                ];
            } else {
                $flashCardsWithStatus[] = [
                    'Question' => $d->question,
                    'Answer' => UserAnswerStatus::NOT_ANSWERED->name,
                ];
            }
        });

        return $flashCardsWithStatus;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     * @author mohamedmunshif
     * @description get all flash cards with answers (Relationship)
     * @startOn 2022-08-25
     * @endOn 2022-08-25
     */
    public static function getFlashCardsWithAnswers(): \Illuminate\Database\Eloquent\Collection|array
    {
        return Flashcard::with('answers')->get();
    }

    /**
     * @param $userId
     * @return UserAnswer[]|\Illuminate\Database\Eloquent\Collection
     * @author mohamedmunshif
     * @description get answers by user
     * @startOn 2022-08-25
     * @endOn 2022-08-25
     */
    public static function getUserAnswers($userId): \Illuminate\Database\Eloquent\Collection|array
    {
        return UserAnswer::where('user_id', $userId)->get();
    }


    /**
     * @param $user
     * @param $flashcardId
     * @param $answer
     * @return string
     * @throws \Throwable
     * @author mohamedmunshif
     * @description store answers in the db
     * @startOn 2022-08-25
     * @endOn 2022-08-25
     */
    public static function saveAnswer($user, $flashcardId, $answer): string
    {
        $flashcard = Flashcard::find($flashcardId);

        $dataUserAnswer = UserAnswer::where('user_id', $user->id)
            ->where('flashcard_id', $flashcardId)
            ->first();

        if ($dataUserAnswer?->state === UserAnswerStatus::CORRECT->value) {
            throw new CorrectAnswerAlreadyExistException();
        }

        $userAnswer = $dataUserAnswer ?? new UserAnswer;
        $userAnswer->answer = $answer;
        $userAnswer->user()->associate($user);
        $userAnswer->flashcard()->associate($flashcard);
        $userAnswer->status = $flashcard->answer === $answer ? UserAnswerStatus::CORRECT->value : UserAnswerStatus::INCORRECT->value;
        $userAnswer->save();

        return $userAnswer->status;
    }


    /**
     * @param $user
     * @return int
     * @author mohamedmunshif
     * @description get user's flash progress (total questions and correct answered questions
     * @startOn 2022-08-25
     * @endOn 2022-08-25
     */
    public static function getUserProgress($user):int{
        $totalQuestions = self::getFlashCards()->count();
        $totalCorrectAnswers = UserAnswer::where('user_id', $user->id)
            ->where('status', 'correct')
            ->get()
            ->count();
        try {
            return round(100 / $totalQuestions * $totalCorrectAnswers);

        }catch (\Throwable){
            return 0;
        }
    }


    /**
     * @return array
     * @author mohamedmunshif
     * @description
     * @startOn 2022-08-25
     * @endOn 2022-08-25
     */
    public static function getStatictis(){

        $stats = self::statictisQuery();

        $totalQuestions =  $stats['total_questions'];
        $totalAnswers = $stats['total_answers'];
        $totalCorrectAnswers = $stats['correct_answers'];

        try{
            $totalAnswersPercent = round($totalAnswers / $totalQuestions   * 100);
            $totalCorrectAnswersPercent = round($totalCorrectAnswers / $totalQuestions * 100);

        }catch (\Throwable $e){
            $totalAnswersPercent  = 0;
            $totalCorrectAnswersPercent = 0;
        }

        $statData = [
            'totalQuestions' => $totalQuestions,
            'totalAnswersPercent' => $totalAnswersPercent,
            'totalCorrectAnswersPercent' => $totalCorrectAnswersPercent,
        ];

        return  $statData;

    }

    /**
     * @return array
     * @author mohamedmunshif
     * @description
     * @startOn 2022-08-25
     * @endOn 2022-08-25
     */
    private static function statictisQuery(){

        return Flashcard::selectRaw('count(distinct flashcards.id) as `total_questions`')
            ->selectRaw('count(distinct any.flashcard_id) as `total_answers`')
            ->selectRaw('count(distinct correct.flashcard_id) as `correct_answers`')
            ->leftJoin('user_answers as any', 'any.flashcard_id', 'flashcards.id')
            ->join(
                'user_answers as correct',
                fn(JoinClause $join) => $join
                    ->on('correct.flashcard_id', '=', 'flashcards.id')
                    ->where('correct.status', '=', 'correct'),
                type: 'left'
            )
            ->first()
            ->toArray();
    }

    /**
     * @param $user
     * @return void
     * @author mohamedmunshif
     * @description delete user progress
     * @startOn 2022-08-25
     * @endOn 2022-08-25
     */
    public static function resetProgress($user): void
    {
        UserAnswer::forUserId($user->id)->delete();
    }



}
