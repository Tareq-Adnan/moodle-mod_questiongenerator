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


 function mod_questiongenerator_extends_navigation(\settings_navigation $settingsnav, \navigation_node $modulenode) {
    global $CFG, $USER, $DB;

    // Ensure the current course module is available
    if ($modulenode->key !== 'modulesettings') {
        return;
    }

    // Get the course module ID
    $cmid = optional_param('id', 0, PARAM_INT); // Course module ID
    if (!$cmid) {
        return; // Exit if there's no valid course module ID
    }

    // Get the context of the course module
    $context = \context_module::instance($cmid);

    // Check if the user has the required capability (e.g., to manage activities)
    if (!has_capability('mod/questiongenerator:manage', $context)) {
        return; // Exit if the user doesn't have the required capability
    }

    // Add a custom navigation node under the module's settings
    $url = new moodle_url('/mod/questiongenerator/view.php', array('id' => $cmid));
    $modulenode->add(
        get_string('customsettings', 'questiongenerator'), // Display name for the link
        $url, // URL to navigate to
        navigation_node::TYPE_SETTING, // Type of node
        null, // Shortname (optional)
        'mod_questiongenerator_customsettings' // Unique key
    );

    // Add another custom link, for example to generate questions
    $url_generate = new moodle_url('/mod/questiongenerator/generate.php', array('id' => $cmid));
    $modulenode->add(
        get_string('generatequestions', 'questiongenerator'), // Display name for the link
        $url_generate, // URL for generating questions
        navigation_node::TYPE_SETTING, // Type of node
        null, // Shortname (optional)
        'mod_questiongenerator_generatequestions' // Unique key
    );
}




/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function questiongenerator_supports($feature) {
    switch ($feature) {
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
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_questiongenerator_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function questiongenerator_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();

    $id = $DB->insert_record('questiongenerator', $moduleinstance);

    return $id;
}

/**
 * Updates an instance of the mod_questiongenerator in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_questiongenerator_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function questiongenerator_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

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

    $exists = $DB->get_record('questiongenerator', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('questiongenerator', array('id' => $id));

    return true;
}

 function mod_qg_generate($prompt) {

    $apiKey = get_config('mod_questiongenerator', 'apiKey');
    $url = get_config('mod_questiongenerator', 'endpoint');

    // Initialize cURL
    $ch = curl_init($url);
    $large_text = file_get_contents('sample.txt');

    // Set the cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ]);

    // JSON pattern example
    $json_pattern = '{
        "question": "What is CTE?",
        "options": [
            "A type of cancer",
            "A viral infection",
            "A brain tumor",
            "A degenerative brain disorder"
        ],
        "correct_answer": "A brain tumor"
    }';

    // JSON data to be sent
    $data = [
        "messages" => [
            [
                "role" => "user",
                "content" => "Only json response, no other texts.
                ONLY generate 5 multiple-choice QUESTIONS from the following text in a JSON format. Each question object should have exactly 4 options and a 'correct_answer' field and don't add any extra texts. Strictly follow this format: " . $json_pattern . " Text: \n\n" . $prompt
            ]
        ],
        "max_tokens" => 500,
        "stream" => false
    ];

    // Encode data to JSON
    $jsonData = json_encode($data);

    // Set POST data
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

    // Execute the request
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
        curl_close($ch);
        exit;
    }

    // Decode the API response
    $response_data = json_decode($response, true);

    // Extract content from response
    $content = $response_data['choices'][0]['message']['content'] ?? '';
    $contentArray = json_decode($content, true); // Decodes JSON string into an associative array

    // Check if content is valid JSON
    $isCorrectFormat = false;
        if (!empty($contentArray) &&
            isset($contentArray[0]['question']) && 
        isset($contentArray[0]['options']) && 
        isset($contentArray[0]['correct_answer'])) {
        
        $isCorrectFormat = true;

    }

    // Output the response if it matches the expected format
    if ($isCorrectFormat) {
        return $content;    
       
    } else {
      
        // Prepare correction prompt
        $correction_prompt = 'The response format is incorrect. Please adjust the response to match the following expected format: ' .
                            json_encode([
                                [
                                    "question" => "Example question?",
                                    "options" => [
                                        "Option 1",
                                        "Option 2",
                                        "Option 3",
                                        "Option 4"
                                    ],
                                    "correct_answer" => "Option 1"
                                ]
                            ], JSON_PRETTY_PRINT) .
                            ' Here is the actual response received: ' . htmlspecialchars($content);

        // Create a new request to guide the API with the corrected format
        $correction_data = [
            "messages" => [
                [
                    "role" => "user",
                    "content" => "Modify the response to fit the following format: " . $correction_prompt
                ]
            ],
            "max_tokens" => 500,
            "stream" => false
        ];

        $correction_jsonData = json_encode($correction_data);

        // Set POST data for correction request
        curl_setopt($ch, CURLOPT_POSTFIELDS, $correction_jsonData);

        // Execute the correction request
        $correction_response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
        } else {
            // Decode and display the correction response
            $correction_response_data = json_decode($correction_response, true);
            $correction_content = $correction_response_data['choices'][0]['message']['content'] ?? '';
            $correction_content_array = json_decode($correction_content, true);
            return $correction_content;    
         
        }
    }

    // Close cURL session
    curl_close($ch);

}