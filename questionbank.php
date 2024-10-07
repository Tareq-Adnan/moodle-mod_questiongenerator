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
 * TODO describe file questionbank
 *
 * @package    mod_questiongenerator
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_login();

$cmid = optional_param('id', 0, PARAM_INT);
global $DB, $OUTPUT, $PAGE, $USER;

$context = context_module::instance($cmid);
$cm = get_coursemodule_from_id('questiongenerator', $cmid, 0, false, MUST_EXIST);
$isadmin = is_siteadmin($USER->id);
if ($isadmin) {
    $categories = $DB->get_records('qg_categories', [], '', 'id, name');
    $quizes = $DB->get_records('qg_quiz', ['cmid' => $cm->id], '', 'id, quiz_title, state, total_marks');

} else {
    $categories = $DB->get_records('qg_categories', ['cmid' => $cm->id, 'userid' => $USER->id], '', 'id, name');

    $quizes = $DB->get_records('qg_quiz', ['cmid' => $cm->id, 'userid' => $USER->id], '', 'id, quiz_title, state,total_marks');

}

$templatecontext = [];

// Check if categories are available.
if (!empty($categories)) {
    // Get the first category from the list.
    $firstcategory = reset($categories); // Get the first category object.

    // Fetch related questions for the first category.
    $questions = $DB->get_records(
        'qg_questions',
        ['category_id' => $firstcategory->id],
        '',
        'id, question, options, answer, question_level'
    );

    // Prepare questions data for Mustache.
    $templatecontext['questions'] = array_map(function ($question) {
        return [
            'id' => $question->id,
            'question' => $question->question,
            'options' => unserialize($question->options), // Assuming options are comma-separated.
            'answer' => $question->answer,
            'difficulty' => $question->question_level,
        ];
    }, array_values($questions));

    // Include categories and first category ID in the template context.
    $templatecontext['categories'] = array_values($categories);
    $templatecontext['first_category_id'] = $firstcategory->id;
    $templatecontext['has_categories'] = !empty($categories); // Set true if categories exist, false otherwise.
    $templatecontext['quizes'] = array_values($quizes);

}
$url = new moodle_url('/mod/questiongenerator/questionbank.php');
$PAGE->set_url($url);

$PAGE->set_cm($cm);
$PAGE->set_context($context);
$PAGE->set_heading(get_string('questionbank', 'questiongenerator'));
$PAGE->set_title(get_string('questionbank', 'questiongenerator'));


// Custom JS and CSS.
$PAGE->requires->js_call_amd('mod_questiongenerator/questiongenerator', 'init', ['cmid' => $cmid]);


$PAGE->requires->css('/mod/questiongenerator/css/style.css');

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_questiongenerator/questiongenerator', $templatecontext);
echo $OUTPUT->footer();
