=== Contact Coldform ===

Plugin Name: Contact Coldform
Plugin URI: http://perishablepress.com/contact-coldform/
Description: Secure, lightweight and flexible contact form with plenty of options and squeaky clean markup.
Tags: captcha, contact, contact form, email, form, mail
Author: Jeff Starr
Author URI: http://monzilla.biz/
Contributors: specialk
Donate link: http://wp-tao.com/
Requires at least: 3.7
Tested up to: 4.0
Stable tag: trunk
Version: 20140922
License: GPL v2

Contact Coldform is a secure, lightweight and flexible contact form with plenty of options and squeaky clean markup.

== Description ==

[Contact Coldform](http://perishablepress.com/contact-coldform/) is a secure, lightweight, flexible contact form with plenty of options and squeaky clean markup. Coldform blocks spam while making it easy for your visitors to contact you from your WordPress-powered website. The comprehensive Settings Page makes it easy to take full control with plenty of options and several built-in themes for styling the form. Coldform delivers everything you need and nothing you don&rsquo;t -- no frills, no gimmicks, just pure contact-form satisfaction.

**Overview**

* Plug-n-play: use shortcode or template tag to display Coldform anywhere
* Sweet emails: Coldform sends descriptive, well-formatted messages in plain-text
* Safe and secure: Coldform blocks spam and filters malicious content
* Ultra-clean code: lightweight, standards-compliant, semantic, valid HTML markup
* Fully customizable: easy to configure and style from the Coldform Settings page

**Features**

* Slick, toggling-panel Settings Page makes it easy to customize and style Coldform
* Style Coldform using built-in "coldskins" or upload some custom CSS
* Provides template tag to display Coldform anywhere in your theme
* Provides shortcode to display Coldform on any post or page
* Displays customizable confirmation message to the sender

**Anti-spam &amp; Security**

* Captcha: Coldform includes challenge question/answer (w/ option to disable for users)
* Bot trap: hidden input field further reduces automated spam
* Firewall: secure form processing protects against bad bots and malicious input
* User-friendly: same-page error messages to help users complete required fields

**Customize Everything**

* Includes option to enable users to receive carbon copies
* Coldform message includes IP, host, agent, and other user details
* Customizable form-field captions, error messages, and success message
* Includes three built-in themes "coldskins" to style, or
* Style the Coldform with your own custom CSS
* Option to add a custom prefix to the subject line
* Option to disable the captcha for registered users

**Clean Codes**

Coldform brings delicious code on every front:

* Squeaky-clean PHP: every line like a fine wine
* Crispy-clean markup: valid, semantic source code with proper formatting
* Shiny-clean emails: Coldform emails deliver descriptive, well-formatted content
* Better performance: conditional loading of styles only when Coldform is displayed

**More Features**

* Works perfectly without JavaScript.
* Option to load CSS and custom styles only when/where Coldform is displayed
* Option to reset default settings
* Options to customize many aspects of the form
* Options to customize success, error, and spam messages
* Option to enable and disable CSS styles

== Installation ==

Typical plugin install: upload, activate, and customize in the WP Admin.

1. Unzip and upload the entire directory to your "plugins" folder and activate
2. Use the shortcode to display Coldform on any Post or Page, or:
3. Use the template tag to display the Coldform anywhere in your theme template.
4. Visit the Coldform Settings Page to configure your options and for more info.

Shortcode: `[coldform]`

Template tag: `<?php if (function_exists('contact_coldform_public')) contact_coldform_public(); ?>`

Check out the [Coldform Demo](http://bluefeed.net/wordpress/contact-coldform/) and its [CSS hooks](http://m0n.co/b).

For more information, visit the [Coldform Homepage](http://perishablepress.com/contact-coldform/).

== Upgrade Notice ==

__Important!__ Many things have changed in the new version of the plugin. Please copy/paste your current Coldform settings to a safe place. Then update the plugin as usual and compare the current settings with the previous ones. You know, just to make sure it's all good.

== Screenshots ==

Screenshots available at the [Coldform Homepage](http://perishablepress.com/contact-coldform/#screenshots).

== Changelog ==

= Version 20140922 =

* Tested with latest version of WP (4.0)
* Added option to show/hide the website field
* Increased minimum version to WP 3.7
* Added conditional check for minimum version function
* Updated mo/po translation files

= Version 20140305 =

* Improved localization support
* Generated new mo/po templates
* Bugfix: undefined index: coldform_carbon 
* Bugfix: replaced 'gmt_offset' with 'coldform_offset'
* Update: changed the description and default option for GMT setting

= Version 20140123 =

* Tested with latest version of WordPress (3.8)
* Added option to display or not display the anti-spam/captcha field
* Added trailing slash to URL for load_plugin_textdomain()

= Version 20131107 =

* Added uninstall.php file
* Added "rate this plugin" links
* Added support for i18n

= Version 20131103 =

* Edited readme.txt install steps for clarity
* Removed "&Delta;" from die() for better security
* Tested plugin with current version of WordPress (3.7) 

= Version 20130725 =

* Tightened form security
* Tightened plugin security

= Version 20130704 =

* Added localization support
* Cleaned/updated/optimize code
* Overview and Updates admin panels now open by default
* Resolve PHP Notices for "Undefined variable" and "Undefined Index"
* Changed the placeholder attribute of the antispam field

= Version 20130103 =

* Added margins to buttons (now required due to CSS changes in WP 3.5)

= Version 20121119 =

* Now supports both shortcodes: `[coldform]` and `[contact_coldform]`
* Renamed `register_my_style()` to `contact_coldform_register_style()`
* Removed border on all fieldsets via CSS
* Added padding to input and textareas via CSS
* Replaced answer with question in anti-spam placeholder
* Added placeholder attributes to error fields
* Fixed styles to load on success page

= Version 20121031 =

* rebuilt with cleaner, smarter code
* restructured markup, cleaner hooks
* revamped settings page with toggling
* includes three "coldskins" for styling
* enable user to upload custom CSS styles
* toggle on/off the built-in coldskins
* conditional load of styles only on Coldform
* improved markup for required, error, success output
* option to disable the captcha for registered users
* now use admin email, name, site title by default
* now using built-in wp_mail for email
* removed the credit link and option
* add option for subject line prefix
* add HTML5 placeholder attributes
* add hidden anti-spam field

= Version 0.88.1 =

* Compatibility with WordPress version 2.8.1 by setting `admin_menu`.

= Version 0.88.0 =

* Initial release.

== Frequently Asked Questions ==

Question: "Where does the shortcode go? Where does the template tag go?"

Answer: The shortcode may be used on any Post or Page. For example, if you log in to the Admin Area and view a Page named "Contact", you can add the shortcode anywhere in the Page content to display the form. To use the template tag, add it to the desired location in your theme template. For example, if you have a custom page template named "page-contact.php", you could add the template tag directly after `<?php the_content(); ?>` to display the form.

Question: "Where do the [CSS hooks](http://m0n.co/b) go? How do I change the CSS for the form?"

Answer: Visit the "Appearance and Styles" panel in the plugin settings. There you may configure appearance and add custom CSS for the form.

= Questions? =

I try to keep an eye on the WordPress forums, but it's best to [contact me](http://perishablepress.com/contact/) directly with questions or concerns. Thanks.

== Donations ==

I created this plugin with love for the WP community. To show support, consider purchasing one of my books: [The Tao of WordPress](http://wp-tao.com/), [Digging into WordPress](http://digwp.com/), or [.htaccess made easy](http://htaccessbook.com/).

Links, tweets and likes also appreciated. Thanks! :)