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
 * TODO describe module questiongenerator
 *
 * @module     mod_questiongenerator/questiondifficulty
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax'], function($, Ajax) {
    return {
        init: function() {
            $('.check-difficulty').on('click', function() {
                var categoryid = $(this).val();
                
                var request = {
                    methodname: 'mod_questiongenerator_get_generated_questions',
                    args: {categoryid: categoryid},
                };

                Ajax.call([request])[0].done(function(response) {
                    var tableBody = $('#questionTable tbody');
                    tableBody.empty();

                    response.forEach(function(question) {
                        var row = '<tr>' +
                                  '<td>' + question.question + '</td>' +
                                  '<td>' + question.options + '</td>' +
                                  '<td>' + question.answer + '</td>' +
                                  '<td><button class="btn btn-primary check-difficulty">Check Difficulty</button></td>' +
                                  '</tr>';
                        tableBody.append(row);
                    });
                }).fail(function(error) {
                    console.log('Error fetching questions:', error);
                });
            });
        }
    };
});

