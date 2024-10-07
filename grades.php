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
 * TODO describe file grade
 *
 * @package    mod_questiongenerator
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

require_login();

$cmid = optional_param('id', 0, PARAM_INT);
global $DB, $OUTPUT, $PAGE, $USER;

$url = new moodle_url('/mod/questiongenerator/grades.php');
$PAGE->set_url($url);
$context = context_module::instance($cmid);
$cm = get_coursemodule_from_id('questiongenerator', $cmid, 0, false, MUST_EXIST);
$PAGE->set_cm($cm);
$PAGE->set_context($context);
$PAGE->set_heading(get_string('qggrade', 'questiongenerator'));
$PAGE->set_title(get_string('qggrade', 'questiongenerator'));
$PAGE->requires->js_call_amd('mod_questiongenerator/questiongenerator', 'init', ['cmid' => $cmid]);
$PAGE->requires->css('/mod/questiongenerator/css/style.css');

$isstudent = has_capability('mod/questiongenerator:attemptquiz', $context);

echo $OUTPUT->header();

if (!$isstudent || is_siteadmin($USER->id)) {
    $sql = "SELECT u.username, u.id ,CONCAT(u.firstname, ' ',u.lastname) as fullname, u.email
              FROM {user} u
              JOIN {user_enrolments} ue ON ue.userid = u.id
              JOIN {enrol} e ON e.id = ue.enrolid
              JOIN {role_assignments} ra ON ra.userid = u.id
              JOIN {context} c ON c.id = ra.contextid
              JOIN {role} r ON r.id = ra.roleid
             WHERE e.courseid = :courseid AND r.shortname = 'student'
                    AND c.contextlevel = 50 GROUP BY u.username";
    $users = $DB->get_records_sql($sql, ['courseid' => $COURSE->id]);

    $index = 1;
    $users = array_map(function ($user) use (&$cmid, $DB, &$index) {
        $sql = "SELECT g.quizid, SUM(g.grade) AS grade, q.easy , q.medium , q.hard,
                       q.quiz_title AS quiz_title,q.cmid, q.total_marks
                  FROM {qg_grades} g
             LEFT JOIN {qg_quiz} q ON q.id = g.quizid
             LEFT JOIN {qg_quiz_attempts} qa ON qa.quiz = g.quizid AND qa.status = 1
                 WHERE g.userid = :userid AND g.cmid = :cmid
              GROUP BY g.userid, g.quizid, q.quiz_title";
        $data = $DB->get_records_sql($sql, ['userid' => $user->id, 'cmid' => $cmid]);

        $user->quizdata = array_values($data);
        $user->index = $index;
        $index++;
        return $user;
    }, $users);

    echo $OUTPUT->render_from_template('mod_questiongenerator/grade_page_teacher', ['userdata' => array_values($users)]);

} else {

    $sql = "SELECT g.userid, g.quizid, SUM(g.grade) AS grade, q.easy , q.medium , q.hard,
                   q.quiz_title AS quiz_title,q.cmid, q.total_marks
              FROM {qg_grades} g
         LEFT JOIN {qg_quiz} q ON q.id = g.quizid
         LEFT JOIN {qg_quiz_attempts} qa ON qa.quiz = g.quizid AND qa.status = 1
             WHERE g.userid = :userid AND g.cmid = :cmid
          GROUP BY g.userid, g.quizid, q.quiz_title";
    $data = $DB->get_records_sql($sql, ['userid' => $USER->id, 'cmid' => $cmid]);

    foreach (array_values($data) as $index => &$option) {
        $option->index = $index + 1;
    }

    echo $OUTPUT->render_from_template('mod_questiongenerator/grade_page', ['quizdata' => array_values($data)]);
}

echo $OUTPUT->footer();
