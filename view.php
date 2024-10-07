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
 * Prints an instance of mod_questiongenerator.
 *
 * @package     mod_questiongenerator
 * @copyright   2024 Tarekul Islam <tarekul.islam@brainstation-23.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$q = optional_param('q', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('questiongenerator', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('questiongenerator', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('questiongenerator', ['id' => $q], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('questiongenerator', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/questiongenerator/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->requires->js_call_amd('mod_questiongenerator/prompt_handling', 'promptHandling', ['cmid' => $cm->id]);
$PAGE->requires->css('/mod/questiongenerator/css/style.css');

echo $OUTPUT->header();

if (has_capability('mod/questiongenerator:attemptquiz', $context) && !is_siteadmin()) {
    $quiz = $DB->get_record('qg_quiz', ['cmid' => $cm->id, 'state' => 1]);

    $attempt = $DB->get_record('qg_quiz_attempts', ['quiz' => isset($quiz->id) ? $quiz->id : '', 'userid' => $USER->id]);
    if (isset($attempt->status) && $attempt->status == 1) {
        echo $OUTPUT->render_from_template('mod_questiongenerator/alreadysubmitted', []);
    } else {
        echo $OUTPUT->render_from_template('mod_questiongenerator/attempt_quiz',
                    ['url' => new moodle_url('/mod/questiongenerator/attempt.php',
                    ['id' => $cm->id]),
                    'text' => isset($attempt->status) && $attempt->status == 0 ? 'Continue' : "Attempt Quiz",
                    'quiz_title' => isset($quiz->quiz_title) ? $quiz->quiz_title : '']);
    }

} else {
    echo $OUTPUT->render_from_template('mod_questiongenerator/view', $context);
}


echo $OUTPUT->footer();
