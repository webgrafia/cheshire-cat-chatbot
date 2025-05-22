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
    ?>
    <div class="wrap">
        <h1><?php if (function_exists('get_admin_page_title')) {
                echo esc_html(get_admin_page_title());
            } ?></h1>

        <p><?php esc_html_e('Welcome to the Cheshire Cat Chatbot plugin! This plugin allows you to integrate the powerful Cheshire Cat AI chatbot into your WordPress website.', 'cheshire-cat-chatbot'); ?></p>

        <h2><?php esc_html_e('Before You Begin', 'cheshire-cat-chatbot'); ?></h2>
        <p>
            <?php esc_html_e('To use this plugin, you must have a working installation of', 'cheshire-cat-chatbot'); ?> <a href="https://cheshirecat.ai/" target="_blank">Cheshire Cat AI</a>. <?php esc_html_e('This plugin acts as a bridge between your WordPress site and your Cheshire Cat AI instance.', 'cheshire-cat-chatbot'); ?>
        </p>
        <p>
            <?php esc_html_e('You will need the following information from your Cheshire Cat AI setup:', 'cheshire-cat-chatbot'); ?>
        <ul style="list-style: disc; margin-left: 20px;">
            <li><?php esc_html_e('<strong>Cheshire Cat URL:</strong> The URL where your Cheshire Cat AI instance is running.', 'cheshire-cat-chatbot'); ?></li>
            <li><?php esc_html_e('<strong>Cheshire Cat Token:</strong> The API token for your Cheshire Cat AI instance.', 'cheshire-cat-chatbot'); ?></li>
        </ul>
        </p>
        <p>
            <?php esc_html_e('You can enter these details in the', 'cheshire-cat-chatbot'); ?> <a href="admin.php?page=cheshire-cat-configuration"><?php esc_html_e('Configuration', 'cheshire-cat-chatbot'); ?></a> <?php esc_html_e('section.', 'cheshire-cat-chatbot'); ?>
        </p>

        <h2><?php esc_html_e('Usage', 'cheshire-cat-chatbot'); ?></h2>

        <h3><?php esc_html_e('Displaying the Chat with the Shortcode', 'cheshire-cat-chatbot'); ?></h3>
        <p>
            <?php esc_html_e('To display the chat on a specific page or post, use the following shortcode:', 'cheshire-cat-chatbot'); ?>
            <code>[cheshire_chat]</code>
        </p>
        <p>
            <?php esc_html_e('Simply paste this shortcode into the content area of any page or post where you want the chat to appear.', 'cheshire-cat-chatbot'); ?>
        </p>

        <h3><?php esc_html_e('Enabling Global Chat', 'cheshire-cat-chatbot'); ?></h3>
        <p>
            <?php esc_html_e('If you want the chat to appear on every page of your website, you can enable the "Global Chat" option in the', 'cheshire-cat-chatbot'); ?> <a href="admin.php?page=cheshire-cat-configuration"><?php esc_html_e('Configuration', 'cheshire-cat-chatbot'); ?></a> <?php esc_html_e('section.', 'cheshire-cat-chatbot'); ?>
        </p>
        <p>
            <?php esc_html_e('When the Global Chat is enabled, the chat will be automatically added to all pages, and you', 'cheshire-cat-chatbot'); ?> <strong><?php esc_html_e('do not', 'cheshire-cat-chatbot'); ?></strong> <?php esc_html_e('need to use the shortcode.', 'cheshire-cat-chatbot'); ?>
        </p>
    </div>
    <?php
}
