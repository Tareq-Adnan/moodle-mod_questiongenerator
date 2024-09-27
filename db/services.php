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
 * External functions and service declaration for Questions Generator
 *
 * Documentation: {@link https://moodledev.io/docs/apis/subsystems/external/description}
 *
 * @package    mod_questiongenerator
 * @category   webservice
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_questiongenerator_submit_prompts' => [
        'classname'     => 'mod_questiongenerator_external',
        'methodname'    => 'submit_prompts',
        'description'   => 'Submit Prompts for generating question',
        'type'          => 'read',
        'ajax'          => true,
        'capabilities'  => 'mod/questiongenerator:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ],'mod_questiongenerator_create_question_category' => [
        'classname'     => 'mod_questiongenerator_external',
        'methodname'    => 'create_question_category',
        'description'   => 'Create a Question Category',
        'type'          => 'write',
        'ajax'          => true,
        'capabilities'  => 'mod/questiongenerator:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ],'mod_questiongenerator_get_questions_categories' => [
        'classname'     => 'mod_questiongenerator_external',
        'methodname'    => 'get_questions_categories',
        'description'   => 'Get All the Questions Categories',
        'type'          => 'read',
        'ajax'          => true,
        'capabilities'  => 'mod/questiongenerator:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ],'mod_questiongenerator_save_generated_questions' => [
        'classname'     => 'mod_questiongenerator_external',
        'methodname'    => 'save_generated_questions',
        'description'   => 'Save AI generated Questions',
        'type'          => 'write',
        'ajax'          => true,
        'capabilities'  => 'mod/questiongenerator:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ],'mod_questiongenerator_get_generated_questions' => [
        'classname'     => 'mod_questiongenerator_external',
        'methodname'    => 'get_generated_questions',
        'description'   => 'Get All saved question by category',
        'type'          => 'read',
        'ajax'          => true,
        'capabilities'  => 'mod/questiongenerator:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ],'mod_questiongenerator_check_dificulty_level' => [
        'classname'     => 'mod_questiongenerator_external',
        'methodname'    => 'check_dificulty_level',
        'description'   => 'Submit Prompts for generating question',
        'type'          => 'read',
        'ajax'          => true,
        'capabilities'  => 'mod/questiongenerator:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ],'mod_questiongenerator_create_quiz' => [
        'classname'     => 'mod_questiongenerator_external',
        'methodname'    => 'create_quiz',
        'description'   => 'Create Quiz',
        'type'          => 'write',
        'ajax'          => true,
        'capabilities'  => 'mod/questiongenerator:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ],'mod_questiongenerator_get_quizes' => [
        'classname'     => 'mod_questiongenerator_external',
        'methodname'    => 'get_quiz',
        'description'   => 'get quiz list',
        'type'          => 'read',
        'ajax'          => true,
        'capabilities'  => 'mod/questiongenerator:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ],'mod_questiongenerator_start_quiz' => [
        'classname'     => 'mod_questiongenerator_external',
        'methodname'    => 'start_quiz',
        'description'   => 'Start Quiz',
        'type'          => 'read',
        'ajax'          => true,
        'capabilities'  => 'mod/questiongenerator:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ],'mod_questiongenerator_attempt_quiz' => [
        'classname'     => 'mod_questiongenerator_external',
        'methodname'    => 'attempt_quiz',
        'description'   => 'Attempt a quiz',
        'type'          => 'write',
        'ajax'          => true,
        'capabilities'  => 'mod/questiongenerator:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ],'mod_questiongenerator_end_quiz' => [
        'classname'     => 'mod_questiongenerator_external',
        'methodname'    => 'end_quiz',
        'description'   => 'Submit Quiz',
        'type'          => 'write',
        'ajax'          => true,
        'capabilities'  => 'mod/questiongenerator:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ],'mod_questiongenerator_get_grades' => [
        'classname'     => 'mod_questiongenerator_external',
        'methodname'    => 'get_grades',
        'description'   => 'Submit Prompts for generating question',
        'type'          => 'read',
        'ajax'          => true,
        'capabilities'  => 'mod/questiongenerator:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ],'mod_questiongenerator_update_quiz_state' => [
        'classname'     => 'mod_questiongenerator_external',
        'methodname'    => 'update_quiz_state',
        'description'   => 'Update Quiz State',
        'type'          => 'Write',
        'ajax'          => true,
        'capabilities'  => 'mod/questiongenerator:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ]
];
