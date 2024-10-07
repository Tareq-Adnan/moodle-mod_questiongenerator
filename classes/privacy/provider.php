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

namespace mod_questiongenerator\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\writer;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use context;

/**
 * Class provider
 *
 * @package    mod_questiongenerator
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // This interface is required to declare stored user data for GDPR compliance.
    \core_privacy\local\metadata\provider,

    // This interface is used to retrieve and export all user data.
    \core_privacy\local\request\user_provider {

    /**
     * Returns metadata about this plugin's data storage.
     *
     * @param collection $items The initialized collection to add items to.
     * @return collection The collection with the metadata information.
     */
    public static function get_metadata(collection $items): collection {
        // Add information about the tables where the user information is stored.
        $items->add_database_table(
            'questiongenerator',
            [
                'course' => 'privacy:metadata:questiongenerator:course',
                'name' => 'privacy:metadata:questiongenerator:name',
                'intro' => 'privacy:metadata:questiongenerator:intro',
                'timemodified' => 'privacy:metadata:questiongenerator:timemodified',
            ],
            'privacy:metadata:questiongenerator'
        );

        $items->add_database_table(
            'qg_categories',
            [
                'userid' => 'privacy:metadata:qg_categories:userid',
                'name' => 'privacy:metadata:qg_categories:name',
                'timecreated' => 'privacy:metadata:qg_categories:timecreated',
                'timemodified' => 'privacy:metadata:qg_categories:timemodified',
            ],
            'privacy:metadata:qg_categories'
        );

        $items->add_database_table(
            'qg_questions',
            [
                'userid' => 'privacy:metadata:qg_questions:userid',
                'question' => 'privacy:metadata:qg_questions:question',
                'answer' => 'privacy:metadata:qg_questions:answer',
                'timecreated' => 'privacy:metadata:qg_questions:timecreated',
                'timemodified' => 'privacy:metadata:qg_questions:timemodified',
            ],
            'privacy:metadata:qg_questions'
        );

        $items->add_database_table(
            'qg_quiz',
            [
                'userid' => 'privacy:metadata:qg_quiz:userid',
                'quiz_title' => 'privacy:metadata:qg_quiz:quiz_title',
                'timecreated' => 'privacy:metadata:qg_quiz:timecreated',
                'timemodified' => 'privacy:metadata:qg_quiz:timemodified',
            ],
            'privacy:metadata:qg_quiz'
        );

        $items->add_database_table(
            'qg_quiz_attempts',
            [
                'userid' => 'privacy:metadata:qg_quiz_attempts:userid',
                'status' => 'privacy:metadata:qg_quiz_attempts:status',
                'timecreated' => 'privacy:metadata:qg_quiz_attempts:timecreated',
                'timemodified' => 'privacy:metadata:qg_quiz_attempts:timemodified',
            ],
            'privacy:metadata:qg_quiz_attempts'
        );

        return $items;
    }

    /**
     * Get the list of contexts that contain user information for the given user.
     *
     * @param int $userid The user ID to search.
     * @return \core_privacy\local\request\contextlist
     */
    public static function get_contexts_for_userid(int $userid): \core_privacy\local\request\contextlist {
        // Add logic to fetch the contexts where this user has data stored.
        $contextlist = new \core_privacy\local\request\contextlist();

        // Add SQL queries to fetch relevant context ids.
        $sql = "SELECT ctx.id
                FROM {context} ctx
                JOIN {questiongenerator} qg ON qg.course = ctx.instanceid
                WHERE qg.userid = :userid";

        $params = ['userid' => $userid];

        // Add the context instances to the list.
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export user data for the given approved contexts.
     *
     * @param approved_contextlist $contextlist List of contexts approved for export.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        // Get the context and the user id.
        $userid = $contextlist->get_user()->id;

        // Loop through each context to export data.
        foreach ($contextlist->get_contexts() as $context) {
            // Fetch user data from each relevant table, e.g., questiongenerator, qg_categories, qg_questions, etc.
            // Example: Fetch data from qg_questions for the user.
            $questions = $DB->get_records('qg_questions', ['userid' => $userid]);

            // Write data for each context using the writer object.
            writer::with_context($context)->export_data(
                ['Questions'],
                (object) ['questions' => $questions]
            );
        }
    }

    /**
     * Delete user data for the given approved contexts.
     *
     * @param approved_contextlist $contextlist List of contexts approved for deletion.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        // Add the logic to delete all user data for the specified context.
        $DB->delete_records('qg_questions', ['cmid' => $context->instanceid]);
        // Add other tables as necessary.
    }

    /**
     * Delete user data for a specific user in a context.
     *
     * @param approved_contextlist $contextlist List of contexts approved for deletion.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            // Add the logic to delete data for a specific user.
            $DB->delete_records('qg_questions', ['userid' => $userid]);
        }
    }
}
