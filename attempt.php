<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * TODO describe file quiz
 *
 * @package    mod_questiongenerator
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_login();

$cmid = optional_param('id', 0, PARAM_INT);
global $DB, $OUTPUT, $PAGE;


$url = new moodle_url('/mod/questiongenerator/attempt.php');
$PAGE->set_url($url);
$context = context_module::instance($cmid);
$cm = get_coursemodule_from_id('questiongenerator', $cmid, 0, false, MUST_EXIST);
$PAGE->set_cm($cm);
$PAGE->set_context($context);
$PAGE->set_heading(get_string('qggrade', 'questiongenerator'));
$PAGE->set_title(get_string('qggrade', 'questiongenerator'));


$sql = "SELECT aiquestion.question, aiquestion.answer, aiquestion.id as questionid, aiquestion.question_level as level, aiquestion.options
           FROM {qg_quiz} quiz
      LEFT JOIN {qg_quiz_questions} question ON quiz.id = question.quizid 
      LEFT JOIN {qg_questions} aiquestion ON question.questionid = aiquestion.id
          WHERE quiz.state = 1 AND quiz.cmid = :cmid";
$questions = $DB->get_records_sql($sql, ['cmid' => $cmid]);

$quiz = $DB->get_record('qg_quiz', ['cmid' => $cmid, 'state' => 1], 'id, easy, medium, hard');

$totalMarkeasy = $quiz->easy;
$totalmarkmedium = $quiz->medium;
$totalMarkhard = $quiz->hard;

// Initialize counts for each difficulty level
$countEasy = 0;
$countMedium = 0;
$countHard = 0;

// Process questions and add the index for options
$questions = array_map(function ($question) use (&$countEasy, &$countMedium, &$countHard) {
    $question->options = unserialize($question->options);
    
    // Count the questions based on their level
    switch ($question->level) {
        case 'easy':
            $question->color = 'success';
            $countEasy++;
            break;
        case 'medium':
            $question->color = 'warning';
            $countMedium++;
            break;
        case 'hard':
            $question->color = 'danger';
            $countHard++;
            break;
    }
    
    $question->level = ucfirst($question->level);
   
    // Add an index to each option
    foreach ($question->options as $index => $option) {
        $question->indexed_options[] = [
            'index' => $index + 1,
            'value' => $option
        ];
        if($option === $question->answer) {
            $question->answer = base64_encode($index+1);
        }
    }

    return $question;
}, $questions);

// Calculate per-question mark for each difficulty level
$easyPerQuestionMark = $countEasy > 0 ? $totalMarkeasy / $countEasy : 0;
$mediumPerQuestionMark = $countMedium > 0 ? $totalmarkmedium / $countMedium : 0;
$hardPerQuestionMark = $countHard > 0 ? $totalMarkhard / $countHard : 0;
$marks = [
    'easy' => $easyPerQuestionMark,
    'medium' => $mediumPerQuestionMark,
    'hard' => $hardPerQuestionMark
];
$PAGE->requires->js_call_amd('mod_questiongenerator/quiz_handle', 'quizHandling', ['cmid' => $cmid,'quizid' => $quiz->id,'marks' => $marks]);

//  echo "<pre>";
//  var_dump($marks);
//  die;
echo $OUTPUT->header();

echo $OUTPUT->render_from_template('mod_questiongenerator/attempt', ['questions' => array_values($questions)]);

echo $OUTPUT->footer();
