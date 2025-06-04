<?php

namespace webgrafia\cheshirecat\inc\admin;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Configuration page callback.
 */
function cheshirecat_configuration_page()
{
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'cheshire-cat-chatbot'));
    }

    // Handle form submission
    if (isset($_POST['cheshire_plugin_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['cheshire_plugin_nonce'])), 'cheshire_plugin_save_settings')) {
        if (isset($_POST['cheshire_plugin_url'])) {
            $cheshire_plugin_url = sanitize_text_field(wp_unslash($_POST['cheshire_plugin_url']));
            update_option('cheshire_plugin_url', esc_url_raw($cheshire_plugin_url));
        }
        if (isset($_POST['cheshire_plugin_token'])) {
            $cheshire_plugin_token = sanitize_text_field(wp_unslash($_POST['cheshire_plugin_token']));
            update_option('cheshire_plugin_token', $cheshire_plugin_token);
        }
        if (isset($_POST['cheshire_plugin_global_chat'])) {
            $cheshire_plugin_global_chat = sanitize_text_field(wp_unslash($_POST['cheshire_plugin_global_chat']));
            update_option('cheshire_plugin_global_chat', $cheshire_plugin_global_chat);
        }
        if (isset($_POST['cheshire_plugin_enable_avatar'])) {
            $cheshire_plugin_enable_avatar = sanitize_text_field(wp_unslash($_POST['cheshire_plugin_enable_avatar']));
            update_option('cheshire_plugin_enable_avatar', $cheshire_plugin_enable_avatar);
        } else {
            update_option('cheshire_plugin_enable_avatar', 'off');
        }
        if (isset($_POST['cheshire_plugin_enable_context'])) {
            $cheshire_plugin_enable_context = sanitize_text_field(wp_unslash($_POST['cheshire_plugin_enable_context']));
            update_option('cheshire_plugin_enable_context', $cheshire_plugin_enable_context);
        } else {
            update_option('cheshire_plugin_enable_context', 'off');
        }

        // Default chat state (open/closed)
        if (isset($_POST['cheshire_plugin_default_state'])) {
            $cheshire_plugin_default_state = sanitize_text_field(wp_unslash($_POST['cheshire_plugin_default_state']));
            update_option('cheshire_plugin_default_state', $cheshire_plugin_default_state);
        }

        // Logged-in users only
        if (isset($_POST['cheshire_plugin_logged_in_only'])) {
            $cheshire_plugin_logged_in_only = sanitize_text_field(wp_unslash($_POST['cheshire_plugin_logged_in_only']));
            update_option('cheshire_plugin_logged_in_only', $cheshire_plugin_logged_in_only);
        } else {
            update_option('cheshire_plugin_logged_in_only', 'off');
        }

        // Post types
        if (isset($_POST['cheshire_plugin_enabled_post_types']) && is_array($_POST['cheshire_plugin_enabled_post_types'])) {
            $post_types = array_map('sanitize_text_field', wp_unslash($_POST['cheshire_plugin_enabled_post_types']));
            update_option('cheshire_plugin_enabled_post_types', $post_types);
        } else {
            update_option('cheshire_plugin_enabled_post_types', array());
        }

        // Taxonomies
        if (isset($_POST['cheshire_plugin_enabled_taxonomies']) && is_array($_POST['cheshire_plugin_enabled_taxonomies'])) {
            $taxonomies = array_map('sanitize_text_field', wp_unslash($_POST['cheshire_plugin_enabled_taxonomies']));
            update_option('cheshire_plugin_enabled_taxonomies', $taxonomies);
        } else {
            update_option('cheshire_plugin_enabled_taxonomies', array());
        }

        // Content type application mode
        if (isset($_POST['cheshire_plugin_content_type_mode'])) {
            $content_type_mode = sanitize_text_field(wp_unslash($_POST['cheshire_plugin_content_type_mode']));
            update_option('cheshire_plugin_content_type_mode', $content_type_mode);
        }

        // Show in homepage
        if (isset($_POST['cheshire_plugin_show_in_homepage'])) {
            $show_in_homepage = sanitize_text_field(wp_unslash($_POST['cheshire_plugin_show_in_homepage']));
            update_option('cheshire_plugin_show_in_homepage', $show_in_homepage);
        } else {
            update_option('cheshire_plugin_show_in_homepage', 'off');
        }
    }

    $cheshire_plugin_url = get_option('cheshire_plugin_url');
    $cheshire_plugin_token = get_option('cheshire_plugin_token');
    $cheshire_plugin_global_chat = get_option('cheshire_plugin_global_chat');
    $cheshire_plugin_enable_avatar = get_option('cheshire_plugin_enable_avatar', 'off');
    $cheshire_plugin_enable_context = get_option('cheshire_plugin_enable_context', 'off');
    $cheshire_plugin_default_state = get_option('cheshire_plugin_default_state', 'open');
    $cheshire_plugin_logged_in_only = get_option('cheshire_plugin_logged_in_only', 'off');
    $cheshire_plugin_content_type_mode = get_option('cheshire_plugin_content_type_mode', 'site_wide');
    $cheshire_plugin_show_in_homepage = get_option('cheshire_plugin_show_in_homepage', 'off');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post">
            <?php wp_nonce_field('cheshire_plugin_save_settings', 'cheshire_plugin_nonce'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Cheshire Cat URL', 'cheshire-cat-chatbot'); ?></th>
                    <td><input type="text" name="cheshire_plugin_url" value="<?php echo esc_attr($cheshire_plugin_url); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Cheshire Cat Token', 'cheshire-cat-chatbot'); ?></th>
                    <td><input type="text" name="cheshire_plugin_token" value="<?php echo esc_attr($cheshire_plugin_token); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Global Chat', 'cheshire-cat-chatbot'); ?></th>
                    <td>
                        <input type="checkbox" name="cheshire_plugin_global_chat" <?php checked($cheshire_plugin_global_chat, 'on'); ?> />
                        <label for="cheshire_plugin_global_chat"><?php esc_html_e('Enable Global Chat', 'cheshire-cat-chatbot'); ?></label>
                        <p class="description"><?php esc_html_e('Check this box to enable the chat on every page of your website.', 'cheshire-cat-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Chat Avatar', 'cheshire-cat-chatbot'); ?></th>
                    <td>
                        <input type="checkbox" name="cheshire_plugin_enable_avatar" <?php checked($cheshire_plugin_enable_avatar, 'on'); ?> />
                        <label for="cheshire_plugin_enable_avatar"><?php esc_html_e('Enable Avatar', 'cheshire-cat-chatbot'); ?></label>
                        <p class="description"><?php esc_html_e('Check this box to display an avatar below the chat, making it look like a speech bubble.', 'cheshire-cat-chatbot'); ?></p>
                        <p class="description"><strong><?php esc_html_e('Note:', 'cheshire-cat-chatbot'); ?></strong> <?php printf(
                            /* translators: %s: URL to the Style page */
                            esc_html__('After enabling the avatar, go to the %s page to upload your custom avatar image.', 'cheshire-cat-chatbot'),
                            '<a href="' . esc_url(admin_url('admin.php?page=cheshire-cat-style')) . '">' . esc_html__('Style', 'cheshire-cat-chatbot') . '</a>'
                        ); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Context Information', 'cheshire-cat-chatbot'); ?></th>
                    <td>
                        <input type="checkbox" name="cheshire_plugin_enable_context" <?php checked($cheshire_plugin_enable_context, 'on'); ?> />
                        <label for="cheshire_plugin_enable_context"><?php esc_html_e('Enable Context Information', 'cheshire-cat-chatbot'); ?></label>
                        <p class="description"><?php esc_html_e('Check this box to send page context information (title, description, etc.) to the Cheshire Cat with each message.', 'cheshire-cat-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Default Chat State', 'cheshire-cat-chatbot'); ?></th>
                    <td>
                        <select name="cheshire_plugin_default_state">
                            <option value="open" <?php selected($cheshire_plugin_default_state, 'open'); ?>><?php esc_html_e('Open', 'cheshire-cat-chatbot'); ?></option>
                            <option value="closed" <?php selected($cheshire_plugin_default_state, 'closed'); ?>><?php esc_html_e('Closed', 'cheshire-cat-chatbot'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Choose whether the chat window should be open or closed by default when a user visits your site.', 'cheshire-cat-chatbot'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Logged-in Users Only', 'cheshire-cat-chatbot'); ?></th>
                    <td>
                        <input type="checkbox" name="cheshire_plugin_logged_in_only" <?php checked($cheshire_plugin_logged_in_only, 'on'); ?> />
                        <label for="cheshire_plugin_logged_in_only"><?php esc_html_e('Show chat only to logged-in users', 'cheshire-cat-chatbot'); ?></label>
                        <p class="description"><?php esc_html_e('Check this box to show the chat only to users who are logged in to your WordPress site.', 'cheshire-cat-chatbot'); ?></p>
                    </td>
                </tr>
            </table>

            <div id="content-type-settings" style="<?php echo $cheshire_plugin_global_chat !== 'on' ? 'display: none;' : ''; ?>">
                <h2><?php esc_html_e('Content Type Settings', 'cheshire-cat-chatbot'); ?></h2>
                <p class="description"><?php esc_html_e('Configure how the chat should be displayed across your site.', 'cheshire-cat-chatbot'); ?></p>

                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Display Mode', 'cheshire-cat-chatbot'); ?></th>
                        <td>
                            <div style="margin-bottom: 10px;">
                                <input type="radio" id="site_wide" name="cheshire_plugin_content_type_mode" value="site_wide" <?php checked($cheshire_plugin_content_type_mode, 'site_wide'); ?> />
                                <label for="site_wide"><?php esc_html_e('Show on all pages', 'cheshire-cat-chatbot'); ?></label>
                            </div>
                            <div style="margin-bottom: 10px;">
                                <input type="radio" id="selected_types" name="cheshire_plugin_content_type_mode" value="selected_types" <?php checked($cheshire_plugin_content_type_mode, 'selected_types'); ?> />
                                <label for="selected_types"><?php esc_html_e('Show only on selected post types and taxonomies', 'cheshire-cat-chatbot'); ?></label>
                            </div>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php esc_html_e('Show in Homepage', 'cheshire-cat-chatbot'); ?></th>
                        <td>
                            <input type="checkbox" name="cheshire_plugin_show_in_homepage" <?php checked($cheshire_plugin_show_in_homepage, 'on'); ?> />
                            <label for="cheshire_plugin_show_in_homepage"><?php esc_html_e('Enable chat on homepage', 'cheshire-cat-chatbot'); ?></label>
                            <p class="description"><?php esc_html_e('Check this box to show the chat on your site\'s homepage.', 'cheshire-cat-chatbot'); ?></p>
                        </td>
                    </tr>
                </table>

                <div id="post-type-taxonomy-selection" style="<?php echo $cheshire_plugin_content_type_mode !== 'selected_types' ? 'display: none;' : ''; ?>">
                    <h3><?php esc_html_e('Select Content Types', 'cheshire-cat-chatbot'); ?></h3>
                    <p class="description"><?php esc_html_e('Select which post types and taxonomies should have the chat enabled.', 'cheshire-cat-chatbot'); ?></p>

                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Post Types', 'cheshire-cat-chatbot'); ?></th>
                            <td>
                                <?php
                                $post_types = get_post_types(array('public' => true), 'objects');
                                $enabled_post_types = get_option('cheshire_plugin_enabled_post_types', array('post', 'page'));

                                foreach ($post_types as $post_type) {
                                    $checked = in_array($post_type->name, $enabled_post_types) ? 'checked' : '';
                                    echo '<div style="margin-bottom: 10px;">';
                                    echo '<input type="checkbox" id="cheshire_plugin_enabled_post_types_' . esc_attr($post_type->name) . '" name="cheshire_plugin_enabled_post_types[]" value="' . esc_attr($post_type->name) . '" ' . $checked . ' />';
                                    echo '<label for="cheshire_plugin_enabled_post_types_' . esc_attr($post_type->name) . '">' . esc_html($post_type->label) . '</label>';
                                    echo '</div>';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php esc_html_e('Taxonomies', 'cheshire-cat-chatbot'); ?></th>
                            <td>
                                <?php
                                $taxonomies = get_taxonomies(array('public' => true), 'objects');
                                $enabled_taxonomies = get_option('cheshire_plugin_enabled_taxonomies', array('category', 'post_tag'));

                                foreach ($taxonomies as $taxonomy) {
                                    $checked = in_array($taxonomy->name, $enabled_taxonomies) ? 'checked' : '';
                                    echo '<div style="margin-bottom: 10px;">';
                                    echo '<input type="checkbox" id="cheshire_plugin_enabled_taxonomies_' . esc_attr($taxonomy->name) . '" name="cheshire_plugin_enabled_taxonomies[]" value="' . esc_attr($taxonomy->name) . '" ' . $checked . ' />';
                                    echo '<label for="cheshire_plugin_enabled_taxonomies_' . esc_attr($taxonomy->name) . '">' . esc_html($taxonomy->label) . '</label>';
                                    echo '</div>';
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <script>
                jQuery(document).ready(function($) {
                    // Toggle content type settings visibility based on global chat checkbox
                    $('input[name="cheshire_plugin_global_chat"]').change(function() {
                        if ($(this).is(':checked')) {
                            $('#content-type-settings').show();
                        } else {
                            $('#content-type-settings').hide();
                        }
                    });

                    // Toggle post type and taxonomy selection based on content type mode
                    $('input[name="cheshire_plugin_content_type_mode"]').change(function() {
                        if ($(this).val() === 'selected_types') {
                            $('#post-type-taxonomy-selection').show();
                        } else {
                            $('#post-type-taxonomy-selection').hide();
                        }
                    });
                });
            </script>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
