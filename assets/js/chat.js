jQuery(document).ready(function($) {
    // Function to scroll to the bottom of the chat
    function scrollToBottom() {
        var chatMessages = $('#cheshire-chat-messages');
        chatMessages.scrollTop(chatMessages.prop("scrollHeight"));
    }

    // Add icon to the send button
    $('#cheshire-chat-send').html('<i class="fas fa-paper-plane"></i>');

    // Send message on click
    $('#cheshire-chat-send').click(function() {
        sendMessage();
    });

    // Send message on Enter key press
    $('#cheshire-chat-input').keypress(function(event) {
        if (event.which == 13) {
            sendMessage();
            return false; // Prevent default behavior (form submission)
        }
    });

    // Function to send the message
    function sendMessage() {
        var message = $('#cheshire-chat-input').val();
        if (message.trim() === '') return; // Don't send empty messages
        $('#cheshire-chat-input').val(''); // Clear the input field

        // Display the user's message
        $('#cheshire-chat-messages').append('<div class="user-message"><p>' + message + '</p></div>');
        scrollToBottom();

        // Display the loader
        $('#cheshire-chat-messages').append('<div class="loader" id="cheshire-loader"></div>');
        scrollToBottom();

        $.ajax({
            url: cheshire_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'cheshire_plugin_ajax',
                message: message,
                nonce: cheshire_ajax_object.nonce
            },
            success: function(response) {
                console.log('AJAX Success:', response);
                // Remove the loader
                $('#cheshire-loader').remove();
                if (response.success) {
                    // Extract only the content from the response
                    var content = '';
                    console.log('Response data:', response.data);

                    // Based on the issue description, we need to extract the content from the complex JSON structure
                    if (response.data && typeof response.data === 'object') {
                        // The example in the issue description shows a complex nested structure
                        // The content we need is in the "content" field at the end of the response
                        if (response.data.content) {
                            content = response.data.content;
                        } else {
                            // Try to find content in the nested structure
                            content = response.data;
                        }
                    } else {
                        content = response.data;
                    }
                    // Append only the content to the chat messages
                    $('#cheshire-chat-messages').append('<div class="bot-message"><p>' + content + '</p></div>');
                } else {
                    // Handle the error
                    $('#cheshire-chat-messages').append('<div class="error-message"><p>Error: ' + response.data + '</p></div>');
                }
                scrollToBottom();
            },
            error: function(error) {
                console.error('AJAX Error:', error);
                // Remove the loader
                $('#cheshire-loader').remove();
                // Handle the error
                $('#cheshire-chat-messages').append('<div class="error-message"><p>Error: ' + error.statusText + '</p></div>');
                scrollToBottom();
            }
        });
    }
});
