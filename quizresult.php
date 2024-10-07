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
 * TODO describe file quizresult
 *
 * @package    mod_questiongenerator
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

require_login();
$cmid = optional_param('id', 0, PARAM_INT);
$user = required_param('user', PARAM_INT);
$quiz = required_param('quiz', PARAM_INT);
global $DB, $OUTPUT, $PAGE, $USER;


$url = new moodle_url('/mod/questiongenerator/quizresult.php');
$PAGE->set_url($url);
$context = context_module::instance($cmid);
$cm = get_coursemodule_from_id('questiongenerator', $cmid, 0, false, MUST_EXIST);
$PAGE->set_cm($cm);
$PAGE->set_context($context);
$PAGE->set_heading(get_string('qggrade', 'questiongenerator'));
$PAGE->set_title(get_string('qggrade', 'questiongenerator'));
$PAGE->requires->js_call_amd('mod_questiongenerator/quiz_result', 'quizResult', ['cmid' => $cmid]);
$PAGE->requires->css('/mod/questiongenerator/css/style.css');

$cap = has_capability('mod/questiongenerator:attemptquiz', $context);
$isadmin = is_siteadmin($USER->id);
$sql = "SELECT  grade.questionid,grade.grade, grade.answer as selected, question.question,question.options,question.answer,
                question.question_level as level, quiz.quiz_title
          FROM {qg_grades} grade
          JOIN {qg_questions} question ON grade.questionid = question.id
          JOIN {qg_quiz} quiz ON grade.quizid = quiz.id
         WHERE grade.userid = :userid AND grade.quizid = :quizid";
if ($isadmin) {
    $questions = $DB->get_records_sql($sql, ['userid' => $user, 'quizid' => $quiz]);
} else {
    $questions = $DB->get_records_sql($sql, ['userid' => !$cap ? $user : $USER->id, 'quizid' => $quiz]);
}

$questions = array_map(function ($question) use (&$counteasy, &$countmedium, &$counthard) {
    $question->options = unserialize($question->options);

    // Count the questions based on their level.
    switch ($question->level) {
        case 'easy':
            $question->color = 'success';
            $counteasy++;
            break;
        case 'medium':
            $question->color = 'warning';
            $countmedium++;
            break;
        case 'hard':
            $question->color = 'danger';
            $counthard++;
            break;
    }

    $question->level = ucfirst($question->level);

    // Add an index to each option.
    foreach ($question->options as $index => $option) {
        $question->indexed_options[] = [
            'index' => $index + 1,
            'value' => $option,
            'correct' => $option === $question->answer ? true : false,
            'selected' => $index + 1 == $question->selected ? true : false,
        ];
        if ($option === $question->answer) {
            $question->answer = base64_encode($index + 1);
        }
    }

    return $question;
}, $questions);

echo $OUTPUT->header();
if ($questions) {
    echo $OUTPUT->render_from_template('mod_questiongenerator/result',
    ['questions' => array_values($questions), 'quiz_title' => array_values($questions)[0]->quiz_title]);
} else {

    echo html_writer::tag('p', 'Quiz Not Available at this moment', ['class' => 'alert alert-info']);
    echo html_writer::start_div('vh-100');

}

echo $OUTPUT->footer();
