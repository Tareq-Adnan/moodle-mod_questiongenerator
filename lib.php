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
 * Library of interface functions and constants.
 *
 * @package     mod_questiongenerator
 * @copyright   2024 Tarekul Islam <tarekul.islam@brainstation-23.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */

define('QUESTIONGENERATOR_GRADEHIGHEST', '1');
define('QUESTIONGENERATOR_GRADEAVERAGE', '2');
define('QUESTIONGENERATOR_ATTEMPTFIRST', '3');
define('QUESTIONGENERATOR_ATTEMPTLAST', '4');

/**
 * Method questiongenerator_supports
 *
 * @param $feature $feature [explicite description]
 *
 * @return bool | null
 */
function questiongenerator_supports($feature) {
    switch ($feature) {
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_questiongenerator into the database.
 *
 * Given an object containing all the necessary data, this function will create a new instance and return the id.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_questiongenerator_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function questiongenerator_add_instance($data, $mform = null) {
    global $DB;

    $data->timecreated = time();

    // Insert the new instance into the 'questiongenerator' table.
    $data->id = $DB->insert_record('questiongenerator', $data);
    $data->instance = $data->id;
    questiongenerator_grade_item_update($data);

    // Return the new instance ID.
    return $data->id;
}
/**
 * Method questiongenerator_get_grading_options
 *
 * @return array
 */
function questiongenerator_get_grading_options() {
    return [
        QUESTIONGENERATOR_GRADEHIGHEST => get_string('gradehighest', 'mod_questiongenerator'),
        QUESTIONGENERATOR_GRADEAVERAGE => get_string('gradeaverage', 'mod_questiongenerator'),
        QUESTIONGENERATOR_ATTEMPTFIRST => get_string('attemptfirst', 'mod_questiongenerator'),
        QUESTIONGENERATOR_ATTEMPTLAST => get_string('attemptlast', 'mod_questiongenerator'),
    ];
}

/**
 * Updates an instance of the mod_questiongenerator in the database.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_questiongenerator_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function questiongenerator_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;
    questiongenerator_grade_item_update($moduleinstance);
    return $DB->update_record('questiongenerator', $moduleinstance);
}

/**
 * Removes an instance of the mod_questiongenerator from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function questiongenerator_delete_instance($id) {
    global $DB;

    if (!$DB->record_exists('questiongenerator', ['id' => $id])) {
        return false;
    }

    $DB->delete_records('questiongenerator', ['id' => $id]);

    return true;
}

/**
 * Checks if a given scale is used by the instance of mod_questiongenerator.
 *
 * @param int $moduleinstanceid ID of an instance of this module.
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by the given mod_questiongenerator instance.
 */
function questiongenerator_scale_used($moduleinstanceid, $scaleid) {
    global $DB;

    return $scaleid && $DB->record_exists('questiongenerator', ['id' => $moduleinstanceid, 'grade' => -$scaleid]);
}

/**
 * Checks if scale is being used by any instance of mod_questiongenerator.
 *
 * @param int $scaleid ID of the scale.
 * @return bool True if the scale is used by any mod_questiongenerator instance.
 */
function questiongenerator_scale_used_anywhere($scaleid) {
    global $DB;

    return $scaleid && $DB->record_exists('questiongenerator', ['grade' => -$scaleid]);
}

/**
 * Creates or updates grade item for the given mod_questiongenerator instance.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param bool $reset Reset grades in the gradebook.
 */
function questiongenerator_grade_item_update($moduleinstance, $grades = null) {
    $params = ['itemname' => $moduleinstance->name, 'idnumber' => $moduleinstance->cmidnumber];

    $gradefeedbackenabled = false;

    if (isset($moduleinstance->gradefeedbackenabled)) {
        $gradefeedbackenabled = $moduleinstance->gradefeedbackenabled;
    }

    if ($moduleinstance->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax'] = $moduleinstance->grade;
        $params['grademin'] = 0;

    } else if ($moduleinstance->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid'] = -$moduleinstance->grade;

    } else if ($gradefeedbackenabled) {
        // ...$moduleinstance->grade == 0 and feedback enabled.
        $params['gradetype'] = GRADE_TYPE_TEXT;
    } else {
        // ...$moduleinstance->grade == 0 and no feedback enabled.
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update(
        'mod/questiongenerator',
        $moduleinstance->course,
        'mod',
        'questiongenerator',
        $moduleinstance->instance,
        0,
        $grades,
        $params
    );
}

/**
 * Deletes grade item for the given mod_questiongenerator instance.
 *
 * @param stdClass $moduleinstance Instance object.
 * @return int.
 */
function questiongenerator_grade_item_delete($moduleinstance) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    return grade_update('mod/questiongenerator', $moduleinstance->course, 'mod', 'questiongenerator',
            $moduleinstance->id, 0, null, ['deleted' => 1]);
}

/**
 * Updates mod_questiongenerator grades in the gradebook.
 *
 * @param stdClass $moduleinstance Instance object with extra cmidnumber and modname property.
 * @param int $userid Update grade of a specific user only, 0 means all participants.
 */
function questiongenerator_update_grades($moduleinstance, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');

    if ($moduleinstance->grade == 0 || $moduleinstance->practice) {

        questiongenerator_grade_item_update($moduleinstance);

    } else if ($grades = questiongenerator_get_user_grades($moduleinstance, $userid)) {
        questiongenerator_grade_item_update($moduleinstance, $grades);

    } else if ($userid && $nullifnone) {
        $grade = new stdClass();
        $grade->userid = $userid;
        $grade->rawgrade = null;
        questiongenerator_grade_item_update($moduleinstance, $grade);

    } else {
        questiongenerator_grade_item_update($moduleinstance);
    }
}
/**
 * Method questiongenerator_get_user_grades
 *
 * @param $moduleinstance $moduleinstance [explicite description]
 * @param $userid $userid [explicite description]
 *
 * @return object | array
 */
function questiongenerator_get_user_grades($moduleinstance, $userid = 0) {
    global $CFG, $DB;
    $sql = "SELECT g.userid AS userid, g.timemodified AS dategraded,
                   SUM(g.grade) AS rawgrade, g.timecreated AS datesubmitted
              FROM {qg_grades} g
            LEFT JOIN {qg_quiz} q ON q.id = g.quizid
            LEFT JOIN {qg_quiz_attempts} qa ON qa.quiz = g.quizid AND qa.status = 1
            WHERE g.userid = :userid AND g.cmid = :cmid
            GROUP BY g.userid, g.quizid, q.quiz_title";
    $data = $DB->get_records_sql($sql, ['userid' => $userid, 'cmid' => $moduleinstance->cmid]);
    return $data;
}
/**
 * Generate questions using an external API.
 *
 * @param string $prompt Text input to generate questions from.
 * @return string JSON response with questions and options.
 */
function mod_qg_generate($prompt) {
    $apikey = get_config('mod_questiongenerator', 'apiKey');
    $url = get_config('mod_questiongenerator', 'endpoint');

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apikey",
        "Content-Type: application/json",
    ]);

    $jsonpattern = '{
        "question": "What is CTE?",
        "options": ["A type of cancer", "A viral infection", "A brain tumor", "A degenerative brain disorder"],
        "correct_answer": "A brain tumor"
    }';

    $data = [
        "messages" => [
            [
                "role" => "user",
                "content" => "ONLY generate 5 multiple-choice questions from the text in JSON format.
                                Each question should have 4 options and a 'correct_answer'. Text: " . $prompt,
            ],
        ],
        "max_tokens" => 500,
        "stream" => false,
    ];

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
        curl_close($ch);
        exit;
    }

    $responsedata = json_decode($response, true);
    $content = $responsedata['choices'][0]['message']['content'] ?? '';
    curl_close($ch);

    return $content;
}

/**
 * Analyze question difficulty using external API.
 *
 * @param string $prompt Question to analyze.
 * @return string Difficulty level (easy, medium, or hard).
 */
function mod_qg_question_difficulty($prompt) {
    $apikey = get_config('mod_questiongenerator', 'apiKey');
    $url = get_config('mod_questiongenerator', 'endpoint');

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apikey",
        "Content-Type: application/json",
    ]);

    $data = [
        "messages" => [
            [
                "role" => "user",
                "content" => "Analyze the question and respond with its difficulty
                              level (easy, medium, hard). Question: " . $prompt,
            ],
        ],
        "max_tokens" => 500,
        "stream" => false,
    ];

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
        curl_close($ch);
        exit;
    }

    $responsedata = json_decode($response, true);
    $content = $responsedata['choices'][0]['message']['content'] ?? '';
    curl_close($ch);

    return $content;
}

/**
 * Method questiongenerator_extend_settings_navigation
 *
 * @param settings_navigation $settings [explicite description]
 * @param navigation_node $questiongeneratornode [explicite description]
 *
 * @return void
 */
function questiongenerator_extend_settings_navigation(settings_navigation $settings, navigation_node $questiongeneratornode) {
    global $DB, $USER;
    $context = context_module::instance($settings->get_page()->cm->id);
    $sql = "SELECT quiz FROM {qg_quiz_attempts} WHERE userid = :userid AND cmid = :cmid AND status = 1";
    $data = $DB->get_record_sql($sql, ['userid' => $USER->id, 'cmid' => $settings->get_page()->cm->id]);
    if (has_capability('mod/questiongenerator:attemptquiz', $context) && !is_siteadmin()) {
        $reportnode = $questiongeneratornode->add(
            get_string('qggrade', 'questiongenerator'),
            new moodle_url('/mod/questiongenerator/quizresult.php', ['id' => $settings->get_page()->cm->id, 'user' => $USER->id,
                            'quiz' => isset($data->quiz) ? $data->quiz : 0])
        );
    } else {
        $reportnode = $questiongeneratornode->add(
            get_string('questionbank', 'questiongenerator'),
            new moodle_url('/mod/questiongenerator/questionbank.php', ['id' => $settings->get_page()->cm->id])
        );
        $reportnode = $questiongeneratornode->add(
            get_string('qggrade', 'questiongenerator'),
            new moodle_url('/mod/questiongenerator/grades.php', ['id' => $settings->get_page()->cm->id])
        );
    }
}
