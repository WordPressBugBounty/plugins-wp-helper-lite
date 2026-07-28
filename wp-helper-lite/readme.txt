=== WP Helper Premium ===
Contributors: matbao
Tags: contact button, SMTP, maintenance mode, security, woocommerce
Requires at least: 6.7
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 4.7.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

All-in-one WordPress toolkit: contact channels, SMTP, maintenance, AI, spam filter, WooCommerce — one plugin.

== Description ==

**WP Helper Premium** is an all-in-one WordPress toolkit that consolidates 8 essential site management modules into a single plugin. Every module is independent — activate only what you need from a unified dashboard.

= Contact Channels =
Add a floating contact widget so visitors can reach you instantly.

* Greeting card — title, short description, and online/offline status
* Floating trigger button — custom color and left/right position
* Hotline card — separate info for online and offline hours
* Staff list — multiple agents with names and contact info

= Header & Footer =
Inject tracking, analytics, or custom code without editing theme files.

* Three code areas — `<head>`, body-open (`<body>`), and footer (`</body>`)
* Supports any HTML, JavaScript, or CSS snippet
* Applies site-wide and survives theme updates

= Pop-up =
Promotional and newsletter pop-ups with full scheduling and display control.

* Form pop-up — collects leads, integrates with Contact Form 7, WPForms, Gravity Forms, Ninja Forms
* Banner pop-up — image, title, subtitle, and CTA button
* Display rules — delay, cookie duration, and max display count

= Maintenance Mode =
Put your site under a professional maintenance page while you work.

* 6 templates — Dark, Light, Gradient, Construction, Corporate, Cyberpunk (Neon)
* AI content generation — auto-writes heading, sub-heading, and description (requires AI Hub)
* Countdown timer — shows visitors when the site returns
* Visitor stats — page views, unique visitors, and bounce rate while active
* Admin bypass — logged-in administrators see the site normally

= Email & Contact =
Manage every inbound form and outbound email from one place.

* Form Manager — collect, view, and export contact form submissions
* SMTP Config — reliable delivery via Gmail, Outlook, Zoho Mail, Yahoo Mail, or any custom SMTP server
* Spam Filter — honeypot, rate limiting, IP/country/keyword blocking, DNSBL check
* CAPTCHA — Math Quiz, Google reCAPTCHA, Cloudflare Turnstile, or hCaptcha
* Email Log — status, headers, and body of every email sent, with resend and CSV export

= WooCommerce Toolkit =
Extend WooCommerce with sales and operations tools — no extra plugins needed.

* Wallet Payment — Momo, ZaloPay, VNPay, ShopeePay
* Payment Templates — reusable info blocks for order/CRM management
* E-commerce Links — connect product listings to Shopee, Lazada, TikTok Shop
* Buy Now (CTA) button — custom placement and text on product pages
* Thank You page — customize post-purchase content
* AI Payment Verification — scan bank transfer receipts via OCR to auto-verify payments and flag fraud (requires AI Hub)

= AI Hub =
Connect Google Gemini, Anthropic Claude, or OpenAI GPT to power AI features site-wide.

* AI Writer — drafts blog posts and product descriptions
* AI Security — scans the site for vulnerabilities and misconfigurations
* AI SEO — audits pages and suggests on-page SEO improvements
* AI Payment — verifies bank transfer receipts via OCR and flags fraud risk
* Token stats & fallback — tracks usage per provider with automatic 3-tier fallback

= Protection & Optimization =
Harden and speed up your site with individual toggles — enable only what you need.

* Security — disable XML-RPC, hide WP version, custom login URL, disable copy/right-click
* Performance — remove query strings, disable embeds, Google Fonts, or Dashicons
* Productivity — duplicate pages/posts/menus, redirect 404s to the homepage, enable SVG uploads

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

This plugin does not track users, store personal data, or use cookies by default. Certain optional features transmit data to third-party services — see **External Services** below. All external calls are initiated server-side; most occur only when the relevant feature is enabled and configured with an API key by a site administrator, except the AI Writer image fallback (Pollinations.ai / Picsum.photos) described below, which requires no API key and runs automatically whenever AI Writer generates article images.

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

= Pollinations.ai =
Used by AI Writer to automatically generate an article image from the article's own text prompt whenever Pexels/Pixabay are not configured or return no result. No API key is required — this call happens automatically as part of AI Writer's image step, with no separate toggle. Data sent: an AI-generated image prompt derived from the article content.
* Service: https://pollinations.ai/
* Terms of Service: https://pollinations.ai/
* Privacy Policy: https://pollinations.ai/

= Picsum.photos =
Used by AI Writer as a final placeholder image source when no other image source (Pexels, Pixabay, Pollinations.ai) returns a usable image. No API key is required and no article content is sent — only a random seed value used to pick a placeholder photo.
* Service: https://picsum.photos/
* Terms of Service: https://picsum.photos/
* Privacy Policy: https://picsum.photos/

= online.gov.vn =
Used by the Ministry of Industry and Trade (Bộ Công Thương) registration badge module, part of the WooCommerce Toolkit, to verify that a registration/notification link entered by the site administrator is a genuine online.gov.vn page and actually corresponds to the current website's domain. Data sent: the online.gov.vn URL provided by the administrator (or, as a fallback, a request to the site's own homepage to detect an existing badge link). No personal data is transmitted.
* Service: https://online.gov.vn/
* Terms of Service: https://online.gov.vn/
* Privacy Policy: https://online.gov.vn/

= Telegram Bot API =
Used by the optional AI Payment module, when the administrator enables the Telegram notification channel and supplies their own bot token and chat ID, to alert staff about a customer's bank-transfer confirmation. Data sent: order number, customer name, phone number, order total, and — when provided by the customer — the sender name, bank, last 4 account digits, and receipt image URL.
* Service: https://core.telegram.org/bots/api
* Terms of Service: https://telegram.org/tos
* Privacy Policy: https://telegram.org/privacy

= Discord Webhook =
Used by the optional AI Payment module, when the administrator enables the Discord notification channel and supplies their own channel webhook URL, to alert staff about a customer's bank-transfer confirmation. Data sent: order number, customer name, phone number, order total, and — when provided by the customer — the sender name, bank, last 4 account digits, and receipt image URL.
* Service: https://discord.com/
* Terms of Service: https://discord.com/terms
* Privacy Policy: https://discord.com/privacy

= Custom Webhook (AI Payment) =
When the administrator configures a custom webhook URL under the AI Payment module, the same bank-transfer confirmation data (order ID, order number, customer name, phone, order total, sender name, bank, last 4 account digits, and receipt URL) is sent to that URL as JSON.
* Service: administrator-supplied URL
* Terms of Service: n/a (destination controlled by the site administrator)
* Privacy Policy: n/a (destination controlled by the site administrator)

== Frequently Asked Questions ==

= Do I need to activate every module? =

No. WP Helper is organized into independent modules (Contact Channels, Header & Footer, Pop-up, Maintenance Mode, Email & Contact, WooCommerce Toolkit, AI Hub, Protection & Optimization). Enable only the ones you need from the dashboard — disabled modules add no overhead.

= Do I need an API key to use the plugin? =

No, not for the core modules. AI-powered features (AI Writer, AI Security, AI SEO, AI Payment Verification, and AI-generated maintenance-page content) require you to connect your own Google Gemini, Anthropic Claude, or OpenAI API key under AI Hub. Everything else works without any external account.

= Does the plugin send any data externally by default? =

No. All external connections are optional and initiated server-side, occurring only when the relevant feature is used. Most also require the administrator to configure an API key, with the exception of AI Writer's Pollinations.ai / Picsum.photos image fallback, which needs no key and runs automatically as part of AI Writer's image step. See **External Services** above for the full list of third-party services and what is sent to each.

= Which SMTP providers are supported? =

Gmail, Outlook, Zoho Mail, Yahoo Mail, or any custom SMTP server via the SMTP Config module.

= Which CAPTCHA providers can I use? =

The built-in Math Quiz challenge requires no external service. You can also choose Google reCAPTCHA, Cloudflare Turnstile, or hCaptcha — each requires your own site key and secret from the respective provider.

= Does the Wallet Payment module move money automatically? =

No. It adds MoMo, ZaloPay, VNPay, and ShopeePay as bank-transfer-style payment options in WooCommerce and offers optional AI-assisted OCR verification of uploaded transfer receipts (requires AI Hub). You still configure the underlying gateway/bank details yourself.

= What happens to my data when I uninstall the plugin? =

Uninstalling removes the plugin's settings and the custom database tables it created (spam logs, CAPTCHA logs, email logs, form submissions, and related data).

= Is the WooCommerce registration badge module only for Vietnamese stores? =

The Ministry of Industry and Trade (Bộ Công Thương) registration badge module under the WooCommerce Toolkit is specific to Vietnamese e-commerce compliance. Other WooCommerce Toolkit features (Wallet Payment, E-commerce Links, Buy Now button, Thank You page customization) are not region-specific.

== Screenshots ==

1. Dashboard — module overview with live status indicators for all features
2. AI Hub — multi-provider connection and 3-tier fallback configuration
3. Maintenance Mode — 5-template selector with AI content generation
4. Contact Channels — floating button builder with device visibility controls
5. Email & Contact — SMTP configuration so notifications don't land in spam
6. AI Token Statistics — usage tracking per provider with daily breakdown
7. Spam Filter — honeypot, rate limiting, IP/country blocking, and keyword filters
8. Pop-up — form/newsletter and banner popups with scheduling and display rules
9. WooCommerce Toolkit — Order Success Page with AI Payment Verification
10. Security & Optimization — individual security and performance toggles in one panel

== Changelog ==

= 4.7.6 =
* Security: fixed a server-side request forgery (SSRF) issue in the bank-transfer confirmation flow — a crafted receipt URL could previously cause the server to fetch internal or cloud-metadata addresses; URLs are now validated against private and link-local IP ranges both when the URL is saved and again right before the server fetches it.
* Security: closed a residual SSRF path in the Ministry of Industry and Trade registration badge check — a redirect returned by the official endpoint could previously be followed to an external host; each redirect hop is now re-validated before it is followed.
* Security: replaced a legacy parallel-download routine that disabled SSL certificate verification with the WordPress HTTP API, removing a man-in-the-middle exposure when fetching AI-suggested images.
* Security: added a short-lived, single-use guard against request replay on the background AI Writer AJAX endpoint.
* Security: all bundled third-party libraries (CodeMirror, Font Awesome, jQuery DirtyForms, SheetJS) are now shipped locally with the plugin instead of being loaded from external CDNs at runtime.
* Security: the AI Payment Vision-based receipt verification endpoint now checks that AI Payment/OCR is actually enabled before running, instead of relying solely on the admin UI to hide the button.
* Security: bank-transfer confirmation is now rejected for orders that aren't using a wallet or bank-transfer payment method.
* Security: CSV/spreadsheet exports (Form Manager) now neutralize leading `=`, `+`, `-`, `@` characters in exported values to prevent formula injection when the file is opened in Excel or Google Sheets.
* Security: the plugin's own admin pages no longer suppress admin notices from WordPress core or other plugins.
* Improve: self-hosted the Google Fonts used on the Maintenance Mode and thank-you page templates — visitor IP addresses are no longer sent to Google when those pages are viewed.
* Fix: removed a leftover Google Fonts CDN link that was still being enqueued alongside the self-hosted version on the thank-you page, which had kept sending visitor requests to Google.
* Fix: the Pop-up module's stylesheet was loading a Google Fonts CDN link on every page — even with Pop-up disabled — instead of the font already bundled with the plugin; it now loads locally.
* Fix: AI Payment receipt images stored in the site's own uploads folder are no longer incorrectly blocked by the SSRF safety check on local/dev/LAN environments.
* Fix: the Ministry of Industry and Trade registration badge check no longer intermittently times out on sites where the official endpoint requires several redirect hops.
* Fix: the Ministry of Industry and Trade badge "Check" action now saves the verified link together with the registration status, so the public badge always links to the link that was actually verified.
* Fix: badge detection on the homepage now checks every online.gov.vn link found on the page instead of stopping at the first one, avoiding a false "not registered" result.
* Fix: "Resend" on a logged email now includes its original attachments, which were previously dropped.
* Fix: added a setting to actually disable email logging from the Email Log screen; it could previously only be turned off by editing the database directly.
* Fix: the Email Log "maximum log count" setting is now enforced by the daily cleanup job, in addition to the existing retention-by-days setting.
* Fix: the Email Log detail popup now escapes email subject/address/SMTP response before rendering, for consistency with the Spam Filter and CAPTCHA log popups.
* Fix: the AI Payment Vision rate limit is now applied only once a receipt image has actually been fetched, instead of blocking retries after an early validation failure.
* Fix: corrected inconsistent text-domain usage across several files so all translatable strings load under the plugin's own text domain.
* Fix: uninstalling the plugin now also removes a leftover database table, four WooCommerce wallet options, remaining transients, and scheduled cron events (including a pending AI Writer background job), so no data is left behind.
* Docs: added a Frequently Asked Questions section to this readme.
* Docs: disclosed the Telegram, Discord, and custom-webhook notification channels used by the optional AI Payment module in the External Services section — these were previously undocumented.
* Docs: disclosed Pollinations.ai and Picsum.photos, the no-API-key image fallback used automatically by AI Writer, in the External Services section — these were previously undocumented.
* Docs: disclosed the online.gov.vn request made by the Ministry of Industry and Trade registration badge check in the External Services section.

= 4.7.5 =
* Fix: the AI Payment page showed "undefined" in every label and verification result panel — caused by a duplicate i18n object where the wrapping tab overwrote the sub-tab's translation strings.
* Fix: order-received (thank-you) page — a receipt image is now required before confirming a bank transfer when AI OCR is enabled; transfer confirmation is blocked on orders already processed by an admin; the page now syncs its state (button, banner, status badge) automatically right after confirmation instead of requiring a manual refresh.
* Fix: the close icon on the "Contact support" popup on the thank-you page was visually distorted due to a missing CSS box-sizing rule.
* Fix: the close (X) button on the "AI SEO Scan" confirmation popup was unclickable, hidden behind a decorative overlay.
* Fix: AI SEO Scan no longer invents non-existent internal links — suggestions are now limited to real posts/pages/products on the site and re-validated before being shown.
* Fix: AI SEO Scan no longer auto-inserts placeholder headings and content into a post when clicking Apply — suggested headings are shown for reference only, for the user to add manually.
* Fix: AI SEO Scan for Products now reads the correct product category taxonomy (product_cat) instead of the post category taxonomy, improving suggestion quality.
* Fix: corrected a malformed SVG icon that triggered a browser console error in the SEO scan confirmation popup.
* Improve: AI Payment quick actions (Confirm / Suspect / Reject / Request receipt) now warn clearly when the customer notification email could not be sent (missing billing email), instead of silently reporting success.

= 4.7.4 =
* Fix: Pop-up not showing on freshly installed sites running PHP 8 even when enabled — a loose comparison (`'' == 0`) evaluates to false on PHP 8 (unlike PHP 7), so the Pop-up template selector (Newsletter/Banner) failed to match. Default values are now normalized, self-healing even on sites that already saved an empty value.
* Fix: the on/off toggles (Pop-up, Contact Channels, SMTP, Maintenance...) and the Save button now proactively purge page cache (LiteSpeed, WP Rocket, W3TC, WP Fastest Cache, Breeze, SG Optimizer, Swift Performance) right after saving, preventing full-page-cache sites from showing stale settings.
* Fix: removed leftover internal debug routes `/feedback` and `/feedback/api` from the dev environment, which could incorrectly intercept a client site's real content if the path collided.

= 4.7.3 =
* Security: the AI connection form no longer pre-fills the saved API key (previously exposed via View Source despite password masking); the field stays empty and shows a placeholder hint when a key is already saved, so only pasting a new value updates the connection.
* Fixed: broken image on the login logo preview and the real login page when the previously selected logo had been deleted from the media library; added a file-existence check with fallback to the plugin's default logo.
* Improved: moved the maintenance-mode warning notice to the top of the Pop-up settings page so it's visible before scrolling.

= 4.7.2 =
* Security fix: unauthenticated `/wp-admin` requests were being redirected through the custom login URL slug, exposing it to anyone probing the default admin path and defeating the purpose of hiding the login URL. Blocked access now redirects to the homepage instead.
* Fixed: the custom login URL feature was incorrectly intercepting `admin-ajax.php` and `admin-post.php` requests, breaking public AJAX/form submissions (contact forms, popups, WooCommerce) for logged-out visitors.
* Fixed: Spam Filter "Detect code/script content" level selector was unreachable while its toggle was off, its dropdown menu could be clipped, and its on/off label could fail to update on click due to a JS scoping bug affecting all Spam Filter toggles.

= 4.7.1 =
* Fixed: fatal PHP parse error on PHP 7.4 servers caused by a PHP 8.0-only `match()` expression in the AI Payment verified-stats handler; replaced with a `switch` statement for broad host compatibility.

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
