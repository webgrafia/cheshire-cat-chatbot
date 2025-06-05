// TinyMCE plugin for Cheshire Cat Chatbot
(function() {
    tinymce.create('tinymce.plugins.CheshireCatPlugin', {
        init: function(editor, url) {
            // Add button to the editor
            editor.addButton('cheshire_cat', {
                title: 'Cheshire Cat AI',
                image: cheshire_tinymce_object.plugin_url + 'assets/img/cheshire-cat-logo.svg',
                onclick: function() {
                    // Open modal when button is clicked
                    editor.windowManager.open({
                        title: 'Cheshire Cat AI',
                        width: 620,
                        height: 400,
                        body: [
                            {
                                type: 'textbox',
                                name: 'prompt',
                                multiline: true,
                                minWidth: 580,
                                minHeight: 300
                            }
                        ],
                        onsubmit: function(e) {
                            // Show loading indicator
                            var loadingText = 'Processing your request...';
                            editor.setProgressState(true);

                            // Send AJAX request to the server
                            jQuery.ajax({
                                url: cheshire_tinymce_object.ajax_url,
                                type: 'POST',
                                data: {
                                    action: 'cheshire_plugin_ajax',
                                    nonce: cheshire_tinymce_object.nonce,
                                    message: e.data.prompt,
                                    from_editor: true
                                },
                                success: function(response) {
                                    editor.setProgressState(false);

                                    if (response.success && response.data) {
                                        // Insert the response into the editor
                                        editor.insertContent(response.data);
                                    } else {
                                        // Show error message
                                        alert('Error: ' + (response.data || 'Unknown error occurred.'));
                                    }
                                },
                                error: function(xhr, status, error) {
                                    editor.setProgressState(false);
                                    alert('Error: ' + error);
                                }
                            });
                        }
                    });
                }
            });
        },

        createControl: function(n, cm) {
            return null;
        },

        getInfo: function() {
            return {
                longname: 'Cheshire Cat AI Plugin',
                author: 'Webgrafia',
                authorurl: 'https://webgrafia.it',
                infourl: 'https://webgrafia.it',
                version: '1.0'
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add('cheshire_cat', tinymce.plugins.CheshireCatPlugin);
})();
