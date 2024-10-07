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



/**
 * mod_questiongenerator_external
 */
class mod_questiongenerator_external extends external_api {

    /**
     * Returns the description of the method parameters for submit_prompts.
     *
     * @return external_function_parameters
     *   External function parameters.
     */
    public static function submit_prompts_parameters() {
        return new external_function_parameters(
            [
                'prompt' => new external_value(PARAM_TEXT, 'Questions Prompt', VALUE_REQUIRED),
            ]
        );
    }

    /**
     * Submits the AI question prompt and returns the generated content.
     *
     * @param string $prompt
     *   The AI question prompt.
     *
     * @return string
     *   The AI-generated content.
     */
    public static function submit_prompts($prompt) {
        $content = mod_qg_generate($prompt);
        return $content;
    }

    /**
     * Returns the structure of submit_prompts's return value.
     *
     * @return external_value
     *   External value.
     */
    public static function submit_prompts_returns() {
        return new external_value(PARAM_TEXT, 'AI Response', VALUE_DEFAULT, []);
    }

    /**
     * Returns the description of the method parameters for create_question_category.
     *
     * @return external_function_parameters
     *   External function parameters.
     */
    public static function create_question_category_parameters() {
        return new external_function_parameters(
            [
                'cmid' => new external_value(PARAM_INT, 'The course module ID (cmid)'),
                'categoryname' => new external_value(PARAM_TEXT, 'The name of the category'),
            ]
        );
    }

    /**
     * Creates a new question category.
     *
     * @param int $cmid
     *   The course module ID.
     * @param string $categoryname
     *   The name of the question category.
     *
     * @return array
     *   Status of the operation.
     * @throws invalid_parameter_exception
     *   If parameters are invalid.
     */
    public static function create_question_category($cmid, $categoryname) {
        global $DB, $USER;

        // Validate parameters.
        $params = self::validate_parameters(self::create_question_category_parameters(), [
            'cmid' => $cmid,
            'categoryname' => $categoryname,
        ]);
        $cm = get_coursemodule_from_id('questiongenerator', $params['cmid'], 0, false, MUST_EXIST);
        // Insert the new category.
        $record = new stdClass();
        $record->name = $params['categoryname'];
        $record->cmid = $params['cmid'];
        $record->qgid = $cm->instance;
        $record->userid = $USER->id; // Save the current user's ID.
        $record->timecreated = time();
        $record->timemodified = time();

        $DB->insert_record('qg_categories', $record);

        return ['status' => 'Category created successfully'];
    }

    /**
     * Returns the structure of create_question_category's return value.
     *
     * @return external_single_structure
     *   External single structure.
     */
    public static function create_question_category_returns() {
        return new external_single_structure(
            ['status' => new external_value(PARAM_TEXT, 'Status of the operation')]
        );
    }

    /**
     * Returns the description of the method parameters for get_questions_categories.
     *
     * @return external_function_parameters
     *   External function parameters.
     */
    public static function get_questions_categories_parameters() {
        return new external_function_parameters(
            ['cmid' => new external_value(PARAM_INT, 'The course module ID (cmid)')]
        );
    }

    /**
     * Gets all question categories.
     *
     * @param int $cmid
     *   The course module ID.
     *
     * @return array
     *   List of question categories.
     * @throws dml_exception
     *   If database query fails.
     */
    public static function get_questions_categories($cmid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::get_questions_categories_parameters(), [
            'cmid' => $cmid,
        ]);

        // Fetch all categories.
        $isadmin = is_siteadmin($USER->id);
        if ($isadmin) {
            $categories = $DB->get_records('qg_categories', ['cmid' => $params['cmid']]);
        } else {
            $categories = $DB->get_records('qg_categories', [
                'userid' => $USER->id,
                'cmid' => $params['cmid'],
            ]);
        }

        $result = [];
        foreach ($categories as $category) {
            $result[] = ['id' => $category->id, 'name' => $category->name];
        }

        return $result;
    }

    /**
     * Returns the structure of get_questions_categories's return value.
     *
     * @return external_multiple_structure
     *   External multiple structure.
     */
    public static function get_questions_categories_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'Category ID'),
                    'name' => new external_value(PARAM_TEXT, 'Category name'),
                ]
            )
        );
    }

    /**
     * Returns the description of the method parameters for save_generated_questions.
     *
     * @return external_function_parameters
     *   External function parameters.
     */
    public static function save_generated_questions_parameters() {
        return new external_function_parameters(
            [
                'cmid' => new external_value(PARAM_INT, 'The course module ID (cmid)'),
                'categoryid' => new external_value(PARAM_INT, 'The question category ID'),
                'questionData' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'question' => new external_value(PARAM_TEXT, 'The question text'),
                            'options' => new external_multiple_structure(
                                new external_value(PARAM_TEXT, 'Each option for the question')
                            ),
                            'correct_answer' => new external_value(PARAM_TEXT, 'The correct answer'),
                        ]
                    )
                        ),
                ]
        );
    }

    /**
     * Saves AI-generated questions to the database.
     *
     * @param int $cmid
     *   The course module ID.
     * @param int $categoryid
     *   ID of the question category.
     * @param array $questiondata
     *   Array of question data.
     *
     * @return array
     *   Status of the operation.
     * @throws invalid_parameter_exception
     *   If parameters are invalid.
     */
    public static function save_generated_questions($cmid, $categoryid, $questiondata) {
        global $DB, $USER;

        // Validate parameters.
        $params = self::validate_parameters(self::save_generated_questions_parameters(), [
            'cmid' => $cmid,
            'categoryid' => $categoryid,
            'questionData' => $questiondata,
        ]);
        $cm = get_coursemodule_from_id('questiongenerator', $params['cmid'], 0, false, MUST_EXIST);
        // Insert each question into the database.
        foreach ($params['questionData'] as $question) {
            $record = new stdClass();
            $record->cmid = $params['cmid'];
            $record->qgid = $cm->instance;
            $record->category_id = $params['categoryid'];
            $record->question = $question['question'];
            $record->options = serialize($question['options']);
            $record->answer = $question['correct_answer'];
            $record->question_level = ''; // Optional field for difficulty level.
            $record->timecreated = time();
            $record->timemodified = time();

            $DB->insert_record('qg_questions', $record);
        }

        return ['status' => 'Questions saved successfully'];
    }

    /**
     * Returns the structure of save_generated_questions's return value.
     *
     * @return external_single_structure
     *   External single structure.
     */
    public static function save_generated_questions_returns() {
        return new external_single_structure(
            ['status' => new external_value(PARAM_TEXT, 'Status of the operation')]
        );
    }

    /**
     * Returns the description of the method parameters for get_generated_questions.
     *
     * @return external_function_parameters
     *   External function parameters.
     */
    public static function get_generated_questions_parameters() {
        return new external_function_parameters(
            [
                'categoryid' => new external_value(PARAM_INT, 'The question category ID'),
                'cmid' => new external_value(PARAM_INT, 'The course module ID (cmid)'),
            ]
        );
    }

    /**
     * Gets all saved questions for a given category.
     *
     * @param int $categoryid
     *   ID of the question category.
     * @param int $cmid
     *   Course module ID.
     *
     * @return array
     *   List of questions.
     * @throws dml_exception
     *   If database query fails.
     */
    public static function get_generated_questions($categoryid, $cmid) {
        global $DB;

        $params = self::validate_parameters(self::get_generated_questions_parameters(), [
            'categoryid' => $categoryid,
            'cmid' => $cmid,
        ]);

        // Fetch questions for the given category and course module.
        $questions = $DB->get_records('qg_questions', [
            'category_id' => $params['categoryid'],
            'cmid' => $params['cmid'],
        ]);

        // Format the result.
        $questiondata = [];
        foreach ($questions as $question) {
            $options = unserialize($question->options);
            $optionsstring = implode(", ", $options);

            $questiondata[] = [
                'id' => $question->id,
                'question' => $question->question,
                'options' => $optionsstring,
                'correct_answer' => $question->answer,
                'difficulty' => $question->question_level ?? '',
            ];
        }

        return $questiondata;
    }

    /**
     * Returns the structure of get_generated_questions's return value.
     *
     * @return external_multiple_structure
     *   External multiple structure.
     */
    public static function get_generated_questions_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'Question ID'),
                    'question' => new external_value(PARAM_TEXT, 'Question text'),
                    'options' => new external_value(PARAM_TEXT, 'Question options'),
                    'correct_answer' => new external_value(PARAM_TEXT, 'Correct answer'),
                    'difficulty' => new external_value(PARAM_TEXT, 'Difficulty level'),
                ]
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
        [
            'questionid' => new external_value(PARAM_INT, 'The question ID'),
        ]
        );
    }

    /**
     * Determines the difficulty level based on the given prompt.
     *
     * @param int $questionid The ID of the question.
     * @return string The calculated difficulty level.
     * @throws invalid_parameter_exception If parameters are invalid.
     */
    public static function check_dificulty_level($questionid) {
        global $DB, $USER; // Ensure this is declared at the top of the file.

        // Validate parameters.
        $params = self::validate_parameters(self::check_dificulty_level_parameters(), ['questionid' => $questionid]);
        $questionid = $params['questionid'];

        // Fetch the question from the database.
        $question = $DB->get_records('qg_questions', ['id' => $questionid]);

        // Check if question exists.
        if (!$question) {
            throw new invalid_parameter_exception('Invalid question ID');
        }

        $questiondata = $question[$questionid];
        $prompt = "Question = " . $questiondata->question . "Options = " . $questiondata->options
                  . "Answer = " . $questiondata->answer;
        $content = mod_qg_question_difficulty($prompt);

        // Initialize an empty variable for question level.
        $questionlevel = '';
        $content = strtolower($content);

        // Check if content contains 'easy', 'medium', or 'hard'.
        if (strpos($content, 'easy') !== false) {
            $questionlevel = 'easy';
        } else if (strpos($content, 'medium') !== false) {
            $questionlevel = 'medium';
        } else if (strpos($content, 'hard') !== false) {
            $questionlevel = 'hard';
        }

        // If question level is determined, update the database.
        if (!empty($questionlevel)) {
            $DB->update_record('qg_questions', (object) [
            'id' => $questionid,
            'question_level' => $questionlevel,
            ]);
        }
        $questionlevel = ucwords($questionlevel);

        return $questionlevel;
    }

    /**
     * Returns the structure for check_dificulty_level's return value.
     *
     * @return external_value
     */
    public static function check_dificulty_level_returns() {
        return new external_value(PARAM_TEXT, 'AI Response ', VALUE_DEFAULT, []);
    }

    /**
     * Describes the parameters for create_quiz web service.
     *
     * @return external_function_parameters
     */
    public static function create_quiz_parameters() {
        return new external_function_parameters([
        'quiz_data' => new external_value(PARAM_RAW, 'Quiz data in JSON format'),
        ]);
    }

    /**
     * Creates a quiz based on provided data.
     *
     * @param string $quiz_data The data for the quiz in JSON format.
     * @return array The status of the quiz creation.
     * @throws invalid_parameter_exception
     */
    public static function create_quiz($quizdata) {
        global $DB, $USER;

        // Validate parameters.
        self::validate_parameters(self::create_quiz_parameters(), ['quiz_data' => $quizdata]);
        $quizdataarray = json_decode($quizdata, true);
        if ($quizdataarray === null) {
            throw new invalid_parameter_exception('Invalid JSON data provided');
        }
        $cm = get_coursemodule_from_id('questiongenerator', $quizdataarray['cmid'], 0, false, MUST_EXIST);
        // Prepare the data to insert into the quiz table.
        $quizrecord = new stdClass();
        $quizrecord->cmid = $quizdataarray['cmid']; // Passed from the client.
        $quizrecord->qgid = $cm->instance;
        $quizrecord->userid = $USER->id; // Current user's ID.
        $quizrecord->quiz_title = $quizdataarray['quiz_title'];
        $quizrecord->easy = $quizdataarray['easy_marks'];
        $quizrecord->medium = $quizdataarray['medium_marks'];
        $quizrecord->hard = $quizdataarray['hard_marks'];
        $quizrecord->state = 0;
        $quizrecord->timecreated = time();
        $quizrecord->timemodified = time();

        // Insert the quiz record into the database.
        $quizid = $DB->insert_record('qg_quiz', $quizrecord);
        // Initialize question counts.
        $easyquestioncount = 0;
        $mediumquestioncount = 0;
        $hardquestioncount = 0;
        if ($quizid) {
            // Handle selected questions.
            if (!empty($quizdataarray['selected_questions'])) {
                foreach ($quizdataarray['selected_questions'] as $questionid) {
                    $question = $DB->get_record('qg_questions', ['id' => $questionid]);

                    // Check the difficulty level and increment the respective counter.
                    if ($question->question_level == 'easy') {
                        $easyquestioncount++;
                    } else if ($question->question_level == 'medium') {
                        $mediumquestioncount++;
                    } else if ($question->question_level == 'hard') {
                        $hardquestioncount++;
                    }
                    $questionrecord = new stdClass();
                    $questionrecord->cmid = $quizdataarray['cmid'];
                    $questionrecord->qgid = $cm->instance;
                    $questionrecord->quizid = $quizid;
                    $questionrecord->questionid = $questionid;
                    $questionrecord->timecreated = time();
                    $questionrecord->timemodified = time();

                    $DB->insert_record('qg_quiz_questions', $questionrecord);
                }
                // Calculate total marks based on difficulty and question count.
                $totaleasymarks = $easyquestioncount * intval($quizdataarray['easy_marks']);
                $totalmediummarks = $mediumquestioncount * intval($quizdataarray['medium_marks']);
                $totalhardmarks = $hardquestioncount * intval($quizdataarray['hard_marks']);

                // Total marks for the quiz.
                $totalmarks = $totaleasymarks + $totalmediummarks + $totalhardmarks;

                $DB->update_record('qg_quiz', (object) [
                'id' => $quizid,
                'total_marks' => $totalmarks,
                ]);
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
    public static function create_quiz_returns() {
        return new external_single_structure([
        'status' => new external_value(PARAM_BOOL, 'Status of quiz creation'),
        ]);
    }

    /**
     * Describes the parameters for get_quiz web service.
     *
     * @return external_function_parameters
     */
    public static function get_quiz_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * Retrieves the list of quizzes.
     *
     * @return array The list of quizzes.
     * @throws invalid_parameter_exception
     */
    public static function get_quiz() {
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
    public static function get_quiz_returns() {
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
    public static function start_quiz_parameters() {
        return new external_function_parameters([
        'quizid' => new external_value(PARAM_INT, 'ID of the quiz to start'),
        ]);
    }

    /**
     * Starts a quiz based on the provided quiz ID.
     *
     * @param int $quizid The ID of the quiz.
     * @return array The status of starting the quiz.
     * @throws invalid_parameter_exception
     */
    public static function start_quiz($quizid) {
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
    public static function start_quiz_returns() {
        return new external_single_structure([
        'status' => new external_value(PARAM_BOOL, 'Status of quiz start'),
        ]);
    }

    /**
     * Describes the parameters for attempt_quiz web service.
     *
     * @return external_function_parameters
     */
    public static function attempt_quiz_parameters() {
        return new external_function_parameters([
        'cmid' => new external_value(PARAM_INT, 'ID of the course module'),
        'status' => new external_value(PARAM_TEXT, 'Status of the attempt'),
        ]);
    }

    /**
     * Attempts a quiz based on the provided quiz ID and attempt data.
     *
     * @param int $cmid The ID of the course module.
     * @param string $status The status of the quiz attempt.
     * @return array The status of quiz attempt.
     * @throws invalid_parameter_exception
     */
    public static function attempt_quiz($cmid, $status) {
        global $DB, $USER;
        $params = [
            'cmid' => $cmid,
            'status' => $status,
        ];
        self::validate_parameters(self::attempt_quiz_parameters(), $params);

        $sql = "SELECT * FROM {qg_quiz} WHERE state = 1 AND cmid = :cmid";
        $quiz = $DB->get_record_sql($sql, ['cmid' => $params['cmid']]);

        $exists = $DB->record_exists('qg_quiz_attempts', ['quiz' => $quiz->id, 'userid' => $USER->id,
                                    'cmid' => $params['cmid'], 'status' => 0]);

        $record = $exists ? $DB->get_record('qg_quiz_attempts', ['quiz' => $quiz->id, 'userid' => $USER->id,
                                                'cmid' => $params['cmid'], 'status' => 0]) : new stdClass();
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
    }

    /**
     * Describes the attempt_quiz return value.
     *
     * @return external_single_structure
     */
    public static function attempt_quiz_returns() {
        return new external_single_structure([
        'status' => new external_value(PARAM_BOOL, 'Status of quiz attempt'),
        ]);
    }
    /**
     * Describes the parameters for end_quiz web service.
     *
     * @return external_function_parameters
     */
    public static function end_quiz_parameters() {
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
        ]),
        ]);
    }

    /**
     * Ends and submits the quiz based on the provided quiz ID.
     *
     * @param array $quizdata The data related to the quiz answers.
     * @param int $cmid The course module ID.
     * @param int $quizid The ID of the quiz.
     * @param array $marks The marks distribution for easy, medium, and hard questions.
     * @return array The status of quiz submission.
     * @throws invalid_parameter_exception
     */
    public static function end_quiz($quizdata, $cmid, $quizid, $marks) {
        global $CFG, $USER, $DB;

        // Validate parameters.
        $params = ['quiz_data' => $quizdata, 'cmid' => $cmid, 'quizid' => $quizid, 'marks' => $marks];
        self::validate_parameters(self::end_quiz_parameters(), $params);

        $cm = get_coursemodule_from_id(null, $cmid);

        $correct = 0;
        $wrong = 0;
        $total = 0;
        $easy = 0;
        $medium = 0;
        $hard = 0;
        $questionids = [];
        $records = [];
        foreach ($quizdata as $data) {
            $grade = 0;
            if ($data['answer'] === intval(base64_decode($data['ref']))) {
                $correct += 1;

                switch ($data['type']) {
                    case 'easy':
                        $grade = $marks['easy'];
                        $easy++;
                        break;
                    case 'medium':
                        $grade = $marks['medium'];
                        $medium++;
                        break;
                    case 'hard':
                        $grade = $marks['hard'];
                        $hard++;
                        break;
                }

                $total += $grade;
            } else {
                $wrong += 1;
                switch ($data['type']) {
                    case 'easy':
                        $easy++;
                        break;
                    case 'medium':
                        $medium++;
                        break;
                    case 'hard':
                        $hard++;
                        break;
                }
            }

            $totalmark = $easy * $marks['easy'] + $medium * $marks['medium'] + $hard * $marks['hard'];
            $questionids[] = $data['questions'];
            $records[] = [
            'userid' => $USER->id,
            'cmid' => $cmid,
            'quizid' => $quizid,
            'questionid' => $data['question'],
            'answer' => $data['answer'],
            'grade' => $grade,
            'timecreated' => time(),
            'timemodified' => time(),
            ];
        }

        // Insert all grades into the database.
        $existingcount = $DB->count_records('qg_grades', [
        'userid' => $USER->id,
        'cmid' => $cmid,
        'quizid' => $quizid,
        ]);

        if ($existingcount !== count($questionids)) {
            $DB->insert_records('qg_grades', $records);
        }

        // Update grades in the question generator.
        $quizdata = new stdClass;
        $quizdata->cmid = $cmid;
        $quizdata->course = $cm->course;
        $quizdata->instance = $cm->instance;
        $quizdata->name = $cm->name;
        $quizdata->cmidnumber = 0;
        $quizdata->gradefeedbackenabled = true;
        $quizdata->grade = $totalmark;
        questiongenerator_update_grades($quizdata, $USER->id);

        return [
        'status' => true,
        'redirect' => "$CFG->wwwroot/course/view.php?id=$cm->course",
        'correct_ans' => $correct,
        'wrong' => $wrong,
        'total' => $total,
        'rawmark' => $totalmark,
        ];
    }

    /**
     * Describes the end_quiz return value.
     *
     * @return external_single_structure
     */
    public static function end_quiz_returns() {
        return new external_single_structure([
        'status' => new external_value(PARAM_BOOL, 'Status of quiz creation'),
        'redirect' => new external_value(PARAM_TEXT, 'Redirect URL'),
        'correct_ans' => new external_value(PARAM_INT, 'Number of correct answers'),
        'wrong' => new external_value(PARAM_INT, 'Number of wrong answers'),
        'total' => new external_value(PARAM_INT, 'Total marks obtained'),
        'rawmark' => new external_value(PARAM_INT, 'Raw total marks'),
        ]);
    }

    /**
     * Describes the parameters for get_grades web service.
     *
     * @return external_function_parameters
     */
    public static function get_grades_parameters() {
        return new external_function_parameters([
        'quizid' => new external_value(PARAM_INT, 'ID of the quiz'),
        ]);
    }

    /**
     * Retrieves the grades for the specified quiz.
     *
     * @param int $quizid The ID of the quiz.
     * @return array List of grades for the quiz.
     * @throws invalid_parameter_exception
     */
    public static function get_grades($quizid) {
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
    public static function get_grades_returns() {
        return new external_multiple_structure(
        new external_single_structure([
            'userid' => new external_value(PARAM_INT, 'User ID'),
            'grade' => new external_value(PARAM_FLOAT, 'User grade'),
        ])
        );
    }

    /**
     * Describes the parameters for update_quiz_state web service.
     *
     * @return external_function_parameters
     */
    public static function update_quiz_state_parameters() {
        return new external_function_parameters([
        'quizid' => new external_value(PARAM_INT, 'ID of the quiz'),
        ]);
    }

    /**
     * Updates the state of the specified quiz.
     *
     * @param int $quizid The ID of the quiz.
     * @return array Status of the state update.
     * @throws moodle_exception
     */
    public static function update_quiz_state($quizid) {
        global $DB;

        $params = self::validate_parameters(self::update_quiz_state_parameters(), ['quizid' => $quizid]);

        // Fetch the quiz from the database based on the validated quiz ID.
        $quiz = $DB->get_record('qg_quiz', ['id' => $params['quizid']], '*', IGNORE_MISSING);

        // Check if the quiz record exists.
        if (!$quiz) {
            throw new moodle_exception('invalidquizid', 'mod_questiongenerator', '', $params['quizid'], 'Quiz not found.');
        }

        // Toggle the state: if the current state is 0, set it to 1; if it's 1, set it to 0.
        $newstate = ($quiz->state == 0) ? 1 : 0;

        // Update the quiz state with the new value.
        $status = $DB->update_record('qg_quiz', [
        'id' => $params['quizid'],
        'state' => $newstate, // Update the state to the new value.
        ]);

        // Return a status response.
        return ['status' => $status, 'newstate' => $newstate];
    }

    /**
     * Describes the update_quiz_state return value.
     *
     * @return external_single_structure
     */
    public static function update_quiz_state_returns() {
        return new external_single_structure([
        'status' => new external_value(PARAM_BOOL, 'Status of quiz state update'),
        'newstate' => new external_value(PARAM_INT, 'New state of the quiz (0 or 1)'),
        ]);
    }

}
