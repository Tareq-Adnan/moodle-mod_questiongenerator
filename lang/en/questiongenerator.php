<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     mod_questiongenerator
 * @category    string
 * @copyright   2024 Tarekul Islam <tarekul.islam@brainstation-23.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Question Generator';
$string['modulenameplural'] = 'Questions Generator';
$string['pluginname'] = 'Questions Generator';
$string['questiongeneratorname'] = 'Name';
$string['pluginadministration'] = 'Questions Generator Administration';
$string['questiongenerator:addinstance'] = 'Add a new Questions Generator';
$string['questiongenerator:myaddinstance'] = 'Add a new Questions Generator to the My Moodle page';
$string['questiongenerator:view'] = "View Question Generator Activity";
$string['gquestions'] = "Generated Questions";
$string['aiquestionbank'] = "AI Question Bank";
$string['questionbank'] = 'Question Bank';
$string['questiongenerator:attemptquiz'] = 'Attempt Quiz';
$string['qggrade'] = "Quiz Review";
$string['questiongenerator:grade'] = "grade";
$string['gradeaverage'] = 'Average grade';
$string['gradeboundary'] = 'Grade boundary';
$string['gradeessays'] = 'Grade essays';
$string['gradehighest'] = 'Highest grade';
$string['attemptfirst'] = 'First attempt';
$string['attemptlast'] = 'First last';
$string['grademethod'] = 'Grading method';

$string['privacy:metadata:questiongenerator'] = 'Stores information about the question generator activity instances.';
$string['privacy:metadata:questiongenerator:course'] = 'The course in which the activity is located.';
$string['privacy:metadata:questiongenerator:name'] = 'The name of the question generator activity.';
$string['privacy:metadata:questiongenerator:intro'] = 'The introductory text for the activity.';
$string['privacy:metadata:questiongenerator:timemodified'] = 'The time when the activity was last modified.';
// Privacy metadata for the qg_categories table.
$string['privacy:metadata:qg_categories'] = 'The categories created by users for questions.';
$string['privacy:metadata:qg_categories:userid'] = 'The ID of the user who created the category.';
$string['privacy:metadata:qg_categories:name'] = 'The name of the question category.';
$string['privacy:metadata:qg_categories:timecreated'] = 'The time when the category was created.';
$string['privacy:metadata:qg_categories:timemodified'] = 'The time when the category was last modified.';

// Privacy metadata for the qg_questions table.
$string['privacy:metadata:qg_questions'] = 'The questions added by users, along with possible answers.';
$string['privacy:metadata:qg_questions:userid'] = 'The ID of the user who created the question.';
$string['privacy:metadata:qg_questions:question'] = 'The text of the question.';
$string['privacy:metadata:qg_questions:answer'] = 'The correct answer to the question.';
$string['privacy:metadata:qg_questions:timecreated'] = 'The time when the question was created.';
$string['privacy:metadata:qg_questions:timemodified'] = 'The time when the question was last modified.';

// Privacy metadata for the qg_quiz table.
$string['privacy:metadata:qg_quiz'] = 'The quizzes created by users.';
$string['privacy:metadata:qg_quiz:userid'] = 'The ID of the user who created the quiz.';
$string['privacy:metadata:qg_quiz:quiz_title'] = 'The title of the quiz.';
$string['privacy:metadata:qg_quiz:timecreated'] = 'The time when the quiz was created.';
$string['privacy:metadata:qg_quiz:timemodified'] = 'The time when the quiz was last modified.';

// Privacy metadata for the qg_quiz_attempts table.
$string['privacy:metadata:qg_quiz_attempts'] = 'The attempts made by users for quizzes.';
$string['privacy:metadata:qg_quiz_attempts:userid'] = 'The ID of the user who attempted the quiz.';
$string['privacy:metadata:qg_quiz_attempts:status'] = 'The status of the quiz attempt.';
$string['privacy:metadata:qg_quiz_attempts:timecreated'] = 'The time when the quiz attempt was made.';
$string['privacy:metadata:qg_quiz_attempts:timemodified'] = 'The time when the quiz attempt was last modified.';
