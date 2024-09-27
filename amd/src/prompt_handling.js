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
 * TODO describe module prompt_handling
 *
 * @module     mod_questiongenerator/prompt_handling
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import { call as getContent } from "core/ajax";
import promptModal from './prompt_modal';
export const promptHandling = async (cmid) => {

    var form = $('#prompt-form');
    var content = $('#mod-qg-body');
    var spinner = $('#qg-spinner');
    var save = $('#save-question');
    var nextStep = $('#next-step');
    var tryagain = $('#try-again');
    var modal = $('#bsmodal');
    var questionData = null;
    var saveCategory = $('#save-category');
    form.on('submit', function (e) {
        e.preventDefault();
        let formdata = new FormData(this);
        let prompt = formdata.get('prompt');
        form.trigger('reset');
        spinner.show();
        $(this).find('button[type="submit"]').prop('disabled', true);
        // promptModal.create({});


        getContent([{
            methodname: 'mod_questiongenerator_submit_prompts',
            args: { prompt: prompt },
        }])[0].done(response => {
            let questions = JSON.parse(response);
            // console.log(questions);
            questionData = response;
            showQuestions(questions);
            spinner.hide();
            $(this).find('button[type="submit"]').prop('disabled', false);
        }).fail(error => {
            spinner.hide();
            throw new Error(error.message);
        });
    });

    $(document).on('click', '#next-step', function () {
        $(this).prop('disabled', true); // Disable the button to prevent multiple clicks
        content.empty(); // Clear the content
        // $(this).attr('id','savecat-question');
        if (questionData) {
            console.log(questionData);

            getContent([{
                methodname: 'mod_questiongenerator_get_questions_categories',
                args: {
                    'cmid': cmid
                },
            }])[0].done(response => {
                $(this).prop('disabled', false); // Re-enable the button after response

                // Check if the response has categories
                if (response && response.length > 0) {
                    renderCategorySelect(response);
                } else {
                    renderCategoryInput(); // If no categories, show input field
                }
            }).fail(error => {
                spinner.hide();
                console.error('Error:', error.message);
                $(this).prop('disabled', false); // Re-enable the button in case of error
            });
        }
    });
    $(document).on('click', '#save-category', function () {
        $(this).prop('disabled', true); // Disable the button to prevent multiple clicks
        // $(this).attr('id','savecat-question');
        let categoryValue = $('#category').val();
        content.empty(); // Clear the content

        console.log(categoryValue);
        if (questionData) {
            getContent([{
                methodname: 'mod_questiongenerator_create_question_category',
                args: {
                    'cmid': cmid,
                    'categoryname': categoryValue
                },
            }])[0].done(response => {
                $(this).prop('disabled', false); // Re-enable the button after response
                console.log(response);
                console.log(questionData);

                if (response.status) {
                    getContent([{
                        methodname: 'mod_questiongenerator_get_questions_categories',
                        args: {
                            'cmid':cmid

                        },
                    }])[0].done(response => {
                        $(this).prop('disabled', false); // Re-enable the button after response

                        // Check if the response has categories
                        if (response && response.length > 0) {
                            renderCategorySelect(response);
                        } else {
                            renderCategoryInput(); // If no categories, show input field
                        }
                    }).fail(error => {
                        spinner.hide();
                        console.error('Error:', error.message);
                        $(this).prop('disabled', false); // Re-enable the button in case of error
                    });
                } else {
                    renderCategoryInput(); // If no categories, show input field
                }
                // Check if the response has categories

            }).fail(error => {
                spinner.hide();
                console.error('Error:', error.message);
                $(this).prop('disabled', false); // Re-enable the button in case of error
            });
        }
    });
    $(document).on('click', '#save-question', function () {
        let $this = $(this);
        $this.prop('disabled', true); // Disable the button to prevent multiple clicks
        
        let categoryValue = $('#category').val();
        console.log(categoryValue);
    
        content.empty(); // Clear the content
    
        // Show saving animation
        let savingAnimation = $('<div class="saving-animation">Saving...</div>');
        content.append(savingAnimation);
    
        if (questionData) {
            var questionDataJson = JSON.parse(questionData);
            getContent([{
                methodname: 'mod_questiongenerator_save_generated_questions',
                args: {
                    'cmid': cmid,
                    'categoryid': categoryValue,
                    'questionData': questionDataJson
                },
            }])[0].done(response => {
                $this.prop('disabled', false); // Re-enable the button after response
                console.log(response);
    
                // Remove saving animation and show success icon
                savingAnimation.remove();
                let successIcon = $('<div class="success-icon">✔️ Saved</div>');
                content.html(successIcon);
    
            }).fail(error => {
                spinner.hide();
                console.error('Error:', error.message);
                
                // Remove saving animation
                savingAnimation.remove();
                $this.prop('disabled', false); // Re-enable the button in case of error
            });
        }
    
        // Remove modal footer
        $('.modal-footer').remove();
    });
    
    // $(document).on('click', '.save-btn', function (e) {
    //    e.preventDefault();
    //    let category = $('#category').val();
    //    console.log(val);
    // })


    // Function to render category select dropdown with Bootstrap styling
    function renderCategorySelect(categories) {
        let selectHTML = `<div class="form-group">
                            <label for="category">Select Category:</label>
                            <select id="category" class="form-control" name="category">`;

        // Populate the select options
        categories.forEach(category => {
            selectHTML += `<option value="${category.id}">${category.name}</option>`;
        });

        selectHTML += `</select>
                       </div>
                       <button id="createNewCategoryBtn" type="button" class="btn btn-outline-primary mt-2">Create New Category</button>`;

        content.html(selectHTML); // Render the dropdown into the content variable
        $('#next-step').text('Save Questions');
        $('#next-step').attr('id', 'save-question');
        $('#save-category').text('Save Questions');
        $('#save-category').attr('id', 'save-question');
        // Add event listener for "Create New Category" button
        $('#createNewCategoryBtn').on('click', function () {
            renderCategoryInput(); // Switch to input text field
        });
    }

    // Function to render category input text field with Bootstrap styling
    function renderCategoryInput() {
        const inputHTML = `<div class="form-group">
                             <label for="category">New Category:</label>
                             <input type="text" id="category" class="form-control" name="category" placeholder="Enter new category">
                           </div>
                           <button id="backToSelectBtn" type="button" class="btn btn-outline-secondary mt-2">Back to Select</button>`;

        content.html(inputHTML); // Render the input field into the content variable
        $('#save-question').text('Create Category');
        $('#save-question').attr('id', 'save-category');


        // Add event listener for "Back to Select" button
        $('#backToSelectBtn').on('click', function () {
            // Call the API again or restore previous categories if needed
            getContent([{
                methodname: 'mod_questiongenerator_get_questions_categories',
                args: {
                    'cmid':cmid
                },
            }])[0].done(response => {
                renderCategorySelect(response);
            });
        });
    }

    $(document).on('click', '#mod-qg-close', function () {
        hideModal();
    });

    tryagain.on('click', function () {
        hideModal();
    });

    function showQuestions(questions) {
        let html = '';
        questions.forEach(question => {
            html += ` <div class="quiz-container"><div class="question">
                        <h6>${question.question}</h6>
                        </div>
                          <div class="options">
                        `;
            question.options.forEach((option, index) => {
                html += `<label class="option">
                       
                        <span><strong>${index + 1}.</strong>  ${option}</span>
                    </label>`;
            });

            html += `</div><div id="result" class="result">
                    <p>The correct answer is: <span id="correctAnswer">${question.correct_answer}</span></p>
                    </div>`;
            html += '</div></div>';
        });

        content.empty().html(html);
        modal.modal('show');
    }
    function hideModal() {
        modal.addClass('hide');
        setTimeout(() => {
            modal.modal('hide').removeClass('hide');
        }, 250);
    }

    $(document).on('click', '#attemptquiz', startAttempt);
    function startAttempt(e) {
        e.preventDefault();
    
        getContent([{
            methodname: 'mod_questiongenerator_attempt_quiz',
            args: { cmid: cmid,status:'start' },
        }])[0].done(response => {
          if(response.status) {
            window.location.href = `attempt.php?id=${cmid}`;
          }
        }).fail(error => {
            throw new Error(error.message);
        });
    }
};

