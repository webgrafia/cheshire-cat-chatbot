# Cheshire Cat Chatbot for WordPress

![Cheshire Cat Logo](assets/img/logo-bg.png)

**Cheshire Cat Chatbot** is a WordPress plugin that seamlessly integrates the [Cheshire Cat AI](https://cheshirecat.ai/) chatbot into your WordPress website. It allows you to add a conversational AI assistant to your site, providing an interactive and engaging experience for your users.

## Features

* **Seamless Integration:** Easily integrate the Cheshire Cat AI chatbot into your WordPress site.
* **Conversational AI:** Engage users with a natural language processing-powered chatbot.
* **Customizable Chat Interface:** Style the chat interface to match your website's design:
  * Customize colors for chat background, text, user messages, and bot messages
  * Choose your preferred font family
  * Set a personalized welcome message
* **Global Chat Option:** Enable the chat on every page of your website without using shortcodes.
* **Responsive Design:** The chat interface adapts to different screen sizes, including mobile devices.
* **User Experience Enhancements:**
  * Sequential conversation display shows messages in a clear, chronological order
  * Loading indicator while waiting for the bot's response
  * Smooth animations for message transitions
  * Clear visual distinction between user and bot messages
* **Security Features:**
  * XSS protection for message content
  * AJAX nonce verification for all requests
  * Proper data sanitization and validation
* **Error Handling:** Informative error messages if there are issues with the connection or the bot's response.
* **Accessibility:** Improved keyboard navigation and focus states for better accessibility.
* **Easy to Use:** Simple shortcode `[cheshire_chat]` to add the chat to specific pages.

## Installation

1. **Download:** Download the latest release of the Cheshire Cat Chatbot plugin from the [Releases](https://github.com/webgrafia/cheshire-cat-chatbot/releases) page.
2. **Upload:** Upload the `cheshire-cat-chatbot` folder to the `/wp-content/plugins/` directory of your WordPress installation.
3. **Activate:** Activate the plugin through the 'Plugins' menu in WordPress.

## Configuration

1. **Access Settings:** Go to the WordPress admin panel, then navigate to **Cheshire Cat → Configuration**.
2. **Basic Setup:**
   * Enter your Cheshire Cat URL (e.g., `http://localhost:1865`)
   * Enter your API token
   * Optionally enable Global Chat to show the chat on all pages
3. **Customize Appearance:** Go to **Cheshire Cat → Style** to customize colors, fonts, and welcome message.
4. **Save Changes.**

## Usage

### Using the Shortcode

To add the chat interface to a specific page or post, use the `[cheshire_chat]` shortcode:

```
[cheshire_chat]
```

### Using Global Chat

If you want the chat to appear on every page of your website:

1. Go to **Cheshire Cat → Configuration**
2. Check the "Enable Global Chat" option
3. Save Changes

## Dependencies

* **Cheshire Cat AI:** You need a running instance of the Cheshire Cat AI server.
* **Cheshire Cat SDK for Laravel:** The plugin uses the `webgrafia/cheshire-cat-sdk-laravel` package.
* **Guzzle:** For making HTTP requests to the Cheshire Cat API.
* **Font Awesome:** For the chat interface icons.

## For Developers

### CSS Variables

The chat interface uses CSS variables for easy theming. You can override these variables in your theme's CSS:

```css
:root {
    --chat-primary-color: #0078d7;
    --chat-user-msg-bg: #4caf50;
    --chat-bot-msg-bg: #ffffff;
    /* See chat.css for all available variables */
}
```

### Hooks and Filters

The plugin provides hooks for developers to extend its functionality (coming in future versions).

## Contributing

If you'd like to contribute to the development of this plugin, please:

1. Fork the repository
2. Create a new branch for your changes
3. Follow WordPress coding standards
4. Submit a pull request

## License

This plugin is open-source software licensed under the [GNU General Public License v3.0 or later](LICENSE).

## Support

If you encounter any issues or have questions, please open an issue on the GitHub repository.

## Credits

* **Marco Buttarini** - Plugin Author
* **Cheshire Cat AI** - The AI chatbot platform
* **Contributors** - Thanks to all who have contributed to this project
