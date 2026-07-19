=== WP Helper Premium ===
Contributors: matbao
Tags: contact button, SMTP, maintenance mode, security, woocommerce
Requires at least: 6.7
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 4.7.4
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

= 4.7.4 =
* Fix: Pop-up không hiển thị trên site mới cài (PHP 8) dù đã bật — do so sánh kiểu lỏng `'' == 0` trả về false trên PHP 8 (khác PHP 7), khiến điều kiện chọn mẫu Pop-up (Newsletter/Banner) không khớp. Đã chuẩn hoá giá trị mặc định, tự khắc phục kể cả với site đã bị lưu giá trị rỗng từ trước.
* Fix: các toggle bật/tắt (Pop-up, Kênh liên hệ, SMTP, Bảo trì...) và nút Lưu cài đặt giờ chủ động xoá cache trang (LiteSpeed, WP Rocket, W3TC, WP Fastest Cache, Breeze, SG Optimizer, Swift Performance) ngay sau khi lưu, tránh tình trạng site có cache toàn trang không cập nhật giao diện dù đã đổi cài đặt.
* Fix: gỡ route debug nội bộ `/feedback` và `/feedback/api` sót lại từ môi trường dev — có thể chặn nhầm nội dung thật của site khách nếu trùng đường dẫn.

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
