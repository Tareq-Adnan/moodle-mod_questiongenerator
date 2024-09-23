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
 * @module     mod_questiongenerator/questiongenerator
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax'], function($, Ajax) {
    return {
        init: function() {
            $('#categoryDropdown').on('change', function() {
                var categoryid = $(this).val();
                
                var request = {
                    methodname: 'mod_questiongenerator_get_generated_questions',
                    args: {categoryid: categoryid},
                };

                Ajax.call([request])[0].done(function(response) {
                    var tableBody = $('#questionTable tbody');
                    tableBody.empty();

                    response.forEach(function(question) {
                        console.log(question);
                        var difficultyButton = '';

                        // Check if the difficulty is empty
                        if (!question.difficulty) {
                            difficultyButton = '<button data-question-id="' + question.questionid + '" class="btn btn-primary check-difficulty">Check Difficulty</button>';
                        }
                        else{
                            difficultyButton = question.difficulty.toLowerCase(); // Ensure all lowercase first
                            difficultyButton = difficultyButton.charAt(0).toUpperCase() + difficultyButton.slice(1);
                                                    }
                        var row = '<tr>' +
                                  '<td>' + question.question + '</td>' +
                                  '<td>' + question.options + '</td>' +
                                  '<td>' + question.answer + '</td>' +
                                  '<td>'+difficultyButton+'</td>' +
                                  '</tr>';
                        tableBody.append(row);
                    });
                }).fail(function(error) {
                    console.log('Error fetching questions:', error);
                });
            });
            $(document).on('click', '.check-difficulty', function() {
                // Get the data-question-id attribute
                var questionId = $(this).data('question-id');
                
                // Find the parent <td> of the clicked button
                var parentTd = $('button[data-question-id="' + questionId + '"]').closest('td');
                
                // Show Bootstrap spinner in the parent <td>
                var loaderHtml = '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>';
                parentTd.html(loaderHtml);  // Replace the button with the spinner
            
                var request = {
                    methodname: 'mod_questiongenerator_check_dificulty_level',
                    args: { questionid: questionId },
                };
            
                console.log('Request:', request);
            
                // Make the AJAX call
                Ajax.call([request])[0].done(function(response) {
                    console.log('Response:', response);
                    
                    // Replace spinner with the difficulty level text
                    if (response) {
                        parentTd.html(response); // Replace spinner with the difficulty text
                    } else {
                        parentTd.html('Difficulty not found'); // Fallback if no difficulty is returned
                    }
                }).fail(function(error) {
                    console.log('Error fetching difficulty level:', error);
                    
                    // If there's an error, replace the spinner with an error message
                    parentTd.html('Error fetching difficulty level');
                });
            
                console.log('Question ID:', questionId);
            });
            
            
        }
    };
});

