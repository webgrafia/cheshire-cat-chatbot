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
     * Sanitize HTML to allow certain safe tags while removing potentially dangerous ones.
     *
     * @param {string} html - The HTML to sanitize
     * @return {string} The sanitized HTML
     */
    function sanitizeHTML(html) {
        if (!html) {
            return '';
        }

        // Create a new div element
        var tempDiv = $('<div></div>');

        // Set the HTML content
        tempDiv.html(html);

        // Remove potentially dangerous tags and attributes
        tempDiv.find('script, iframe, object, embed, style').remove();

        // Remove dangerous attributes from all elements
        tempDiv.find('*').each(function() {
            var element = $(this);
            var attrs = element[0].attributes;
            var attrsToRemove = [];

            // Collect attributes to remove
            for (var i = 0; i < attrs.length; i++) {
                var attrName = attrs[i].name.toLowerCase();
                if (attrName.indexOf('on') === 0 || // event handlers
                    attrName === 'href' && /^\s*javascript:/i.test(attrs[i].value) || // javascript: URLs
                    attrName === 'src' && /^\s*javascript:/i.test(attrs[i].value) || // javascript: URLs
                    attrName === 'formaction' || // form action override
                    attrName === 'xlink:href') { // SVG xlink:href can be used for JavaScript execution
                    attrsToRemove.push(attrName);
                }
            }

            // Remove the collected attributes
            for (var j = 0; j < attrsToRemove.length; j++) {
                element.removeAttr(attrsToRemove[j]);
            }
        });

        // Get the sanitized HTML
        return tempDiv.html();
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
            // Handle AgentOutput format as shown in the issue description
            if (data.output) {
                content = data.output;
            }
            // Try to find content in other nested structures
            else if (data.content) {
                content = data.content;
            }
            // Handle other possible response formats
            else if (data.text) {
                content = data.text;
            }
            else if (data.message) {
                content = data.message;
            }
            else if (data.response) {
                content = data.response;
            }
            else {
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

        // Handle code blocks with backticks
        content = content.replace(/```(\w*)\n([\s\S]*?)\n```/g, function(match, language, code) {
            return '<pre><code class="language-' + language + '">' + code + '</code></pre>';
        });

        // Handle markdown-style formatting if not already in HTML
        if (content.indexOf('<strong>') === -1 && content.indexOf('<em>') === -1) {
            // Bold text with double asterisks or double underscores
            content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            content = content.replace(/__(.*?)__/g, '<strong>$1</strong>');

            // Italic text with single asterisk or single underscore
            content = content.replace(/\*(.*?)\*/g, '<em>$1</em>');
            content = content.replace(/_(.*?)_/g, '<em>$1</em>');
        }

        return content;
    }

    /**
     * Store chat messages in localStorage.
     *
     * @param {Array} messages - Array of message objects to store
     */
    function storeMessages(messages) {
        localStorage.setItem('cheshire_chat_messages', JSON.stringify(messages));
    }

    /**
     * Get stored chat messages from localStorage.
     *
     * @return {Array} Array of message objects
     */
    function getStoredMessages() {
        var messages = localStorage.getItem('cheshire_chat_messages');
        return messages ? JSON.parse(messages) : [];
    }

    /**
     * Display a message in the chat window.
     *
     * @param {string} message - The message to display
     * @param {string} type - The type of message ('user', 'bot', or 'error')
     * @param {boolean} store - Whether to store the message (default: true)
     */
    function displayMessage(message, type, store = true) {
        var cssClass = '';
        var processedMessage = '';

        switch (type) {
            case 'user':
                cssClass = 'user-message';
                processedMessage = encodeHTML(message);
                break;
            case 'bot':
                cssClass = 'bot-message';
                // For bot messages, sanitize HTML instead of completely encoding it
                processedMessage = sanitizeHTML(message);
                // Convert line breaks to <br> tags for proper display
                processedMessage = processedMessage.replace(/\n/g, '<br>');
                break;
            case 'error':
                cssClass = 'error-message';
                processedMessage = encodeHTML('Error: ' + message);
                break;
            default:
                cssClass = 'bot-message';
                processedMessage = sanitizeHTML(message);
                processedMessage = processedMessage.replace(/\n/g, '<br>');
        }

        $('#cheshire-chat-messages').append(
            '<div class="' + cssClass + '"><p>' + processedMessage + '</p></div>'
        );

        // Store the message in localStorage if requested
        if (store) {
            var messages = getStoredMessages();
            messages.push({
                message: message,
                type: type,
                timestamp: new Date().getTime()
            });
            storeMessages(messages);
        }

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

        // Get the send button reference
        var sendButton = $('#cheshire-chat-send');

        // Disable the send button and change the icon to spinning
        sendButton.prop('disabled', true);
        sendButton.html('<i class="fas fa-spinner fa-spin"></i>');

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
                nonce: cheshire_ajax_object.nonce,
                page_id: cheshire_ajax_object.page_id || '',
                page_url: window.location.href
            },
            success: function(response) {
                // Hide loading indicator
                hideLoader();

                // Re-enable the send button and restore the original icon
                sendButton.prop('disabled', false);
                sendButton.html('<i class="far fa-arrow-alt-circle-right"></i>');

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

                // Re-enable the send button and restore the original icon
                sendButton.prop('disabled', false);
                sendButton.html('<i class="far fa-arrow-alt-circle-right"></i>');

                // Handle AJAX error
                displayMessage(error.statusText || 'Connection error', 'error');
            }
        });
    }

    /**
     * Load stored messages from localStorage and display them in the chat.
     */
    function loadStoredMessages() {
        var messages = getStoredMessages();

        // Clear existing welcome message if we have stored messages
        if (messages.length > 0) {
            $('#cheshire-chat-messages').empty();
        }

        // Display each stored message
        messages.forEach(function(msgObj) {
            // Use store=false to avoid re-storing the messages
            displayMessage(msgObj.message, msgObj.type, false);
        });
    }

    /**
     * Clear chat history from localStorage and reset the chat window.
     */
    function clearChatHistory() {
        // Clear localStorage
        localStorage.removeItem('cheshire_chat_messages');

        // Clear chat window
        $('#cheshire-chat-messages').empty();

        // Display welcome message again
        $.ajax({
            url: cheshire_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'cheshire_get_welcome_message',
                nonce: cheshire_ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#cheshire-chat-messages').html(response.data);
                } else {
                    // If AJAX fails, add a default welcome message
                    $('#cheshire-chat-messages').html('<div class="bot-message"><p>Hello! How can I help you?</p></div>');
                }
            },
            error: function() {
                // If AJAX fails, add a default welcome message
                $('#cheshire-chat-messages').html('<div class="bot-message"><p>Hello! How can I help you?</p></div>');
            }
        });
    }

    /**
     * Display predefined responses as clickable tags.
     */
    function displayPredefinedResponses() {
        // Check if predefined responses container exists, if not create it
        if ($('#cheshire-predefined-responses').length === 0) {
            $('#cheshire-chat-input-container').before('<div id="cheshire-predefined-responses"></div>');
        }

        // Get predefined responses
        $.ajax({
            url: cheshire_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'cheshire_get_predefined_responses',
                nonce: cheshire_ajax_object.nonce,
                page_id: cheshire_ajax_object.page_id || ''
            },
            success: function(response) {
                if (response.success && response.data) {
                    var responses = response.data;
                    var tagsHtml = '';

                    // Create a tag for each predefined response
                    responses.forEach(function(response) {
                        tagsHtml += '<span class="predefined-response-tag">' + encodeHTML(response) + '</span>';
                    });

                    // Add the tags to the container
                    $('#cheshire-predefined-responses').html(tagsHtml);
                }
            }
        });
    }

    /**
     * Handle click on predefined response tag.
     */
    $(document).on('click', '.predefined-response-tag', function() {
        var message = $(this).text();
        $('#cheshire-chat-input').val(message);

        // If this is a content response tag, make sure the chat is open
        if ($(this).hasClass('content-response-tag')) {
            // Open the chat if it's closed
            if ($('#cheshire-chat-container').hasClass('cheshire-chat-closed')) {
                $('#cheshire-chat-container').removeClass('cheshire-chat-closed').addClass('cheshire-chat-open');
                // Update localStorage
                localStorage.setItem('cheshire_chat_state', 'open');
            }
        }

        sendMessage();
    });

    // Initialize the chat interface

    // Add icon to the send button
    $('#cheshire-chat-send').html('<i class="far fa-arrow-alt-circle-right"></i>');

    // Display predefined responses
    displayPredefinedResponses();

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

    // Close chat on X button click
    $('#cheshire-chat-close').click(function() {
        // Don't hide the chat on the playground page
        if ($('#cheshire-chat-container').hasClass('playground')) {
            return;
        }

        $('#cheshire-chat-container').removeClass('cheshire-chat-open').addClass('cheshire-chat-closed');
        // Store the state in localStorage so it persists across page loads
        localStorage.setItem('cheshire_chat_state', 'closed');
    });

    // Start new conversation on "New" button click
    $('#cheshire-chat-new').click(function() {
        clearChatHistory();
    });

    // Open chat when avatar is clicked
    $(document).on('click', '#cheshire-chat-avatar', function() {
        if ($('#cheshire-chat-container').hasClass('cheshire-chat-closed')) {
            $('#cheshire-chat-container').removeClass('cheshire-chat-closed').addClass('cheshire-chat-open');
            // Update localStorage
            localStorage.setItem('cheshire_chat_state', 'open');
        }
    });

    // Check if chat should be opened on page load
    $(document).ready(function() {
        // Always show the chat on the playground page
        if ($('#cheshire-chat-container').hasClass('playground')) {
            $('#cheshire-chat-container').removeClass('cheshire-chat-closed').addClass('cheshire-chat-open');
            return;
        }

        // Check localStorage for saved state
        var savedState = localStorage.getItem('cheshire_chat_state');

        // If saved state is 'open' or default state is 'open' and no saved state, open the chat
        if (savedState === 'open' || (!savedState && cheshire_ajax_object.default_state === 'open')) {
            $('#cheshire-chat-container').removeClass('cheshire-chat-closed').addClass('cheshire-chat-open');
        }
        // Otherwise, it stays closed (which is the default in HTML now)

        // For backward compatibility, check the old localStorage key and update to new format
        if (localStorage.getItem('cheshire_chat_hidden') === 'true') {
            // Ensure chat stays closed
            $('#cheshire-chat-container').removeClass('cheshire-chat-open').addClass('cheshire-chat-closed');
            localStorage.setItem('cheshire_chat_state', 'closed');
            // Remove old localStorage key
            localStorage.removeItem('cheshire_chat_hidden');
        }

        // Load stored messages from localStorage
        loadStoredMessages();
    });
});
