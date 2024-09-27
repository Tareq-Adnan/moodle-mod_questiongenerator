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



class mod_questiongenerator_external extends external_api
{

    public static function submit_prompts_parameters()
    {
        return new external_function_parameters(
            array(
                'prompt' => new external_value(PARAM_TEXT, 'Questions Prompt', VALUE_REQUIRED)
            )
        );
    }

    public static function submit_prompts($prompt)
    {

        $content = mod_qg_generate($prompt);

        return $content;
    }

    public static function submit_prompts_returns()
    {
        return new external_value(PARAM_TEXT, 'AI Response ', VALUE_DEFAULT, []);
    }

    /**
     * Parameters for creating a question category.
     * 
     * @return external_function_parameters
     */
    public static function create_question_category_parameters()
    {
        return new external_function_parameters(
            array(
                'cmid' => new external_value(PARAM_INT, 'The cmid'),
                'categoryname' => new external_value(PARAM_TEXT, 'The name of the category')
            )
        );
    }

    /**
     * Creates a new question category.
     * 
     * @param string $categoryname Name of the question category.
     * @return array Status of the operation.
     * @throws invalid_parameter_exception If parameters are invalid.
     */
    public static function create_question_category($cmid,$categoryname) {
        global $DB, $USER;

        // Validate parameters.
        $params = self::validate_parameters(self::create_question_category_parameters(), array('cmid' => $cmid, 'categoryname' => $categoryname));
        // Insert the new category.
        $record = new stdClass();
        $record->name = $params['categoryname'];
        $record->cmid = $params['cmid'];
        $record->userid = $USER->id;  // This will save the current user's ID
        $record->timecreated = time();
        $record->timemodified = time();

        $DB->insert_record('qg_categories', $record);

        return ['status' => 'Category created successfully'];
    }

    /**
     * Returns the structure for create_question_category's return value.
     * 
     * @return external_single_structure
     */
    public static function create_question_category_returns()
    {
        return new external_single_structure(
            array('status' => new external_value(PARAM_TEXT, 'Status of the operation'))
        );
    }

    /**
     * Parameters for getting all question categories.
     * 
     * @return external_function_parameters
     */
    public static function get_questions_categories_parameters()
    {
        return new external_function_parameters(array(
          'cmid' => new external_value(PARAM_INT, 'The cmid')
        ));
    }

    /**
     * Gets all question categories.
     * 
     * @return array List of question categories.
     * @throws dml_exception If database query fails.
     */
    public static function get_questions_categories($cmid) {
        global $DB,$USER;
        $params = self::validate_parameters(self::get_questions_categories_parameters(), array('cmid' => $cmid));
        $cmid = $params['cmid'];
        // Fetch all categories.
        $categories = $DB->get_records('qg_categories',['userid' => $USER->id,'cmid' => $cmid] );

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
    public static function get_questions_categories_returns()
    {
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
    public static function save_generated_questions_parameters()
    {
        return new external_function_parameters(
            array(
                'cmid' => new external_value(PARAM_INT, 'cmid'),

                'categoryid' => new external_value(PARAM_INT, 'Category ID'),
                // 'questions' => new external_value(PARAM_INT, 'Questions'),

                'questionData' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'question' => new external_value(PARAM_TEXT, 'The question text'),
                            'options' => new external_multiple_structure(
                                new external_value(PARAM_TEXT, 'Each option for the question')
                            ),
                            'correct_answer' => new external_value(PARAM_TEXT, 'The correct answer')
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
    public static function save_generated_questions($cmid,$categoryid, $questionData) {
        global $DB,$USER;

        // Validate parameters.
        $params = self::validate_parameters(self::save_generated_questions_parameters(), array(
            'cmid' => $cmid,
            'categoryid' => $categoryid,
            'questionData' => $questionData
        ));
        // Insert each question into the database.
        foreach ($params['questionData'] as $question) {
            $record = new stdClass();
            $record->cmid = $params['cmid'];
            $record->category_id = $params['categoryid'];
            $record->question = $question['question'];
            $record->options = serialize($question['options']);
            $record->answer = $question['correct_answer'];
            $record->question_level = '';
            $record->timecreated = time();
            $record->timemodified = time();
            $DB->insert_record('qg_questions', $record);
        }

        return ['status' => 'Questions saved successfully'];
    }

    /**
     * Returns the structure for save_generated_questions's return value.
     * 
     * @return external_single_structure
     */
    public static function save_generated_questions_returns()
    {
        return new external_single_structure(
            array('status' => new external_value(PARAM_TEXT, 'Status of the operation'))
        );
    }

    /**
     * Parameters for getting generated questions by category.
     * 
     * @return external_function_parameters
     */
    public static function get_generated_questions_parameters()
    {
        return new external_function_parameters(
            array(
                'categoryid' => new external_value(PARAM_INT, 'The category ID'),
                'cmid' => new external_value(PARAM_INT, 'The  cmid')
            )
        );
    }

    /**
     * Gets all saved questions for a given category.
     * 
     * @param int $categoryid ID of the category.
     * @return array List of questions.
     * @throws dml_exception If database query fails.
     */
    public static function get_generated_questions($categoryid,$cmid) {
        global $DB ,$USER;

        $params = self::validate_parameters(self::get_generated_questions_parameters(), array('categoryid' => $categoryid, 'cmid' => $cmid));

        $categoryid = $params['categoryid'];
        $cmid = $params['cmid'];


        $questions = $DB->get_records('qg_questions', ['category_id' => $categoryid,'cmid' => $cmid ]);

        $questiondata = [];
        foreach ($questions as $question) {
            $options = unserialize($question->options);
            $options_string = implode(", ", $options);
            $questiondata[] = [
                'questionid' => $question->id,
                'question' => $question->question,
                'options' => $options_string,
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
    public static function get_generated_questions_returns()
    {
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
    public static function check_dificulty_level_parameters()
    {
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
    public static function check_dificulty_level($questionid) {
        global $DB ,$USER; // Ensure this is declared at the top of the file

        // Validate parameters.
        // Validate parameters.
        $params = self::validate_parameters(self::check_dificulty_level_parameters(), array('questionid' => $questionid));
        $questionid = $params['questionid'];
        // Fetch the question from the database
        $question = $DB->get_records('qg_questions', ['id' => $questionid]);

        // Check if question exists
        if (!$question) {
            throw new invalid_parameter_exception('Invalid question ID');
        }
        $question_data = $question[$questionid];
        $prompt = "Question = " . $question_data->question . "Options = " . $question_data->options . "Answer = " . $question_data->answer;
        $content = mod_qg_question_difficulty($prompt);
        // Initialize an empty variable for question level
        $question_level = '';
        $content = strtolower($content);

        // Check if content contains 'easy', 'medium', or 'hard'
        if (strpos($content, 'easy') !== false) {
            $question_level = 'easy';
        } elseif (strpos($content, 'medium') !== false) {
            $question_level = 'medium';
        } elseif (strpos($content, 'hard') !== false) {
            $question_level = 'hard';
        }

        // If question level is determined, update the database
        if (!empty($question_level)) {
            $DB->update_record('qg_questions', (object) [
                'id' => $questionid,
                'question_level' => $question_level
            ]);
        }
        $question_level = ucwords($question_level);

        return $question_level;

    }

    /**
     * Returns the structure for check_dificulty_level's return value.
     * 
     * @return external_single_structure
     */
    public static function check_dificulty_level_returns()
    {
        return new external_value(PARAM_TEXT, 'AI Response ', VALUE_DEFAULT, []);

    }

    /**
     * Describes the parameters for create_quiz web service.
     *
     * @return external_function_parameters
     */
    public static function create_quiz_parameters()
    {
        return new external_function_parameters([
            'quiz_data' => new external_value(PARAM_RAW, 'Quiz data in JSON format')
        ]);
    }

    /**
     * Creates a quiz based on provided data.
     *
     * @param string $quiz_data The data for the quiz in JSON format.
     * @return array The status of the quiz creation.
     * @throws invalid_parameter_exception
     */
    public static function create_quiz($quiz_data) {
        global $DB, $USER;

        // Validate parameters.
        self::validate_parameters(self::create_quiz_parameters(), ['quiz_data' => $quiz_data]);
        $quiz_data_array = json_decode($quiz_data, true);
        if ($quiz_data_array === null) {
            throw new invalid_parameter_exception('Invalid JSON data provided');
        }
    
        // Prepare the data to insert into the quiz table
        $quizrecord = new stdClass();
        $quizrecord->cmid = $quiz_data_array['cmid']; // Passed from the client
        $quizrecord->userid = $USER->id; // Current user's ID
        $quizrecord->quiz_title = $quiz_data_array['quiz_title'];
        $quizrecord->easy = $quiz_data_array['easy_marks'];
        $quizrecord->medium = $quiz_data_array['medium_marks'];
        $quizrecord->hard = $quiz_data_array['hard_marks'];
        $quizrecord->timecreated = time();
        $quizrecord->timemodified = time();
    
        // Insert the quiz record into the database
        $quizid = $DB->insert_record('qg_quiz', $quizrecord);
        if($quizid){
            // Handle selected questions
            if (!empty($quiz_data_array['selected_questions'])) {
                foreach ($quiz_data_array['selected_questions'] as $questionid) {
                    $questionrecord = new stdClass();
                    $questionrecord->cmid = $quiz_data_array['cmid'];
                    $questionrecord->quizid = $quizid;
                    $questionrecord->questionid = $questionid;
                    $questionrecord->timecreated = time();
                    $questionrecord->timemodified = time();

                    $DB->insert_record('qg_quiz_questions', $questionrecord);
                }
            }
       
        }

        // Return the creation status.
        return ['status' => ($quizid) ? true : false];
    }

    /**
     * Describes the create_quiz return value.
     *
     * @return external_single_structure
     */
    public static function create_quiz_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Status of quiz creation'),
        ]);
    }

    /**
     * Describes the parameters for get_quiz web service.
     *
     * @return external_function_parameters
     */
    public static function get_quiz_parameters()
    {
        return new external_function_parameters([]);
    }

    /**
     * Retrieves the list of quizzes.
     *
     * @return array The list of quizzes.
     * @throws invalid_parameter_exception
     */
    public static function get_quiz()
    {
        // Your custom logic to fetch the quiz data.
        $quizzes = mod_questiongenerator_get_quizzes();

        // Return the quiz data.
        return $quizzes;
    }

    /**
     * Describes the get_quiz return value.
     *
     * @return external_multiple_structure
     */
    public static function get_quiz_returns()
    {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Quiz ID'),
                'name' => new external_value(PARAM_TEXT, 'Quiz Name'),
            ])
        );
    }

    /**
     * Describes the parameters for start_quiz web service.
     *
     * @return external_function_parameters
     */
    public static function start_quiz_parameters()
    {
        return new external_function_parameters([
            'quizid' => new external_value(PARAM_INT, 'ID of the quiz to start')
        ]);
    }

    /**
     * Starts a quiz based on the provided quiz ID.
     *
     * @param int $quizid The ID of the quiz.
     * @return array The status of starting the quiz.
     * @throws invalid_parameter_exception
     */
    public static function start_quiz($quizid)
    {
        // Validate parameters.
        self::validate_parameters(self::start_quiz_parameters(), ['quizid' => $quizid]);

        // Custom logic to start the quiz.
        $status = mod_questiongenerator_start_quiz($quizid);

        // Return the start status.
        return ['status' => $status];
    }

    /**
     * Describes the start_quiz return value.
     *
     * @return external_single_structure
     */
    public static function start_quiz_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Status of quiz start'),
        ]);
    }

    /**
     * Describes the parameters for attempt_quiz web service.
     *
     * @return external_function_parameters
     */
    public static function attempt_quiz_parameters()
    {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'ID of the course module'),
            'status' => new external_value(PARAM_TEXT, 'ID of the course module'),
        ]);
    }

    /**
     * Attempts a quiz based on the provided quiz ID and attempt data.
     *
     * @param int $quizid The ID of the quiz.
     * @param string $attempt_data The attempt data in JSON format.
     * @return array The status of quiz attempt.
     * @throws invalid_parameter_exception
     */
    public static function attempt_quiz($cmid,$status)
    {
        global $DB, $USER;
        $params = [
            'cmid' => $cmid,
            'status' => $status,
        ];
        self::validate_parameters(self::attempt_quiz_parameters(), $params);

        $sql = "SELECT * FROM {qg_quiz} WHERE state = 1 AND cmid = :cmid";
        $quiz = $DB->get_record_sql($sql, ['cmid' => $params['cmid']]);

        $exists = $DB->record_exists('qg_quiz_attempts', ['quiz' => $quiz->id, 'userid' => $USER->id, 'cmid' => $params['cmid'],'status' => 0]);

        $record = $exists ? $DB->get_record('qg_quiz_attempts', ['quiz' => $quiz->id, 'userid' => $USER->id, 'cmid' => $params['cmid'],'status' => 0]) : new stdClass();
        try {
            if ($exists && $params['status'] === 'finished') {
                $record->status = 1;
                $record->timemodified = time();
                $DB->update_record('qg_quiz_attempts', $record);
            } else if (!$exists && $params['status'] === 'start') {
                $record->userid = $USER->id;
                $record->quiz = $quiz->id;
                $record->cmid = $params['cmid'];
                $record->status = 0;
                $record->timecreated = time();
                $record->timemodified = time();
                $DB->insert_record('qg_quiz_attempts', $record);
            }
            return ['status' => true];
        } catch (Exception $e) {
            echo $e->getMessage();
            return ['status' => false];
        }


        // Return the attempt status.

    }

    /**
     * Describes the attempt_quiz return value.
     *
     * @return external_single_structure
     */
    public static function attempt_quiz_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Status of quiz attempt'),
        ]);
    }

    /**
     * Describes the parameters for end_quiz web service.
     *
     * @return external_function_parameters
     */
    public static function end_quiz_parameters()
    {
        return new external_function_parameters([
            'quiz_data' => new external_multiple_structure(
                new external_single_structure([
                    'answer' => new external_value(PARAM_INT, 'The answer for the question'),
                    'question' => new external_value(PARAM_INT, 'The question ID'),
                    'ref' => new external_value(PARAM_TEXT, 'Reference for the question'),
                    'type' => new external_value(PARAM_TEXT, 'The question type'),
                ])
            ),
            'cmid' => new external_value(PARAM_INT, 'The course module ID'),
            'quizid' => new external_value(PARAM_INT, 'The quiz ID'),
            'marks' => new external_single_structure([
                'easy' => new external_value(PARAM_INT, 'Total easy marks'),
                'medium' => new external_value(PARAM_INT, 'Total medium marks'),
                'hard' => new external_value(PARAM_INT, 'Total hard marks'),
            ])
        ]);
    }

    /**
     * Ends and submits the quiz based on the provided quiz ID.
     *
     * @param int $quizid The ID of the quiz.
     * @return array The status of quiz submission.
     * @throws invalid_parameter_exception
     */
    public static function end_quiz($quiz_data, $cmid, $quizid, $marks)
    {
        global $CFG, $USER, $DB;

        // Validate parameters.
        $params = ['quiz_data' => $quiz_data, "cmid" => $cmid, 'quizid' => $quizid, 'marks' => $marks];
        self::validate_parameters(self::end_quiz_parameters(), $params);

        $cm = get_coursemodule_from_id(null, $cmid);
        // SQL to calculate total marks
        $sql = "SELECT IFNULL(CAST(easy AS SIGNED), 0) + IFNULL(CAST(medium AS SIGNED), 0) + IFNULL(CAST(hard AS SIGNED), 0) AS total_marks
                FROM {qg_quiz}
                WHERE id = :quizid AND state = 1";

        $quizzes = $DB->get_record_sql($sql, ['quizid' => $quizid]);
        $totalmark = $quizzes->total_marks;

        $correct = 0;
        $wrong = 0;
        $total = 0;

        $records = [];
        foreach ($quiz_data as $data) {
            $grade = 0;
            if ($data['answer'] === intval(base64_decode($data['ref']))) {
                $correct += 1;
                match ($data['type']) {
                    'easy' => $grade = $marks['easy'],
                    'medium' => $grade = $marks['medium'],
                    'hard' => $grade = $marks['hard'],
                };
                $total += $grade;
            } else {
                $wrong += 1;
            }

            $records[] = [
                'userid' => $USER->id,
                'cmid' => $cmid,
                'quizid' => $quizid,
                'questionid' => $data['question'],
                'grade' => $grade,
                'timecreated' => time(),
                'timemodified' => time(),
            ];
        }

        // Insert all grades into the database
        $DB->insert_records('qg_grades', $records);

        return ['status' => true, 'redirect' => "$CFG->wwwroot/course/view.php?id=$cm->course", 'correct_ans' => $correct, 'wrong' => $wrong, 'total' => $total, 'rawmark' => $totalmark];
    }

    /**
     * Describes the end_quiz return value.
     *
     * @return external_single_structure
     */
    public static function end_quiz_returns()
    {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Status of quiz creation'),
            'redirect' => new external_value(PARAM_TEXT, 'Redirect URL'),
            'correct_ans' => new external_value(PARAM_INT, 'Number of correct answers'),
            'wrong' => new external_value(PARAM_INT, 'Number of wrong answers'),
            'total' => new external_value(PARAM_INT, 'Total marks obtained'),
            'rawmark' => new external_value(PARAM_INT, 'Raw total marks')
        ]);
    }

    /**
     * Describes the parameters for get_grades web service.
     *
     * @return external_function_parameters
     */
    public static function get_grades_parameters()
    {
        return new external_function_parameters([
            'quizid' => new external_value(PARAM_INT, 'ID of the quiz')
        ]);
    }

    /**
     * Retrieves the grades for the specified quiz.
     *
     * @param int $quizid The ID of the quiz.
     * @return array List of grades for the quiz.
     * @throws invalid_parameter_exception
     */
    public static function get_grades($quizid)
    {
        // Validate parameters.
        self::validate_parameters(self::get_grades_parameters(), ['quizid' => $quizid]);

        // Custom logic to get quiz grades.
        $grades = mod_questiongenerator_get_quiz_grades($quizid);

        // Return the grades.
        return $grades;
    }

    /**
     * Describes the get_grades return value.
     *
     * @return external_multiple_structure
     */
    public static function get_grades_returns()
    {
        return new external_multiple_structure(
            new external_single_structure([
                'userid' => new external_value(PARAM_INT, 'User ID'),
                'grade' => new external_value(PARAM_FLOAT, 'User grade'),
            ])
        );
    }
}
