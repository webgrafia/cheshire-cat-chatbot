/**
 * Cheshire Cat Chatbot - Frontend JavaScript
 * 
 * Handles the chat interface functionality including sending messages,
 * receiving responses, and updating the UI.
 */
jQuery(document).ready(function($) {
    'use strict';

    /**
     * Scroll the chat window to the bottom to show the latest messages.
     */
    function scrollToBottom() {
        var chatMessages = $('#cheshire-chat-messages');
        chatMessages.scrollTop(chatMessages.prop('scrollHeight'));
    }

    /**
     * Safely encode HTML entities to prevent XSS attacks.
     * 
     * @param {string} text - The text to encode
     * @return {string} The encoded text
     */
    function encodeHTML(text) {
        return $('<div>').text(text).html();
    }

    /**
     * Extract content from the API response.
     * 
     * @param {Object|string} data - The response data
     * @return {string} The extracted content
     */
    function extractContent(data) {
        var content = '';

        if (!data) {
            return '';
        }

        if (typeof data === 'object') {
            // Try to find content in the nested structure
            if (data.content) {
                content = data.content;
            } else {
                // If we can't find a specific content field, convert the object to a string
                try {
                    content = JSON.stringify(data);
                } catch (e) {
                    content = 'Unable to parse response';
                }
            }
        } else {
            content = data;
        }

        return content;
    }

    /**
     * Display a message in the chat window.
     * 
     * @param {string} message - The message to display
     * @param {string} type - The type of message ('user', 'bot', or 'error')
     */
    function displayMessage(message, type) {
        var cssClass = '';

        switch (type) {
            case 'user':
                cssClass = 'user-message';
                break;
            case 'bot':
                cssClass = 'bot-message';
                break;
            case 'error':
                cssClass = 'error-message';
                message = 'Error: ' + message;
                break;
            default:
                cssClass = 'bot-message';
        }

        $('#cheshire-chat-messages').append(
            '<div class="' + cssClass + '"><p>' + encodeHTML(message) + '</p></div>'
        );

        scrollToBottom();
    }

    /**
     * Show the loading indicator.
     */
    function showLoader() {
        $('#cheshire-chat-messages').append('<div class="loader" id="cheshire-loader"></div>');
        scrollToBottom();
    }

    /**
     * Hide the loading indicator.
     */
    function hideLoader() {
        $('#cheshire-loader').remove();
    }

    /**
     * Send a message to the Cheshire Cat API and handle the response.
     */
    function sendMessage() {
        var message = $('#cheshire-chat-input').val();

        // Don't send empty messages
        if (message.trim() === '') {
            return;
        }

        // Clear the input field
        $('#cheshire-chat-input').val('');

        // Display the user's message
        displayMessage(message, 'user');

        // Show loading indicator
        showLoader();

        // Send the message to the server
        $.ajax({
            url: cheshire_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'cheshire_plugin_ajax',
                message: message,
                nonce: cheshire_ajax_object.nonce
            },
            success: function(response) {
                // Hide loading indicator
                hideLoader();

                if (response.success) {
                    // Extract and display the content
                    var content = extractContent(response.data);
                    displayMessage(content, 'bot');
                } else {
                    // Handle error response
                    displayMessage(response.data || 'Unknown error', 'error');
                }
            },
            error: function(error) {
                // Hide loading indicator
                hideLoader();

                // Handle AJAX error
                displayMessage(error.statusText || 'Connection error', 'error');
            }
        });
    }

    // Initialize the chat interface

    // Add icon to the send button
    $('#cheshire-chat-send').html('<i class="fas fa-paper-plane"></i>');

    // Set up event handlers

    // Send message on click
    $('#cheshire-chat-send').click(function() {
        sendMessage();
    });

    // Send message on Enter key press
    $('#cheshire-chat-input').keypress(function(event) {
        if (event.which === 13) {
            sendMessage();
            return false; // Prevent default behavior (form submission)
        }
    });
});
