

## About the Project

Flash Card CLI Application with Laravel & Artisan. 

Laravel sail has been used in the application.

## Requirements
 * PHP 8.1 with composer
 * Docker - Docker needs to be installed, up and running.

<hr>

## How to run the project?

### Setting Up

<h4>1. Please install the dependencies by following command:</h4>
<code>composer install</code>

<h4>2. To start the instance:</h4>
<code>./vendor/bin/sail up -d </code>

<h4>3. To stop the instance:</h4>
<code>./vendor/bin/sail down </code>

<h4>5. Need to run the database migration to create the necessary tables in the database:</h4>
<code>./vendor/bin/sail artisan migrate</code>

<h4>6. Run database seeder to create the test users:</h4>
<code>./vendor/bin/sail artisan db:seed</code>

**Note: Create a copy of .env.example to and rename .env**

<hr>

## Start The CLI Application
###Step 01
<code>./vendor/bin/sail artisan flashcard:interactive</code>

###Step 02
 **Need to enter test user's email id**

<code>munshif@test.com</code> - This is a test user, which has been created from seeder.

<hr>

##Scenario

###Task Description
<p>We want an interactive CLI program for Flashcard practice. For context: a flashcard is a spaced repetition tool for memorising questions and their respective answers.
The command `php artisan flashcard:interactive` should present a main menu with the following actions:</p>

### 1 . Create a flashcard
The user will be prompted to give a flashcard question and the only answer to that question. The question and the answer should be stored in the database.

### 2 . List all flashcards
A table listing all the created flashcard questions with the correct answer.

###3 . Practice
* This is where a user will practice the flashcards that have been added.
* First, show the current progress:The user will be presented with a table listing all questions, and their practice status for each question: <b>Not answered, Correct, Incorrect.</b>
* As a table footer, we want to present the % of completion (all questions vs correctly answered).
* Then, the user will pick the question they want to practice.We should not allow answering questions that are already correct.
* Upon answering, store the answer in the DB and print correct/incorrect.
* Finally, show the first step again (the current progress) and allow the user to keep practicing until they explicitly decide to stop.

###4 . Stats
Display the following stats:
- The total amount of questions.
- % of questions that have an answer.
- % of questions that have a correct answer.

###5 . Reset

This command should erase all practice progress and allow a fresh start.

###6 . Exit
  This option will conclude the interactive command.
  Note: The program should only exit by choosing the `Exit` option on the main menu (or killing the process)
