<?php
namespace webgrafia\cheshirecat\inc\admin;
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Add the Cheshire Cat menu to the WordPress admin.
 */
function cheshirecat_admin_menu()
{
    // Add the main menu item
    add_menu_page(
        __('Cheshire Cat', 'cheshire-cat-chatbot'), // Page title
        __('Cheshire Cat', 'cheshire-cat-chatbot'), // Menu title
        'manage_options', // Capability
        'cheshire-cat', // Menu slug
        __NAMESPACE__ . '\cheshirecat_overview_page', // Callback function for the overview page
        'dashicons-smiley', // Icon (you can change this)
        80 // Position (adjust as needed)
    );
    // Add the "Overview & Usage" submenu
    add_submenu_page(
        'cheshire-cat', // Parent slug
        __('Overview & Usage', 'cheshire-cat-chatbot'), // Page title
        __('Overview & Usage', 'cheshire-cat-chatbot'), // Menu title
        'manage_options', // Capability
        'cheshire-cat', // Menu slug (same as parent to load the overview page)
        __NAMESPACE__ . '\cheshirecat_overview_page' // Callback function
    );
    // Add the "Style" submenu
    add_submenu_page(
        'cheshire-cat', // Parent slug
        __('Style', 'cheshire-cat-chatbot'), // Page title
        __('Style', 'cheshire-cat-chatbot'), // Menu title
        'manage_options', // Capability
        'cheshire-cat-style', // Menu slug
        __NAMESPACE__ . '\cheshirecat_style_page' // Callback function
    );
    // Add the "Configuration" submenu
    add_submenu_page(
        'cheshire-cat', // Parent slug
        __('Configuration', 'cheshire-cat-chatbot'), // Page title
        __('Configuration', 'cheshire-cat-chatbot'), // Menu title
        'manage_options', // Capability
        'cheshire-cat-configuration', // Menu slug
        __NAMESPACE__ . '\cheshirecat_configuration_page' // Callback function
    );

    // Add the "Playground" submenu
    add_submenu_page(
        'cheshire-cat', // Parent slug
        __('Playground', 'cheshire-cat-chatbot'), // Page title
        __('Playground', 'cheshire-cat-chatbot'), // Menu title
        'manage_options', // Capability
        'cheshire-cat-playground', // Menu slug
        __NAMESPACE__ . '\cheshirecat_playground_page' // Callback function
    );
}
add_action('admin_menu', __NAMESPACE__ . '\cheshirecat_admin_menu');
