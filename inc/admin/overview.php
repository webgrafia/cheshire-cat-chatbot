<?php

namespace webgrafia\cheshirecat\inc\admin;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Overview & Usage page callback.
 */
function cheshirecat_overview_page()
{
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'cheshire-cat-chatbot'));
    }

    // Handle form submission
    if (isset($_POST['cheshire_overview_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['cheshire_overview_nonce'])), 'cheshire_overview_save_settings')) {
        if (isset($_POST['cheshire_plugin_url'])) {
            $cheshire_plugin_url = sanitize_text_field(wp_unslash($_POST['cheshire_plugin_url']));
            update_option('cheshire_plugin_url', esc_url_raw($cheshire_plugin_url));
        }
        if (isset($_POST['cheshire_plugin_token'])) {
            $cheshire_plugin_token = sanitize_text_field(wp_unslash($_POST['cheshire_plugin_token']));
            update_option('cheshire_plugin_token', $cheshire_plugin_token);
        }

        // WebSocket communication
        if (isset($_POST['cheshire_plugin_enable_websocket'])) {
            $cheshire_plugin_enable_websocket = sanitize_text_field(wp_unslash($_POST['cheshire_plugin_enable_websocket']));
            update_option('cheshire_plugin_enable_websocket', $cheshire_plugin_enable_websocket);
        } else {
            update_option('cheshire_plugin_enable_websocket', 'off');
        }

        // WebSocket URL
        if (isset($_POST['cheshire_plugin_websocket_url'])) {
            $cheshire_plugin_websocket_url = sanitize_text_field(wp_unslash($_POST['cheshire_plugin_websocket_url']));
            update_option('cheshire_plugin_websocket_url', $cheshire_plugin_websocket_url);
        }

        add_settings_error(
            'cheshire_cat_overview_options',
            'cheshire_cat_settings_updated',
            __('Settings saved successfully.', 'cheshire-cat-chatbot'),
            'success'
        );
    }

    $cheshire_plugin_url = get_option('cheshire_plugin_url');
    $cheshire_plugin_token = get_option('cheshire_plugin_token');
    $cheshire_plugin_enable_websocket = get_option('cheshire_plugin_enable_websocket', 'off');
    $cheshire_plugin_websocket_url = get_option('cheshire_plugin_websocket_url', '');

    ?>
    <div class="wrap cheshire-admin">
        <h1><?php if (function_exists('get_admin_page_title')) {
                echo esc_html(get_admin_page_title());
            } ?></h1>

        <p><?php _e('Welcome to the Cheshire Cat Chatbot plugin! This plugin allows you to integrate the powerful Cheshire Cat AI chatbot into your WordPress website.', 'cheshire-cat-chatbot'); ?></p>

        <div class="cheshire-section">
            <h2><?php _e('Connection Settings', 'cheshire-cat-chatbot'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('cheshire_overview_save_settings', 'cheshire_overview_nonce'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Cheshire Cat URL', 'cheshire-cat-chatbot'); ?></th>
                        <td>
                            <input type="text" name="cheshire_plugin_url" value="<?php echo esc_attr($cheshire_plugin_url); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('The URL where your Cheshire Cat AI instance is running.', 'cheshire-cat-chatbot'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Cheshire Cat Token', 'cheshire-cat-chatbot'); ?></th>
                        <td>
                            <input type="text" name="cheshire_plugin_token" value="<?php echo esc_attr($cheshire_plugin_token); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('The API token for your Cheshire Cat AI instance.', 'cheshire-cat-chatbot'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('WebSocket Communication', 'cheshire-cat-chatbot'); ?></th>
                        <td>
                            <input type="checkbox" id="cheshire_plugin_enable_websocket" name="cheshire_plugin_enable_websocket" <?php checked($cheshire_plugin_enable_websocket, 'on'); ?> />
                            <label for="cheshire_plugin_enable_websocket"><?php esc_html_e('Enable WebSocket Communication', 'cheshire-cat-chatbot'); ?></label>
                            <p class="description"><?php esc_html_e('Check this box to use WebSocket instead of HTTP for communication with the Cheshire Cat. This option does not apply to requests from the editor or prompt tester.', 'cheshire-cat-chatbot'); ?></p>

                            <div id="websocket_url_field" <?php echo $cheshire_plugin_enable_websocket !== 'on' ? 'hidden' : ''; ?>>
                                <label for="cheshire_plugin_websocket_url"><?php esc_html_e('WebSocket URL', 'cheshire-cat-chatbot'); ?></label>
                                <input type="text" id="cheshire_plugin_websocket_url" name="cheshire_plugin_websocket_url" value="<?php echo esc_attr($cheshire_plugin_websocket_url); ?>" class="regular-text" />
                                <p class="description"><?php esc_html_e('Optional: Enter a custom WebSocket URL. If left empty, the plugin will automatically convert the Cheshire Cat URL from HTTP to WebSocket.', 'cheshire-cat-chatbot'); ?></p>
                            </div>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <script>
                jQuery(document).ready(function($) {
                    // Toggle websocket URL field visibility based on websocket checkbox
                    $('#cheshire_plugin_enable_websocket').change(function() {
                        if ($(this).is(':checked')) {
                            $('#websocket_url_field').removeAttr('hidden');
                        } else {
                            $('#websocket_url_field').attr('hidden', 'hidden');
                        }
                    });
                });
            </script>
        </div>

        <div class="cheshire-section">
            <h2><?php _e('Before You Begin', 'cheshire-cat-chatbot'); ?></h2>
            <p>
                <?php _e('To use this plugin, you must have a working installation of', 'cheshire-cat-chatbot'); ?> <a href="https://cheshirecat.ai/" target="_blank">Cheshire Cat AI</a>. <?php esc_html_e('This plugin acts as a bridge between your WordPress site and your Cheshire Cat AI instance.', 'cheshire-cat-chatbot'); ?>
            </p>
            <p>
                <?php _e('Make sure you have entered the correct URL and token in the settings above.', 'cheshire-cat-chatbot'); ?>
            </p>
        </div>

        <div class="cheshire-section">
            <h2><?php _e('Usage', 'cheshire-cat-chatbot'); ?></h2>

            <div class="settings-section">
                <h3 class="settings-section-title"><?php _e('Displaying the Chat with the Shortcode', 'cheshire-cat-chatbot'); ?></h3>
                <p>
                    <?php _e('To display the chat on a specific page or post, use the following shortcode:', 'cheshire-cat-chatbot'); ?>
                    <code>[cheshire_chat]</code>
                </p>
                <p>
                    <?php _e('Simply paste this shortcode into the content area of any page or post where you want the chat to appear.', 'cheshire-cat-chatbot'); ?>
                </p>
            </div>

            <div class="settings-section">
                <h3 class="settings-section-title"><?php _e('Enabling Global Chat', 'cheshire-cat-chatbot'); ?></h3>
                <p>
                    <?php _e('If you want the chat to appear on every page of your website, you can enable the "Global Chat" option in the', 'cheshire-cat-chatbot'); ?> <a href="admin.php?page=cheshire-cat-configuration"><?php esc_html_e('Configuration', 'cheshire-cat-chatbot'); ?></a> <?php esc_html_e('section.', 'cheshire-cat-chatbot'); ?>
                </p>
                <p>
                    <?php _e('When the Global Chat is enabled, the chat will be automatically added to all pages, and you', 'cheshire-cat-chatbot'); ?> <strong><?php esc_html_e('do not', 'cheshire-cat-chatbot'); ?></strong> <?php esc_html_e('need to use the shortcode.', 'cheshire-cat-chatbot'); ?>
                </p>
            </div>
        </div>
    </div>
    <?php
}
