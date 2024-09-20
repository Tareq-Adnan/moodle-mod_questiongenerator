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
 * Class external
 *
 * @package    mod_questiongenerator
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/questiongenerator/lib.php');
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;



class mod_questiongenerator_external extends external_api {

    public static function submit_prompts_parameters()
    {
        return new external_function_parameters(
            array(
                'prompt' => new external_value(PARAM_TEXT, 'Questions Prompt',VALUE_REQUIRED)
            )
        );
    }

    public static function submit_prompts($prompt) {

        $content = mod_qg_generate($prompt);

        return $content;
    }

    public static function submit_prompts_returns() {
        return new external_value(PARAM_TEXT, 'AI Response ',VALUE_DEFAULT,[]);
    }

     /**
     * Parameters for creating a question category.
     * 
     * @return external_function_parameters
     */
    public static function create_question_category_parameters() {
        return new external_function_parameters(
            array('categoryname' => new external_value(PARAM_TEXT, 'The name of the category'))
        );
    }

    /**
     * Creates a new question category.
     * 
     * @param string $categoryname Name of the question category.
     * @return array Status of the operation.
     * @throws invalid_parameter_exception If parameters are invalid.
     */
    public static function create_question_category($categoryname) {
        global $DB;

        // Validate parameters.
        $params = self::validate_parameters(self::create_question_category_parameters(), array('categoryname' => $categoryname));

        // Insert the new category.
        $record = new stdClass();
        $record->name = $params['categoryname'];
        $record->timecreated = time();

        $DB->insert_record('qg_categories', $record);

        return ['status' => 'Category created successfully'];
    }

    /**
     * Returns the structure for create_question_category's return value.
     * 
     * @return external_single_structure
     */
    public static function create_question_category_returns() {
        return new external_single_structure(
            array('status' => new external_value(PARAM_TEXT, 'Status of the operation'))
        );
    }

    /**
     * Parameters for getting all question categories.
     * 
     * @return external_function_parameters
     */
    public static function get_questions_categories_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Gets all question categories.
     * 
     * @return array List of question categories.
     * @throws dml_exception If database query fails.
     */
    public static function get_questions_categories() {
        global $DB;

        // Fetch all categories.
        $categories = $DB->get_records('qg_categories');

        $result = [];
        foreach ($categories as $category) {
            $result[] = ['id' => $category->id, 'name' => $category->name];
        }

        return $result;
    }

    /**
     * Returns the structure for get_questions_categories's return value.
     * 
     * @return external_multiple_structure
     */
    public static function get_questions_categories_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Category ID'),
                    'name' => new external_value(PARAM_TEXT, 'Category name')
                )
            )
        );
    }

    /**
     * Parameters for saving generated questions.
     * 
     * @return external_function_parameters
     */
    public static function save_generated_questions_parameters() {
        return new external_function_parameters(
            array(
                'categoryid' => new external_value(PARAM_INT, 'Category ID'),
                'questions' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'questiontext' => new external_value(PARAM_TEXT, 'The question text'),
                            'difficulty' => new external_value(PARAM_TEXT, 'The difficulty level')
                        )
                    )
                )
            )
        );
    }

    /**
     * Saves AI-generated questions to the database.
     * 
     * @param int $categoryid ID of the question category.
     * @param array $questions Array of question data.
     * @return array Status of the operation.
     * @throws invalid_parameter_exception If parameters are invalid.
     */
    public static function save_generated_questions($categoryid, $questions) {
        global $DB;

        // Validate parameters.
        $params = self::validate_parameters(self::save_generated_questions_parameters(), array(
            'categoryid' => $categoryid,
            'questions' => $questions
        ));

        // Insert each question into the database.
        foreach ($params['questions'] as $question) {
            $record = new stdClass();
            $record->categoryid = $params['categoryid'];
            $record->questiontext = $question['questiontext'];
            $record->difficulty = $question['question_level'];
            $record->timecreated = time();

            // $DB->insert_record('questiongenerator_generated_questions', $record);
        }

        return ['status' => 'Questions saved successfully'];
    }

    /**
     * Returns the structure for save_generated_questions's return value.
     * 
     * @return external_single_structure
     */
    public static function save_generated_questions_returns() {
        return new external_single_structure(
            array('status' => new external_value(PARAM_TEXT, 'Status of the operation'))
        );
    }

    /**
     * Parameters for getting generated questions by category.
     * 
     * @return external_function_parameters
     */
    public static function get_generated_questions_parameters() {
        return new external_function_parameters(
            array('categoryid' => new external_value(PARAM_INT, 'The category ID'))
        );
    }

    /**
     * Gets all saved questions for a given category.
     * 
     * @param int $categoryid ID of the category.
     * @return array List of questions.
     * @throws dml_exception If database query fails.
     */
    public static function get_generated_questions($categoryid) {
        global $DB;

        $params = self::validate_parameters(self::get_generated_questions_parameters(), array('categoryid' => $categoryid));
        $categoryid = $params['categoryid'];

        $questions = $DB->get_records('qg_questions', ['category_id' => $categoryid]);

        $questiondata = [];
        foreach ($questions as $question) {
            $questiondata[] = [
                'questionid' => $question->id,
                'question' => $question->question,
                'options' => $question->options,
                'answer' => $question->answer,
                'difficulty' => $question->question_level
            ];
        }

        return $questiondata;
    }

    /**
     * Returns the structure for get_generated_questions's return value.
     * 
     * @return external_multiple_structure
     */
    public static function get_generated_questions_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'questionid' => new external_value(PARAM_INT, 'The question ID'),
                    'question' => new external_value(PARAM_TEXT, 'The question'),
                    'options' => new external_value(PARAM_TEXT, 'Options for the question'),
                    'answer' => new external_value(PARAM_TEXT, 'The correct answer'),
                    'difficulty' => new external_value(PARAM_TEXT, 'The difficulty level'),
                )
            )
        );
    }

    /**
     * Parameters for checking the difficulty level of a generated question.
     * 
     * @return external_function_parameters
     */
    public static function check_dificulty_level_parameters() {
        return new external_function_parameters(
            array('questionid' => new external_value(PARAM_INT, 'The question ID'))
        );
    }

    /**
     * Determines the difficulty level based on the given prompt.
     * 
     * @param string $prompt The prompt text for the question.
     * @return array The calculated difficulty level.
     * @throws invalid_parameter_exception If parameters are invalid.
     */
    public static function check_dificulty_level($prompt) {
        // Validate parameters.
        $params = self::validate_parameters(self::check_dificulty_level_parameters(), array('questionid' => $questionid));
        $questionid = $params['questionid'];

        $question = $DB->get_records('qg_questions', ['id' => $questionid]);
        var_dump($question);
        die;
        // Determine difficulty level (example logic).
        $difficulty = (strlen($params['prompt']) < 50) ? 'easy' : 'hard';

        return ['difficulty' => $difficulty];
    }

    /**
     * Returns the structure for check_dificulty_level's return value.
     * 
     * @return external_single_structure
     */
    public static function check_dificulty_level_returns() {
        return new external_single_structure(
            array('difficulty' => new external_value(PARAM_TEXT, 'The difficulty level'))
        );
    }
}
