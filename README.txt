=== Cheshire Cat Chatbot ===
Contributors: webgrafia
Tags: chatbot, ai, cheshire cat, chat, assistant
Requires at least: 5.8
Requires PHP: 7.4
Tested up to: 6.8
Stable tag: 0.9.5
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
*   **Easy to use:** Use a shortcode to add the chat to your pages.
*   **Global Chat:** Enable the chat on every page of your website or choose a specific post type or taxonomy.
*   **Avatar Support:** Display a customizable avatar below the chat, making it look like a speech bubble.
*   **Context Awareness:** Optionally send page context information (title, content, etc.) to make the chatbot aware of the current page's content.
*   **TinyMCE Editor Integration:** Add AI-generated content directly to your posts and pages with a dedicated TinyMCE editor button.
*   **Declarative Memory Integration:** Automatically upload your WordPress content to Cheshire Cat's declarative memory, with support for WooCommerce products and batch processing.

== Installation ==

1.  Upload the `cheshire-cat-chatbot` folder to the `/wp-content/plugins/` directory of your WordPress installation.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Go to the WordPress admin panel, then navigate to **Settings -> Cheshire Cat**.
4.  Enter your Cheshire Cat URL (e.g., `http://localhost:1865`) and your API token.
5.  Save Changes.

== Usage ==

1.  **Add the Shortcode:** To add the chat interface to a page or post, use the `[cheshire_chat]` shortcode.
2.  **Interact with the Chatbot:** Visit the page where you added the shortcode and start chatting with the Cheshire Cat AI.
3.  **Enable Global Chat:** If you want the chat to appear on every page of your website, you can enable the "Global Chat" option in the **Settings -> Cheshire Cat** section.
4.  **Use the TinyMCE Editor Button:** When editing a post or page, you'll see a Cheshire Cat button in the editor toolbar. Click it to open a dialog where you can enter a prompt. The AI-generated response will be inserted directly into your content.

== Frequently Asked Questions ==

= What is Cheshire Cat AI? =

Cheshire Cat AI is an open-source AI chatbot platform. You need to have a running instance of Cheshire Cat AI to use this plugin.

= Where can I find my Cheshire Cat URL and Token? =

You can find these details in your Cheshire Cat AI instance's configuration.

= How do I customize the chat interface? =

You can customize the chat interface's colors and font in the **Settings -> Cheshire Cat** section of your WordPress admin panel.

= Can I use the chat on every page? =
Yes, you can enable the "Global Chat" option in the **Settings -> Cheshire Cat** section.

= Can I customize the avatar image? =
The avatar is always enabled for better user experience. You can upload a custom avatar image in the **Style** section of the Cheshire Cat menu. If no custom image is provided, a default robot avatar will be used.

= What is the Playground page? =
The Playground page is a full-page chat interface for administrators to test the chatbot. It's accessible from the Cheshire Cat menu in the WordPress admin area.

= What is the Context Awareness feature? =
The Context Awareness feature allows the chatbot to receive information about the current page (such as title, content, categories, etc.) with each message. This helps the chatbot provide more relevant responses based on the page the user is viewing. You can enable this feature in the Configuration section.

= How do I use the TinyMCE editor button? =
When editing a post or page, you'll see a Cheshire Cat button in the editor toolbar. Click it to open a dialog where you can enter your prompt. After submitting, the AI-generated response will be inserted directly into your content at the cursor position.

== Screenshots ==

1.  The Cheshire Cat Chatbot Overview & Usage page.
2.  The Cheshire Cat Chatbot Configuration page.
3.  Style settings
4.  Playground
5.  Cheshire Cat Settings
6.  Chatbot in action
7.  Chatbot in Editor

== Changelog ==


= 0.9.5 =
* New: Option to select enabled post types for Declarative Memory uploads in configuration
* Improvement: Declarative Memory Sync page defaults post type selection to saved options

= 0.9.4 =
* Minor fixes

= 0.9.3 =
* Add support for predefined responses in WooCommerce product categories
* Admin configuration for category-specific responses
* Ability to limit predefined questions displayed
* Backend updates for retrieving and processing these responses

= 0.9.2 =
* Simplify WebSocket message handling logic and improve comment clarity

= 0.9.1 =
* Improved accessibility by removing target="_blank" from links
* Enhanced user experience with links opening in the same tab

= 0.9 =
* Enhanced related links functionality to show all links that exceed the minimum score, not just the highest scoring one
* Improved related links filtering to exclude the current post from related links when viewing a post detail page
* Always enabled avatar feature for better user experience
* Improved UI consistency and user experience

= 0.8.2 =
* Added new Declarative Memory Sync admin page for batch processing posts to declarative memory
* Improved WooCommerce product data in declarative memory with short descriptions and product characteristics
* Enhanced user interface with progress bar and filters for post types, status, and date range
* Optimized batch processing for better performance with large numbers of posts

= 0.8.1 =
* Optimized WebSocket connection management to only initialize when the chat is open
* Improved performance by closing WebSocket connection when chat is closed
* Enhanced resource usage by only creating connections when needed

= 0.8.0 =
* Added declarative memory functionality to upload content to Cheshire Cat
* Implemented automatic content upload to declarative memory when posts are saved
* Added option to exclude specific posts from declarative memory upload
* Added automatic content removal from declarative memory when posts are deleted or trashed
* Improved error handling and debugging for declarative memory operations
* Added support for metadata in declarative memory points

= 0.7.3 =
* Added new "Meowww" page in admin dashboard to display Cheshire Cat AI installation information
* Added display of active plugins and their settings
* Added display of LLM (Large Language Model) configurations and settings
* Improved error handling for connection issues
* Enhanced security by masking API keys in the admin interface

= 0.7.2 =
* Improved WebSocket functionality for better real-time communication
* Enhanced WebSocket URL handling in both frontend and admin interfaces
* Fixed issues with WebSocket connection management
* Optimized performance for WebSocket communication

= 0.7.1 =
* Improved UI organization: moved avatar settings to Style page
* Set avatar feature to be enabled by default for better user experience
* Reorganized configuration page for more logical grouping of settings
* Improved usability by moving context information settings before reinforcement message

= 0.7 =
* Added WebSocket communication for improved real-time chat experience
* Implemented automatic fallback to AJAX when WebSocket is not available
* Added configuration option to enable/disable WebSocket communication
* Enhanced performance with faster message delivery and reduced server load
* Improved user experience with more responsive chat interface

= 0.6.5 =
* Removed debug logs for improved performance
* Enhanced code cleanliness and maintainability
* Fixed issue with reinforcement message in editor and prompt tester

= 0.6.4 =
* Added option to hide predefined questions in chat when they are shown in content
* Improved user experience by avoiding duplicate questions display
* Enhanced compatibility with latest WordPress version

= 0.6.3 =
* Added support for predefined responses in content
* Improved chat interface and styling
* Enhanced compatibility with latest WordPress version
* Fixed various bugs and improved performance

= 0.6.2 =
* Added predefined responses functionality
* Improved user experience with better message handling
* Fixed styling issues in various themes
* Enhanced compatibility with WooCommerce

= 0.6.1 =
* Added support for custom predefined responses
* Improved chat interface responsiveness
* Fixed minor bugs and styling issues

= 0.6 =
* Added TinyMCE editor button for inserting AI-generated content directly into posts and pages
* Implemented modal dialog for entering prompts in the editor
* Added functionality to process prompts and insert responses into the editor content

= 0.5.4 =
* Added chat persistence across page navigation
* Added "New conversation" button to clear chat history
* Improved user experience by maintaining conversation context between pages
* Fixed styling issues with chat header buttons

= 0.5.3 =
* Fixed display logic for post types and taxonomies
* Chat now only appears on singular pages of selected post types
* Chat now only appears on term pages of selected taxonomies

= 0.5.2 =
*   Release with latest integrations and improvements
*   Enhanced stability and performance

= 0.5.1 =
*   Maintenance release with stability improvements
*   Fixed minor bugs and improved performance

= 0.5 =
*   Added Context Awareness feature to send page information to the chatbot
*   Improved page content detection for better context awareness
*   Enhanced handling of different WordPress page types
*   Added support for WooCommerce product information in context
*   Fixed issues with title and content retrieval in AJAX requests

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

= 0.9.3 =
Feature update: Added support for predefined responses in WooCommerce product categories, including admin configuration for category-specific responses, the ability to limit predefined questions displayed, and backend updates for retrieving and processing these responses.

= 0.9 =
Feature update: Enhanced related links functionality to show all relevant links, improved filtering to exclude current post from related links, and always enabled avatar for better user experience.

= 0.8.2 =
Feature update: Added new Declarative Memory Sync admin page for batch processing posts to declarative memory and improved WooCommerce product data integration with short descriptions and product characteristics.

= 0.8.1 =
Performance update: Optimized WebSocket connection management to only initialize when the chat is open and close when chat is closed, improving performance and resource usage.

= 0.7.3 =
Feature update: Added new "Meowww" admin page that provides detailed information about your Cheshire Cat AI installation, including active plugins and LLM configurations. Improved error handling and enhanced security.

= 0.7.2 =
Maintenance update: Improved WebSocket functionality for better real-time communication, enhanced WebSocket URL handling, and fixed connection management issues.

= 0.7 =
Performance update: Added WebSocket communication for improved real-time chat experience with faster message delivery and reduced server load. Includes configuration option to enable/disable this feature.

= 0.6.5 =
Performance update: Removed debug logs for improved performance, enhanced code cleanliness, and fixed issue with reinforcement message in editor and prompt tester.

= 0.6.4 =
Feature update: Added option to hide predefined questions in chat when they are shown in content, improving user experience by avoiding duplicate display of questions.

= 0.6.3 =
Feature update: Added support for predefined responses in content, improved chat interface, and fixed various bugs for better performance and compatibility.

= 0.6.2 =
Feature update: Added predefined responses functionality, improved user experience, and enhanced compatibility with WooCommerce.

= 0.6.1 =
Feature update: Added support for custom predefined responses, improved chat interface responsiveness, and fixed minor bugs.

= 0.6 =
Feature update: Added TinyMCE editor button that allows you to insert AI-generated content directly into your posts and pages. Simply click the Cheshire Cat button in the editor toolbar, enter your prompt, and the response will be inserted into your content.

= 0.5.4 =
Feature update: Added chat persistence across page navigation, allowing users to maintain their conversation when navigating between pages. Added a "New conversation" button for better user control.

= 0.5.3 =
Bugfix update: Improved display logic for post types and taxonomies. Chat now only appears on singular pages of selected post types and term pages of selected taxonomies.

= 0.5.2 =
Release with latest integrations: Enhanced stability and performance improvements.

= 0.5.1 =
Maintenance release: Stability improvements and bug fixes for better performance.

= 0.5 =
Feature update: Added Context Awareness to make the chatbot aware of page content, improved content detection, and fixed title/content retrieval issues.

= 0.4.1 =
Feature update: Added avatar support, reset buttons, admin playground, and fixed CSS issues.

= 0.3 =
Security update: Fixed security issues. Updated tested up to version.

= 0.2 =
Major update: Added Global Chat, dynamic CSS, welcome message, overview page, error handling, loading indicator, sequential conversation display and fixed some bugs.
