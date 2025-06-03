=== Cheshire Cat Chatbot ===
Contributors: webgrafia
Tags: chatbot, ai, cheshire cat, chat, assistant
Requires at least: 5.8
Requires PHP: 7.4
Tested up to: 6.8
Stable tag: 0.4.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A WordPress plugin to integrate the Cheshire Cat AI chatbot, offering seamless conversational AI for your site.

== Description ==

**Cheshire Cat Chatbot** is a WordPress plugin that seamlessly integrates the [Cheshire Cat AI](https://cheshirecat.ai/) chatbot into your WordPress website. It allows you to add a conversational AI assistant to your site, providing an interactive and engaging experience for your users.

**Features:**

*   **Seamless Integration:** Easily integrate the Cheshire Cat AI chatbot into your WordPress site.
*   **Conversational AI:** Engage users with a natural language processing-powered chatbot.
*   **Customizable Chat Interface:** Style the chat interface to match your website's design.
*   **Sequential Conversation Display:** Show user and bot messages in a clear, chronological order.
*   **Loading Indicator:** Display a loading animation while waiting for the bot's response.
*   **Error Handling:** Display error messages if there are issues with the connection or the bot's response.
*   **Easy to use:** Use a shortcode to add the chat to your pages.
*   **Global Chat:** Enable the chat on every page of your website.
*   **Avatar Support:** Display a customizable avatar below the chat, making it look like a speech bubble.

== Installation ==

1.  Upload the `cheshire-cat-chatbot` folder to the `/wp-content/plugins/` directory of your WordPress installation.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Go to the WordPress admin panel, then navigate to **Settings -> Cheshire Cat**.
4.  Enter your Cheshire Cat URL (e.g., `http://localhost:1865`) and your API token.
5.  Save Changes.

== Usage ==

1.  **Add the Shortcode:** To add the chat interface to a page or post, use the `[cheshire_chat]` shortcode.
2.  **Interact with the Chatbot:** Visit the page where you added the shortcode and start chatting with the Cheshire Cat AI.
3. **Enable Global Chat:** If you want the chat to appear on every page of your website, you can enable the "Global Chat" option in the **Settings -> Cheshire Cat** section.

== Frequently Asked Questions ==

= What is Cheshire Cat AI? =

Cheshire Cat AI is an open-source AI chatbot platform. You need to have a running instance of Cheshire Cat AI to use this plugin.

= Where can I find my Cheshire Cat URL and Token? =

You can find these details in your Cheshire Cat AI instance's configuration.

= How do I customize the chat interface? =

You can customize the chat interface's colors and font in the **Settings -> Cheshire Cat** section of your WordPress admin panel.

= Can I use the chat on every page? =
Yes, you can enable the "Global Chat" option in the **Settings -> Cheshire Cat** section.

= How do I enable the avatar feature? =
You can enable the avatar feature in the **Configuration** section of the Cheshire Cat menu. After enabling it, you can upload a custom avatar image in the **Style** section.

= What is the Playground page? =
The Playground page is a full-page chat interface for administrators to test the chatbot. It's accessible from the Cheshire Cat menu in the WordPress admin area.

== Screenshots ==

1.  The Cheshire Cat Chatbot configuration page.
2.  The chat interface in action.

== Changelog ==

= 0.4.1 =
*   Added avatar functionality with customizable images
*   Added chat bubble styling when avatar is enabled
*   Added reset buttons for all options and color settings
*   Added admin playground page for full-page chat testing
*   Fixed CSS issues with chat container and avatar display

= 0.4 =
*   Fixes for Plugin Compliance and Security Improvement Guidelines

= 0.3 =
*   Fixed security issues.
*   Updated tested up to version.

= 0.2 =
*   Added Global Chat option.
*   Added dynamic CSS.
*   Added welcome message.
*   Added overview page.
*   Added error handling.
*   Added loading indicator.
*   Added sequential conversation display.
*   Fixed some bugs.

= 0.1 =
*   Initial release.
*   Basic integration with Cheshire Cat AI.
*   Shortcode for adding the chat to pages and posts.

== Upgrade Notice ==

= 0.4.1 =
Feature update: Added avatar support, reset buttons, admin playground, and fixed CSS issues.

= 0.3 =
Security update: Fixed security issues. Updated tested up to version.

= 0.2 =
Major update: Added Global Chat, dynamic CSS, welcome message, overview page, error handling, loading indicator, sequential conversation display and fixed some bugs.
