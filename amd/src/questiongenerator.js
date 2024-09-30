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
    'use strict';
    return {
        init: function(cmid) {
            var form = document.getElementById('quizForm');
            function submitQuizToMoodle(formData) {
                // Display "Creating Quiz..." message
                var modalBody = document.querySelector('.modal-body');
                modalBody.innerHTML = '<p>Creating quiz...</p>';
            
                // Optionally, you can add a spinner (for better UX)
                modalBody.innerHTML += '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>';
            
                // Convert the form data to a JSON string
                var jsonData = JSON.stringify(formData);
                
                // Define the request for the Moodle AJAX call
                var request = {
                    methodname: 'mod_questiongenerator_create_quiz', // Your actual method name
                    args: {
                        quiz_data: jsonData // Pass the JSON string as data
                    },
                };
            
                // Remove modal footer (assuming you have a class 'modal-footer' in your modal)
                var modalFooter = document.querySelector('.modal-footer');
                if (modalFooter) {
                    modalFooter.remove();
                }
            
                // Send the AJAX request
                Ajax.call([request])[0]
                    .done(function(response) {
                        if(response.status){
                            console.log('Quiz submitted successfully:', response);
            
                            // Update the modal with a success message
                            modalBody.innerHTML = '<p>Quiz created successfully!</p>';
                
                            // Optionally, you can add an auto-close after a few seconds
                            setTimeout(function() {
                                $('#quizModal').modal('hide'); // Close the modal
                            }, 2000); // 2-second delay before closing
                        }

                    })
                    .fail(function(error) {
                        console.error('Error submitting quiz:', error);
            
                        // Show an error message to the user
                        modalBody.innerHTML = '<p class="text-danger">There was an error creating the quiz. Please try again.</p>';
                    });
            }
            
            console.log(cmid);
            $('#setQuizBtn').on('click', function() {
               
            });

            // var form = $('#quizForm');
            // console.log(form);
            $(document).on('submit','#quizForm', function (event) {
                event.preventDefault(); 
                var form = $('#quizForm')[0]; // Convert jQuery object to native DOM element

                if (!form.checkValidity()) {
                    event.stopPropagation();
                    form.classList.add('was-validated');
                    return;
                }

                // Gather form data
                
                   // Collect form data using input values directly
                var quizTitle = $('input[name="quiz_title"]').val();
                var easyMarks = $('input[name="easy_marks"]').val();
                var mediumMarks = $('input[name="medium_marks"]').val();
                var hardMarks = $('input[name="hard_marks"]').val();

                // Collect selected questions
                const selectedQuestions = [];
                const checkboxes = $('#questionTable input[type="checkbox"]:checked'); // Get only checked checkboxes
                checkboxes.each(function() {
                    if ($(this).attr('id') !== 'selectAll') {
                        selectedQuestions.push($(this).val()); // Collect the value of each checked checkbox

                    }
                });

                // Create an object to hold the form data
                var formData = {
                    cmid : cmid,
                    quiz_title: quizTitle,
                    easy_marks: easyMarks,
                    medium_marks: mediumMarks,
                    hard_marks: hardMarks,
                    selected_questions: selectedQuestions
                };

                // For debugging: Log the formData object
                console.log(formData);

                // Send the data to the Moodle web service via AJAX
                submitQuizToMoodle(formData);
            });

            $('#categoryDropdown').on('change', function() {
                var categoryid = $(this).val();
                
                var request = {
                    methodname: 'mod_questiongenerator_get_generated_questions',
                    args: {
                        categoryid: categoryid,
                        cmid: cmid,

                    },
                };

                Ajax.call([request])[0].done(function(response) {
                    var tableBody = $('#questionTable tbody');
                    tableBody.empty();

                    response.forEach(function(question) {
                        console.log(question);
                        var difficultyButton = '';
                        var selectField = '';

                        // Check if the difficulty is empty
                        if (!question.difficulty) {
                            selectField = '<input type="checkbox" name="select_question" value="' + question.questionid + '" disabled="true">';
                            difficultyButton = '<button data-question-id="' + question.questionid + '" class="btn btn-primary check-difficulty">Check Difficulty</button>';
                        }
                        else{
                            selectField = '<input type="checkbox" name="select_question" value="' + question.questionid + '" >';
                            difficultyButton = question.difficulty.toLowerCase(); // Ensure all lowercase first
                            difficultyButton = difficultyButton.charAt(0).toUpperCase() + difficultyButton.slice(1);
                                                    }
                        var row = '<tr>' +
                                  '<td>' + selectField + '</td>' +
                                  '<td>' + question.question + '</td>' +
                                  '<td>' + question.options + '</td>' +
                                  '<td>' + question.answer + '</td>' +
                                  '<td>'+difficultyButton+'</td>' +
                                  '</tr>';
                        tableBody.append(row);
                        attachCheckboxListeners(); 

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
                        document.querySelector('input[type="checkbox"][value="'+questionId+'"]').disabled = false;

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
            // Function to attach checkbox event listeners
            function attachCheckboxListeners() {
                const setQuizBtn = document.getElementById('setQuizBtn');
                const selectAll = document.getElementById('selectAll');
                // Event listener for the "Select All" checkbox
                selectAll.addEventListener('change', function() {
                    // Select all dynamically generated checkboxes in the table
                    const checkboxes = document.querySelectorAll('#questionTable input[type="checkbox"]:not(:disabled)');
                    
                    // Check or uncheck all checkboxes based on the "Select All" checkbox state
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = selectAll.checked;
                    });
                    
                    // Call toggleButton to show or hide the "Set Quiz" button based on the checkbox states
                    toggleButton();
                });
                // Initially hide the button
                setQuizBtn.style.visibility = 'hidden';
            
                // Use event delegation to listen for changes in dynamically generated checkboxes
                document.querySelector('#questionTable').addEventListener('change', function(e) {
                    // Ensure the event is coming from a checkbox
                    if (e.target && e.target.type === 'checkbox') {
                        toggleButton();
                    }
                });
            
                function toggleButton() {
                    // Select all dynamically generated checkboxes in the table
                    const checkboxes = document.querySelectorAll('#questionTable input[type="checkbox"]:not(:disabled)');
                    let isChecked = false;
            
                    checkboxes.forEach(checkbox => {
                        if (checkbox.checked) {
                            isChecked = true;
                        }
                    });
            
                    // Show the button if at least one checkbox is checked, otherwise hide it
                    setQuizBtn.style.visibility = isChecked ? 'visible' : 'hidden';
                }
            }
            attachCheckboxListeners();
            
            // Call the attachCheckboxListeners function after generating the table dynamically
            
            
            
        
            
        }
    };
    

});

