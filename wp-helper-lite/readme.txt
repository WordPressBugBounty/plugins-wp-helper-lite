=== WP Helper Premium ===
Contributors: matbao
Tags: contact button, SMTP, maintenance mode, security, woocommerce, popup, spam filter, AI, all-in-one
Requires at least: 6.7
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 4.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

All-in-one WordPress toolkit: contact channels, SMTP, maintenance mode, AI content, spam filter, WooCommerce enhancements and more — in a single plugin.

== Description ==

WP Helper Premium replaces a dozen single-purpose plugins with one unified toolkit. Every feature is optional and toggled from a clean admin UI — activate only what you need.

**Core features**

* **Contact Channels** — floating buttons for phone, Zalo, Facebook, Email, and custom links.
* **SMTP Mail** — configure Gmail SMTP or any custom SMTP server; built-in test-send tool.
* **Header & Footer Scripts** — inject custom HTML/JS/CSS into `<head>`, body-top, or `<footer>` without editing theme files.
* **Maintenance Mode** — put the site in maintenance with 5 built-in templates (gradient, cyberpunk, corporate, construction, minimal). Optional AI-generated content via Google Gemini.
* **Security** — disable XML-RPC, hide WP version, remove generator meta tag, disable file editing, custom login URL.
* **Pop-up** — promotional and newsletter pop-ups with scheduling and display rules.
* **WooCommerce Toolkit** — buy-now button placement, e-commerce platform links, order search by phone, Thank You page customization, AI payment verification (OCR receipt scanning).
* **Spam Filter** — honeypot, rate limiting, IP blocking, country blocking, keyword filtering. Optionally checks visitor IP via ip-api.com.
* **Captcha** — Math Quiz captcha for contact forms and comments. Optionally integrates Google reCAPTCHA, Cloudflare Turnstile, or hCaptcha.
* **Email Log** — records every outgoing email with status, headers, and body for debugging.
* **Form Manager** — collect, view, and export contact form submissions.
* **AI Hub** — connect Google Gemini, Anthropic Claude, or OpenAI GPT to power AI features site-wide with a 3-tier fallback chain.
* **Extensions** — duplicate page/post/menu, redirect 404 → homepage, remove emoji scripts, disable embeds, disable Google Fonts, disable Dashicons, SVG upload support.

== Installation ==

= From the WordPress Dashboard =
1. Go to **Plugins → Add New**.
2. Search for **WP Helper Premium** and click **Install Now**.
3. Activate the plugin from the **Plugins** menu.
4. Navigate to **WP Helper** in the admin sidebar to configure features.

= Manual Installation =
1. Download `wp-helper-premium.zip`.
2. Unzip and upload the `wp-helper-lite` folder to `/wp-content/plugins/`.
3. Activate via **Plugins → Installed Plugins**.

== Privacy Policy ==

This plugin does not track users, store personal data, or use cookies by default. Certain optional features transmit data to third-party services — see **External Services** below. All external calls are initiated server-side and occur only when the relevant feature is enabled and configured by a site administrator.

== External Services ==

This plugin optionally connects to the following third-party services when the corresponding features are enabled by the site administrator. No data is sent unless the feature is explicitly activated and configured.

= Google Gemini API =
Used for AI-powered content generation (maintenance page content, product descriptions, image captions). Data sent: text prompts provided by the administrator.
* Service: https://ai.google.dev/
* Terms of Service: https://ai.google.dev/terms
* Privacy Policy: https://policies.google.com/privacy

= Anthropic Claude API =
Used as a fallback AI provider for content generation when configured by the administrator.
* Service: https://www.anthropic.com/
* Terms of Service: https://www.anthropic.com/legal/consumer-terms
* Privacy Policy: https://www.anthropic.com/legal/privacy

= OpenAI API =
Used as an optional AI provider for content generation when configured by the administrator.
* Service: https://openai.com/
* Terms of Service: https://openai.com/terms
* Privacy Policy: https://openai.com/privacy

= ip-api.com =
Used by the Spam Filter feature to look up the country/ISP of a visitor's IP address when country-based blocking is enabled. Only the visitor's IP address is transmitted.
* Service: https://ip-api.com/
* Terms of Service: https://ip-api.com/docs
* Privacy Policy: https://ip-api.com/

= Google reCAPTCHA =
Used by the Captcha module when the administrator selects Google reCAPTCHA. Visitor IP and browser information are sent to Google for spam scoring.
* Service: https://www.google.com/recaptcha/
* Terms of Service: https://policies.google.com/terms
* Privacy Policy: https://policies.google.com/privacy

= Cloudflare Turnstile =
Used by the Captcha module when the administrator selects Cloudflare Turnstile. Challenge tokens are verified server-side with Cloudflare.
* Service: https://www.cloudflare.com/products/turnstile/
* Terms of Service: https://www.cloudflare.com/terms/
* Privacy Policy: https://www.cloudflare.com/privacypolicy/

= hCaptcha =
Used by the Captcha module when the administrator selects hCaptcha. Challenge responses are verified server-side with hCaptcha.
* Service: https://www.hcaptcha.com/
* Terms of Service: https://www.hcaptcha.com/terms
* Privacy Policy: https://www.hcaptcha.com/privacy

= Pexels API =
Used to search and import free stock photos for AI-generated pages when the administrator has configured a Pexels API key.
* Service: https://www.pexels.com/
* Terms of Service: https://www.pexels.com/terms-of-service/
* Privacy Policy: https://www.pexels.com/privacy-policy/

= Pixabay API =
Used to search and import free stock photos as an alternative image source when configured by the administrator.
* Service: https://pixabay.com/
* Terms of Service: https://pixabay.com/service/terms/
* Privacy Policy: https://pixabay.com/service/privacy/

== Screenshots ==

1. Dashboard overview
2. Contact Channels configuration
3. SMTP Mail settings
4. Maintenance Mode with template selector
5. AI Hub — provider connection

== Changelog ==

= 4.7 =
* Added AI Hub module with multi-provider support (Gemini, Claude, OpenAI) and 3-tier fallback chain.
* Added Spam Filter module with honeypot, rate limiting, IP/country blocking, keyword filtering.
* Added Email Log module to track all outgoing emails with SMTP diagnostics.
* Added Captcha module supporting Math Quiz, Google reCAPTCHA, Cloudflare Turnstile, and hCaptcha.
* Added Form Manager module to collect and manage form submissions.
* Added Maintenance Mode module with 5 customizable templates and AI content generation.
* Added AI Payment module for WooCommerce: OCR receipt verification, fraud detection, risk alerts.
* Added AI Token Statistics page with usage tracking per provider.
* Security: added ABSPATH guard to all PHP files.
* Security: all user-supplied data in database queries uses $wpdb->prepare().
* Added uninstall.php to clean up all plugin data on deletion.
* Fixed: toggle is locked/disabled when AI provider is not connected.
* Fixed: eye icon on API Key input now toggles correctly between show/hide states.
* i18n: added full English (en_US) translation.

= 4.6 =
* WooCommerce Toolkit enhancements: platform links, order search by phone number.
* Pop-up module: scheduling, display rules, newsletter pop-up.
* Security module: custom login URL, disable file editing.
* Added SVG upload support.
