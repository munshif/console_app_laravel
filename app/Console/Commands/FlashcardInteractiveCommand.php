<?php

namespace App\Console\Commands;

use App\Enums\FlashcardAction as Action;
use App\Enums\UserAnswerStatus;
use App\Models\User;
use App\Services\FlashCardService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class FlashcardInteractiveCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashcard:interactive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This console is for flashcard question and answer app';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Throwable
     */

    public function handle(): int
    {
        //asking for user email id
        $emailId = $this->ask('Please enter the user email id');

        $user = User::where('email', $emailId)->first();

        //check if user exist
        if (!$user) {
            $this->warn('User not found!');
            return 0;
        }

        $this->menuOptions($user);

        return 0;
    }


    /**
     * @return void
     * @throws \Throwable
     * @author mohamedmunshif
     * @startOn 2022-08-25
     * @endOn 2022-08-25
     */
    protected function menuOptions($user){

        do {
            $action = $this->selectAction();

            match ($action) {
                Action::CREATE_FLASHCARD => $this->createFlashcard(),
                Action::LIST_FLASHCARDS => $this->listFlashcards(),
                Action::PRACTICE => $this->practiceFlashCard($user),
                Action::STATS => $this->showStats(),
                Action::RESET => $this->resetProgress($user),
                default => $action
            };
        } while ($action !== Action::EXIT);

    }
    /**
     * @return Action
     * @author mohamedmunshif
     * @description select actions menu
     * @startOn 2022-08-24
     */
    protected function selectAction(): Action
    {
        $action = $this->choice('Select action', Action::actions());
        return Action::tryFrom($action);
    }


    /**
     * @return void
     * @author mohamedmunshif
     * @description function to create a flash card
     * @startOn 2022-08-24
     */
    protected function createFlashcard(): void
    {
        $flashCardQuestion = $this->ask('Question');
        $flashCardAnswer = $this->ask('Answer');

        // Storing a new flash card
        try {
            FlashCardService::createFlashCard($flashCardQuestion, $flashCardAnswer);
            $this->info('Flashcard added successfully!');

        } catch (\Throwable $e) {
            $this->error('Invalid Input!');
        }
    }


    /**
     * @return void
     * @author mohamedmunshif
     * @description fetch all available flash cards and display it in the table.
     * @startOn 2022-08-24
     */
    protected function listFlashcards(): void
    {
        // fetch flash cards
        $flashcards = FlashCardService::getFlashCards();

        $this->info('All Available Flash Cards');
        $this->table(
            ['Question', 'Answer'],
            $flashcards->map->only(['question', 'answer'])
        );
    }


    /**
     * @param User $user
     * @return void
     * @throws \Throwable
     * @author mohamedmunshif
     * @description
     * @startOn 2022-08-25
     * @endOn 2022-08-25
     */
    protected function practiceFlashCard(User $user): void
    {
        while (true) {

            $this->lineBreak();

            $flashCardQuestionsAndStatus = FlashCardService::getPracticeStatus($user);
            $flashCardWithAnswers = FlashCardService::getFlashCardsWithAnswers();

            // display a table with questions and their answer status
            $this->info('Practice overview:');
            $this->table(
                ['Question', 'Answer'],
                $flashCardQuestionsAndStatus
            );

            //show progress in %
            $progress = FlashCardService::getUserProgress($user);
            $this->info("Completion progress: {$progress}%");

            $questionsOption = $this->selectOption($flashCardWithAnswers);

            //If user selected Stop
            if ($questionsOption === null) {
                return;
            }

            if ($questionsOption->answers->count() && $questionsOption->answers[0]->status === 'correct') {

                // if the question is already correctly answered, show a message
                $this->warn('This question is already answered!');

            } else {

                // get user's answer and store it in the db
                $answer = $this->ask($questionsOption->question);

                try {
                    $result = FlashCardService::saveAnswer($user, $questionsOption->id, $answer);

                    match ($result) {
                        UserAnswerStatus::CORRECT->value => $this->info('Great! the answer is correct'),
                        UserAnswerStatus::INCORRECT->value => $this->warn('The answer is incorrect!'),
                        default => $this->error('Something went wrong! Invalid Status'),
                    };

                }catch (\Throwable $e){
                    $this->error('Invalid Input!');
                }


            }
        }
    }


    /**
     * @param array|Collection $questionsOption
     * @return mixed|null
     * @author mohamedmunshif
     * @startOn 2022-08-25
     * @endOn 2022-08-25
     */
    protected function selectOption(array|Collection $questionsOption)
    {
        $selection = $this->choice(
            'Select a question to answer or select `Stop` to return',
            $questionsOption
                ->map(fn($entry) => $entry->question)
                ->prepend('Stop')
                ->toArray()
        );

        if ($selection === 'Stop') {
            return null;
        }

        return $questionsOption->first(fn($questionsOption) => $questionsOption->question === $selection);
    }

    protected function showStats()
    {
        $stats = FlashCardService::getStatictis();

        // display total number of questions, % of answered, % of correctly answered
        $this->table(
            ['Total number of questions', 'Answered', 'Correct Answers'],
            [
                [
                    $stats['totalQuestions'],
                    $stats['totalAnswersPercent'] . '%',
                    $stats['totalCorrectAnswersPercent'] . '%'
                ]
            ]
        );
    }

    protected function resetProgress(User $user)
    {
        $confirmed = $this->confirm('This action will reset all your progress, do you wish to continue?');

        if (!$confirmed) {
            return;
        }

        FlashCardService::resetProgress($user);

        $this->info('Your progress has been reset and all answers deleted!');
    }


    /**
     * @return void
     * @author mohamedmunshif
     * @startOn 2022-08-25
     * @endOn 2022-08-25
     */
    protected function lineBreak(): void
    {
        $this->newLine();
        $this->line('────────────────────────');
        $this->newLine();
    }

}
