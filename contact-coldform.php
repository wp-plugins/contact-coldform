<?php 
/*
Plugin Name: Contact Coldform
Plugin URI: https://perishablepress.com/contact-coldform/
Description: Secure, lightweight and flexible contact form with plenty of options and squeaky clean markup.
Tags: captcha, contact, contact form, email, form, mail
Author: Jeff Starr
Author URI: http://monzilla.biz/
Donate link: http://m0n.co/donate
Contributors: specialk
Requires at least: 3.9
Tested up to: 4.2
Stable tag: trunk
Version: 20150507
Text Domain: coldform
Domain Path: /languages/
License: GPL v2 or later
*/

if (!function_exists('add_action')) die();
 
$contact_coldform_wp_vers = '3.9';
$contact_coldform_version = '20150507';
$contact_coldform_plugin  = __('Contact Coldform', 'coldform');
$contact_coldform_options = get_option('contact_coldform_options');
$contact_coldform_path    = plugin_basename(__FILE__); // 'contact-coldform/contact-coldform.php';
$contact_coldform_homeurl = 'https://perishablepress.com/contact-coldform/';

function coldform_i18n_init() {
	load_plugin_textdomain('coldform', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'coldform_i18n_init');

function contact_coldform_require_wp_version() {
	global $wp_version, $contact_coldform_path, $contact_coldform_plugin, $contact_coldform_wp_vers;
	if (version_compare($wp_version, $contact_coldform_wp_vers, '<')) {
		if (is_plugin_active($contact_coldform_path)) {
			deactivate_plugins($contact_coldform_path);
			$msg =  '<strong>' . $contact_coldform_plugin . '</strong> ' . __('requires WordPress ', 'coldform') . $contact_coldform_wp_vers . __(' or higher, and has been deactivated!', 'coldform') . '<br />';
			$msg .= __('Please return to the ', 'coldform') . '<a href="' . admin_url() . '">' . __('WordPress Admin area', 'coldform') . '</a> ' . __('to upgrade WordPress and try again.', 'coldform');
			wp_die($msg);
		}
	}
}
if (isset($_GET['activate']) && $_GET['activate'] == 'true') {
	add_action('admin_init', 'contact_coldform_require_wp_version');
}

$coldform_post_vars_name = ''; $coldform_post_vars_email = ''; $coldform_post_vars_response = ''; $coldform_post_vars_message = '';

if (isset($_POST['coldform_name']))     $coldform_post_vars_name     = sanitize_text_field($_POST['coldform_name']);
if (isset($_POST['coldform_email']))    $coldform_post_vars_email    = sanitize_text_field($_POST['coldform_email']);
if (isset($_POST['coldform_response'])) $coldform_post_vars_response = sanitize_text_field($_POST['coldform_response']);
if (isset($_POST['coldform_message']))  $coldform_post_vars_message  = sanitize_text_field($_POST['coldform_message']);

$name = ''; $email = ''; $response = ''; $message = ''; $verify = ''; $error = '';

$contact_coldform_strings = array(
	'name'     => '<input name="coldform_name" id="coldform_name" type="text" size="33" maxlength="99" value="' . $coldform_post_vars_name . '" placeholder="'. __('Your name', 'coldform') .'" />', 
	'email'    => '<input name="coldform_email" id="coldform_email" type="text" size="33" maxlength="99" value="' . $coldform_post_vars_email . '" placeholder="'. __('Your email', 'coldform') .'" />', 
	'response' => '<input name="coldform_response" id="coldform_response" type="text" size="33" maxlength="99" value="' . $coldform_post_vars_response . '" placeholder="'. __('Please type the correct response', 'coldform') .'" />', 
	'message'  => '<textarea name="coldform_message" id="coldform_message" cols="33" rows="7" placeholder="'. __('Your message', 'coldform') .'">' . $coldform_post_vars_message . '</textarea>', 
	'verify'   => '<input name="coldform_verify" type="text" size="33" maxlength="99" value="" />', 
	'error'    => '',
);

function contact_coldform_filter_input($input) {
	$maliciousness = false;
	$denied_inputs = array("\r", "\n", "mime-version", "content-type", "cc:", "to:");
	foreach($denied_inputs as $denied_input) {
		if(strpos(strtolower($input), strtolower($denied_input)) !== false) {
			$maliciousness = true;
			break;
		}
	}
	return $maliciousness;
}

function contact_coldform_spam_question($input) {
	global $contact_coldform_options;
	$response = $contact_coldform_options['coldform_response'];
	$response = stripslashes(trim($response));
	if ($contact_coldform_options['coldform_casing'] == true) {
		return (strtoupper($input) == strtoupper($response));
	} else {
		return ($input == $response);
	}
}

function contact_coldform_get_ip_address() {
	if (isset($_SERVER)) {
		if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$ip_address = $_SERVER["HTTP_X_FORWARDED_FOR"];
		} elseif(isset($_SERVER["HTTP_CLIENT_IP"])) {
			$ip_address = $_SERVER["HTTP_CLIENT_IP"];
		} else {
			$ip_address = $_SERVER["REMOTE_ADDR"];
		}
	} else {
		if(getenv('HTTP_X_FORWARDED_FOR')) {
			$ip_address = getenv('HTTP_X_FORWARDED_FOR');
		} elseif(getenv('HTTP_CLIENT_IP')) {
			$ip_address = getenv('HTTP_CLIENT_IP');
		} else {
			$ip_address = getenv('REMOTE_ADDR');
		}
	}
	return $ip_address;
}

function contact_coldform_input_filter() {
	global $contact_coldform_options, $contact_coldform_strings;
	$coldform_style = $contact_coldform_options['coldform_style'];
	$coldform_quest = $contact_coldform_options['coldform_question'];
	$pass = true;

	if (isset($_POST['coldform_name']))     $coldform_name     = sanitize_text_field($_POST['coldform_name']);
	if (isset($_POST['coldform_email']))    $coldform_email    = sanitize_text_field($_POST['coldform_email']);
	if (isset($_POST['coldform_topic']))    $coldform_topic    = sanitize_text_field($_POST['coldform_topic']);
	if (isset($_POST['coldform_website']))  $coldform_website  = sanitize_text_field($_POST['coldform_website']);
	if (isset($_POST['coldform_message']))  $coldform_message  = sanitize_text_field($_POST['coldform_message']);
	if (isset($_POST['coldform_response'])) $coldform_response = sanitize_text_field($_POST['coldform_response']);

	if (!isset($_POST['coldform_key'])) { 
		return false; 
	}
	if (empty($_POST['coldform_name'])) {
		$pass = false;
		$fail = 'empty';
		$contact_coldform_strings['name'] = '<input name="coldform_name" id="coldform_name" type="text" size="33" maxlength="99" value="'. $coldform_name .'" class="coldform-error-input" '. $coldform_style .' placeholder="'. __('Your name', 'coldform') .'" />';
	}
	if (!is_email($_POST['coldform_email'])) {
		$pass = false;
		$fail = 'empty';
		$contact_coldform_strings['email'] = '<input name="coldform_email" id="coldform_email" type="text" size="33" maxlength="99" value="'. $coldform_email .'" class="coldform-error-input" '. $coldform_style .' placeholder="'. __('Your email', 'coldform') .'" />';
	}
	if (!empty($_POST['coldform_verify'])) { 
		$pass = false; 
		$fail = 'verify';
		$contact_coldform_strings['verify'] = '<input name="coldform_verify" type="text" size="33" maxlength="99" class="coldform-error-input" value="" '. $coldform_style .' />';
	}
	if (empty($_POST['coldform_message'])) {
		$pass = false; 
		$fail = 'empty';
		$contact_coldform_strings['message'] = '<textarea name="coldform_message" id="coldform_message" cols="33" rows="11" class="coldform-error-input" '. $coldform_style .' placeholder="'. __('Your message', 'coldform') .'">'. $coldform_message .'</textarea>';
	}
	if (contact_coldform_filter_input($coldform_name) || contact_coldform_filter_input($coldform_email)) {
		$pass = false; 
		$fail = 'malicious';
	}
	if ($contact_coldform_options['coldform_trust'] == false) {
		if (empty($_POST['coldform_response'])) {
			$pass = false; 
			$fail = 'empty';
			$contact_coldform_strings['response'] = '<input name="coldform_response" id="coldform_response" type="text" size="33" maxlength="99" value="'. $coldform_response .'" class="coldform-error-input" '. $coldform_style .' placeholder="'. $coldform_quest .'" />';
		}
		if (!contact_coldform_spam_question($_POST['coldform_response'])) {
			$pass = false;
			$fail = 'wrong';
			$contact_coldform_strings['response'] = '<input name="coldform_response" id="coldform_response" type="text" size="33" maxlength="99" value="'. $coldform_response .'" class="coldform-error-input" '. $coldform_style .' placeholder="'. $coldform_quest .'" />';
		}	
	}
	if ($pass == true) {
		return true;
	} else {
		if ($fail == 'malicious') {
			$contact_coldform_strings['error'] = '<p class="coldform-error">'. __('Please do not include any of the following in the Name or Email fields: linebreaks, or the phrases mime-version, content-type, cc: or to:', 'coldform') .'</p>';
		} elseif ($fail == 'empty') {
			$contact_coldform_strings['error'] = $contact_coldform_options['coldform_error'];
		} elseif ($fail == 'wrong') {
			$contact_coldform_strings['error'] = $contact_coldform_options['coldform_spam'];
		} elseif ($fail == 'verify') {
			$contact_coldform_strings['error'] = '<p class="coldform-error">'. __('Please leave the human-verification field empty and try again.', 'coldform') .'</p>';
		}
		return false;
	}
}

function contact_coldform_register_style() {
	global $contact_coldform_options, $contact_coldform_version;
	$coldform_coldskin = $contact_coldform_options['coldform_coldskin'];
	if ($coldform_coldskin == 'coldskin_default') {
		$coldskin = 'default.css';
	} elseif ($coldform_coldskin == 'coldskin_classic') {
		$coldskin = 'classic.css';
	} elseif ($coldform_coldskin == 'coldskin_dark') {
		$coldskin = 'dark.css';
	}
	$enable_styles = $contact_coldform_options['coldform_styles'];
	if ($enable_styles == true) {
		$coldform_url = $contact_coldform_options['coldform_url'];
		$current_url = trailingslashit('http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
		if ($coldform_url !== '') {
			if ($coldform_url == $current_url) {
				wp_register_style('coldform', plugins_url() . '/contact-coldform/coldskins/coldskin-' . $coldskin, array(), $contact_coldform_version, 'all');
				wp_enqueue_style('coldform');
			}
		} else {
			wp_register_style('coldform', plugins_url() . '/contact-coldform/coldskins/coldskin-' . $coldskin, array(), $contact_coldform_version, 'all');
			wp_enqueue_style('coldform');
		}
	}
}
add_action('init', 'contact_coldform_register_style');

function contact_coldform_shortcode() {
	if (contact_coldform_input_filter()) {
		return contact_coldform();
	} else {
		return contact_coldform_display_form();
	}
}
add_shortcode('coldform','contact_coldform_shortcode');
add_shortcode('contact_coldform','contact_coldform_shortcode');

function contact_coldform_public() {
	if(contact_coldform_input_filter()) {
		echo contact_coldform();
	} else {
		echo contact_coldform_display_form();
	}
}

function contact_coldform_display_form() {
	global $contact_coldform_options, $contact_coldform_strings;

	$question = $contact_coldform_options['coldform_question'];
	$nametext = $contact_coldform_options['coldform_nametext'];
	$mailtext = $contact_coldform_options['coldform_mailtext'];
	$sitetext = $contact_coldform_options['coldform_sitetext'];
	$subjtext = $contact_coldform_options['coldform_subjtext'];
	$messtext = $contact_coldform_options['coldform_messtext'];
	$copytext = $contact_coldform_options['coldform_copytext'];
	$lgndtext = $contact_coldform_options['coldform_welcome'];
	$captcha  = $contact_coldform_options['display_captcha'];

	if ($contact_coldform_options['coldform_custom'] !== '') {
		$coldform_custom = '<style type="text/css">' . $contact_coldform_options['coldform_custom'] . '</style>';
	} else { $coldform_custom = ''; }
	
	$coldform_captcha = '';
	if ($contact_coldform_options['coldform_trust'] == false) {
		if ($captcha) {
			$coldform_captcha = '<fieldset class="coldform-response">
					<label for="coldform_response">' . $question . '</label>
					' . $contact_coldform_strings['response'] . '
				</fieldset>';
		}
	}
	
	if ($contact_coldform_options['coldform_carbon'] == true) {
		$coldform_carbon = '<fieldset class="coldform-carbon">
					<input id="coldform_carbon" name="coldform_carbon" type="checkbox" value="1" checked="checked" /> 
					<label for="coldform_carbon">' . $copytext . '</label>
				</fieldset>';
	} else { $coldform_carbon = ''; }
	
	$coldform_website = '';
	$coldform_website_value = '';
	if (isset($_POST['coldform_website'])) $coldform_website_value = sanitize_text_field($_POST['coldform_website']);
	if (isset($contact_coldform_options['display_website']) && $contact_coldform_options['display_website'] == true) {
		$coldform_website = '<fieldset class="coldform-website">
					<label for="coldform_website">' . $sitetext . '</label>
					<input name="coldform_website" id="coldform_website" type="text" size="33" maxlength="177" value="' . $coldform_website_value . '" placeholder="'. __('Your website', 'coldform') .'" />
				</fieldset>';
	}
	
	$coldform_topic = '';
	if (isset($_POST['coldform_topic'])) $coldform_topic = sanitize_text_field($_POST['coldform_topic']);
	
	$coldform = (
		$contact_coldform_strings['error'] . '
		<!-- Contact Coldform @ https://perishablepress.com/contact-coldform/ -->
		<div id="coldform">
			<form action="' . get_permalink() . '" method="post">
				<legend title="'. __('Note: text only, no markup.', 'coldform') .'">' . $lgndtext . '</legend>
				<fieldset class="coldform-name">
					<label for="coldform_name">' . $nametext . '</label>
					' . $contact_coldform_strings['name'] . '
				</fieldset>
				<fieldset class="coldform-email">
					<label for="coldform_email">' . $mailtext . '</label>
					' . $contact_coldform_strings['email'] . '
				</fieldset>
				' . $coldform_website . '
				<fieldset class="coldform_topic">
					<label for="coldform_topic">' . $subjtext . '</label>
					<input name="coldform_topic" id="coldform_topic" type="text" size="33" maxlength="177" value="' . $coldform_topic . '" placeholder="'. __('Subject of email', 'coldform') .'" />
				</fieldset>
				' . $coldform_captcha . '
				<fieldset class="coldform-message">
					<label for="coldform_message">' . $messtext . '</label>
					' . $contact_coldform_strings['message'] . '
				</fieldset>
				<fieldset id="coldform_verify" style="display:none;">
					<label for="coldform_verify">'. __('Human verification: leave this field empty.', 'coldform') .'</label>
					' . $contact_coldform_strings['verify'] . '
				</fieldset>
				' . $coldform_carbon . '
				<div class="coldform-submit">
					<input name="coldform_submit" type="submit" value="'. __('Send it!', 'coldform') .'" id="coldform_submit" />
					<input name="coldform_key" type="hidden" value="process" />
				</div>
			</form>
		</div>
		' . $coldform_custom . '
		<script type="text/javascript">(function(){var e = document.getElementById("coldform_verify");e.parentNode.removeChild(e);})();</script>
		<div class="clear">&nbsp;</div>
		');
	return $coldform;
}

function contact_coldform($content='') {
	global $contact_coldform_options, $contact_coldform_strings;

	$prefix_topic = $contact_coldform_options['coldform_prefix'] . sanitize_text_field($_POST['coldform_topic']);
	$user_topic = sanitize_text_field($_POST['coldform_topic']);

	if (empty($_POST['coldform_topic'])) {
		$topic = $contact_coldform_options['coldform_subject'];
	} elseif (!empty($_POST['coldform_topic'])) {
		$topic = $prefix_topic;
	}
	if (empty($_POST['coldform_carbon'])) {
		$copy  = __('No carbon copy sent.', 'coldform');
	} elseif (!empty($_POST['coldform_carbon'])) {
		$copy  = __('Copy sent to sender.', 'coldform');
	}
	if (empty($_POST['coldform_website'])) {
		$website = __('No website specified.', 'coldform');
		
	} elseif (!empty($_POST['coldform_website'])) {
		$website = sanitize_text_field($_POST['coldform_website']);
	}
	$recipient = $contact_coldform_options['coldform_email'];
	$recipname = $contact_coldform_options['coldform_name'];
	$recipsite = $contact_coldform_options['coldform_website'];
	$success   = $contact_coldform_options['coldform_success'];
	$thanks    = $contact_coldform_options['coldform_thanks'];
	$name      = sanitize_text_field($_POST['coldform_name']);
	$email     = sanitize_text_field($_POST['coldform_email']);

	$senderip  = contact_coldform_get_ip_address();
	$agent     = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
	$form      = sanitize_text_field(getenv("HTTP_REFERER"));
	$host      = gethostbyaddr($_SERVER['REMOTE_ADDR']);
	$offset    = $contact_coldform_options['coldform_offset'];
	$date      = date("l, F jS, Y @ g:i a", time()+$offset*60*60);

	$headers   = "MIME-Version: 1.0\n";
	$headers  .= "From: $name <$email>\n";
	$headers  .= "Reply-To: $email\n";
	$headers  .= "Return-Path: $email\n";
	$headers  .= "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";

	$message   = stripslashes(strip_tags(trim($_POST['coldform_message'])));
	$message   = wordwrap($message, 77, "\n");
	$fullmsg   = "Hello $recipname,

You are being contacted via $recipsite:

Name:     $name
Email:    $email
Carbon:   $copy
Website:  $website
Subject:  $topic
Message:

$message

-----------------------

Additional Information:

IP:     $senderip
Site:   $recipsite
URL:    $form
Time:   $date
Host:   $host
Agent:  $agent
Whois:  http://www.arin.net/whois/

";
	$fullmsg = stripslashes(strip_tags(trim($fullmsg)));
	wp_mail($recipient, $topic, $fullmsg, $headers);
	if (isset($_POST['coldform_carbon']) && $_POST['coldform_carbon'] == '1') {
		wp_mail($email, $topic, $fullmsg, $headers);
	}

	if ($contact_coldform_options['coldform_custom'] !== '') {
		$coldform_custom = '<style type="text/css">' . $contact_coldform_options['coldform_custom'] . '</style>';
	} else { $coldform_custom = ''; }

	$results = '<div id="coldform_thanks">' . $success . $thanks . 
'<pre><code>Date:       ' . $date . '
Name:       ' . $name    . '
Email:      ' . $email   . '
Carbon:     ' . $copy    . '
Website:    ' . $website . '
Subject:    ' . $user_topic . '
Message:    ' . $message . '</code></pre>
<p class="coldform-reset">[ <a href="'.$form.'">'. __('Click here to reset the form.', 'coldform') .'</a> ]</p>
</div>' . $coldform_custom;

	return $results;
}

function contact_coldform_plugin_action_links($links, $file) {
	global $contact_coldform_path;
	if ($file == $contact_coldform_path) {
		$contact_coldform_links = '<a href="' . get_admin_url() . 'options-general.php?page=' . $contact_coldform_path . '">' . __('Settings', 'coldform') .'</a>';
		array_unshift($links, $contact_coldform_links);
	}
	return $links;
}
add_filter ('plugin_action_links', 'contact_coldform_plugin_action_links', 10, 2);

function add_coldform_links($links, $file) {
	if ($file == plugin_basename(__FILE__)) {
		$rate_url = 'http://wordpress.org/support/view/plugin-reviews/' . basename(dirname(__FILE__)) . '?rate=5#postform';
		$links[] = '<a href="' . $rate_url . '" target="_blank" title="'. __('Click here to rate and review this plugin on WordPress.org', 'coldform') .'">'. __('Rate this plugin', 'coldform') .'</a>';
	}
	return $links;
}
add_filter('plugin_row_meta', 'add_coldform_links', 10, 2);

function contact_coldform_delete_plugin_options() {
	delete_option('contact_coldform_options');
}
if ($contact_coldform_options['default_options'] == 1) {
	register_uninstall_hook (__FILE__, 'contact_coldform_delete_plugin_options');
}

function contact_coldform_add_defaults() {
	$user_info = get_userdata(1);
	if ($user_info == true) {
		$admin_name = $user_info->user_login;
	} else {
		$admin_name = 'Mr. Smith';
	}
	$site_title = get_bloginfo('name');
	$admin_mail = get_bloginfo('admin_email');
	$tmp = get_option('contact_coldform_options');
	if(($tmp['default_options'] == '1') || (!is_array($tmp))) {
		$arr = array(
			'default_options'   => 0,
			'coldform_name'     => $admin_name,
			'coldform_website'  => $site_title,
			'coldform_email'    => $admin_mail,
			'coldform_offset'   => '0',
			'coldform_subject'  => __('Message sent from your contact form', 'coldform'),
			'coldform_success'  => '<p id=\'coldform_success\'>'. __('Success! Your message has been sent.', 'coldform') .'</p>',
			'coldform_error'    => '<p id=\'coldform_error\' class=\'coldform-error\'>'. __('Please complete the required fields.', 'coldform') .'</p>',
			'coldform_spam'     => '<p id=\'coldform_spam\' class=\'coldform-error\'>'. __('Incorrect response for challenge question. Please try again.', 'coldform') .'</p>',
			'coldform_style'    => 'style=\'border: 1px solid #CC0000;\'',
			'coldform_question' => '1 + 1 =',
			'coldform_response' => '2',
			'coldform_casing'   => false,
			'coldform_carbon'   => false,
			'coldform_nametext' => __('Name (Required)', 'coldform'),
			'coldform_mailtext' => __('Email (Required)', 'coldform'),
			'coldform_sitetext' => __('Website (Optional)', 'coldform'),
			'coldform_subjtext' => __('Subject (Optional)', 'coldform'),
			'coldform_messtext' => __('Message (Required)', 'coldform'),
			'coldform_copytext' => __('Carbon Copy?', 'coldform'),
			'coldform_prefix'   => __('Contact Coldform: ', 'coldform'),
			'coldform_trust'    => false,
			'coldform_styles'   => true,
			'coldform_coldskin' => 'coldskin_default',
			'coldform_custom'   => '',
			'coldform_url'      => '',
			'coldform_thanks'   => '<p class=\'coldform-thanks\'><span>'. __('Thanks for contacting me.', 'coldform') .'</span> '. __('The following information has been sent via email:', 'coldform') .'</p>',
			'coldform_welcome'  => '<strong>'. __('Hello!', 'coldform') .'</strong> '. __('Please use this contact form to send us an email.', 'coldform'),
			'display_captcha'   => true,
			'display_website'   => true,
		);
		update_option('contact_coldform_options', $arr);
	}
}
register_activation_hook (__FILE__, 'contact_coldform_add_defaults');

function contact_coldform_validate_options($input) {
	global $coldform_coldskins;

	if (!isset($input['default_options'])) $input['default_options'] = null;
	$input['default_options'] = ($input['default_options'] == 1 ? 1 : 0);
	
	$input['coldform_name'] = wp_filter_nohtml_kses($input['coldform_name']);
	$input['coldform_website'] = wp_filter_nohtml_kses($input['coldform_website']);
	$input['coldform_email'] = wp_filter_nohtml_kses($input['coldform_email']);
	$input['coldform_offset'] = wp_filter_nohtml_kses($input['coldform_offset']);
	$input['coldform_subject'] = wp_filter_nohtml_kses($input['coldform_subject']);
	
	// dealing with kses
	global $allowedposttags;
	$allowed_atts = array('align'=>array(), 'class'=>array(), 'id'=>array(), 'dir'=>array(), 'lang'=>array(), 'style'=>array(), 'xml:lang'=>array(), 'src'=>array(), 'alt'=>array());

	$allowedposttags['strong'] = $allowed_atts;
	$allowedposttags['small'] = $allowed_atts;
	$allowedposttags['span'] = $allowed_atts;
	$allowedposttags['abbr'] = $allowed_atts;
	$allowedposttags['code'] = $allowed_atts;
	$allowedposttags['div'] = $allowed_atts;
	$allowedposttags['img'] = $allowed_atts;
	$allowedposttags['h1'] = $allowed_atts;
	$allowedposttags['h2'] = $allowed_atts;
	$allowedposttags['h3'] = $allowed_atts;
	$allowedposttags['h4'] = $allowed_atts;
	$allowedposttags['h5'] = $allowed_atts;
	$allowedposttags['ol'] = $allowed_atts;
	$allowedposttags['ul'] = $allowed_atts;
	$allowedposttags['li'] = $allowed_atts;
	$allowedposttags['em'] = $allowed_atts;
	$allowedposttags['p'] = $allowed_atts;
	$allowedposttags['a'] = $allowed_atts;

	$input['coldform_success'] = wp_kses_post($input['coldform_success'], $allowedposttags);
	$input['coldform_error'] = wp_kses_post($input['coldform_error'], $allowedposttags);
	$input['coldform_spam'] = wp_kses_post($input['coldform_spam'], $allowedposttags);
	$input['coldform_style'] = wp_kses_post($input['coldform_style'], $allowedposttags);

	$input['coldform_question'] = wp_filter_nohtml_kses($input['coldform_question']);
	$input['coldform_response'] = wp_filter_nohtml_kses($input['coldform_response']);
	
	if (!isset($input['coldform_casing'])) $input['coldform_casing'] = null;
	$input['coldform_casing'] = ($input['coldform_casing'] == 1 ? 1 : 0);
	
	if (!isset($input['coldform_carbon'])) $input['coldform_carbon'] = null;
	$input['coldform_carbon'] = ($input['coldform_carbon'] == 1 ? 1 : 0);
	
	$input['coldform_nametext'] = wp_filter_nohtml_kses($input['coldform_nametext']);
	$input['coldform_mailtext'] = wp_filter_nohtml_kses($input['coldform_mailtext']);
	$input['coldform_sitetext'] = wp_filter_nohtml_kses($input['coldform_sitetext']);
	$input['coldform_subjtext'] = wp_filter_nohtml_kses($input['coldform_subjtext']);
	$input['coldform_messtext'] = wp_filter_nohtml_kses($input['coldform_messtext']);
	$input['coldform_copytext'] = wp_filter_nohtml_kses($input['coldform_copytext']);

	$input['coldform_prefix'] = wp_filter_nohtml_kses($input['coldform_prefix']);

	if (!isset($input['coldform_trust'])) $input['coldform_trust'] = null;
	$input['coldform_trust'] = ($input['coldform_trust'] == 1 ? 1 : 0);

	if (!isset($input['coldform_styles'])) $input['coldform_styles'] = null;
	$input['coldform_styles'] = ($input['coldform_styles'] == 1 ? 1 : 0);

	if (!isset($input['display_captcha'])) $input['display_captcha'] = null;
	$input['display_captcha'] = ($input['display_captcha'] == 1 ? 1 : 0);

	if (!isset($input['coldform_coldskin'])) $input['coldform_coldskin'] = null;
	if (!array_key_exists($input['coldform_coldskin'], $coldform_coldskins)) $input['coldform_coldskin'] = null;

	$input['coldform_custom'] = wp_filter_nohtml_kses($input['coldform_custom']);
	$input['coldform_url'] = wp_filter_nohtml_kses($input['coldform_url']);

	$input['coldform_thanks'] = wp_kses_post($input['coldform_thanks'], $allowedposttags);
	$input['coldform_welcome'] = wp_kses_post($input['coldform_welcome'], $allowedposttags);
	
	if (!isset($input['display_website'])) $input['display_website'] = null;
	$input['display_website'] = ($input['display_website'] == 1 ? 1 : 0);
	
	return $input;
}

$coldform_coldskins = array(
	'coldskin_default' => array(
		'value' => 'coldskin_default',
		'label' => __('Default styles', 'coldform')
	),
	'coldskin_classic' => array(
		'value' => 'coldskin_classic',
		'label' => __('Classic styles', 'coldform')
	),
	'coldskin_dark' => array(
		'value' => 'coldskin_dark',
		'label' => __('Dark styles', 'coldform')
	),
);

function contact_coldform_init() {
	register_setting('contact_coldform_plugin_options', 'contact_coldform_options', 'contact_coldform_validate_options');
}
add_action ('admin_init', 'contact_coldform_init');

function contact_coldform_add_options_page() {
	global $contact_coldform_plugin;
	add_options_page($contact_coldform_plugin, $contact_coldform_plugin, 'manage_options', __FILE__, 'contact_coldform_render_form');
}
add_action ('admin_menu', 'contact_coldform_add_options_page');

function contact_coldform_render_form() {
	global $contact_coldform_plugin, $contact_coldform_options, $contact_coldform_path, $contact_coldform_homeurl, $contact_coldform_version, $coldform_coldskins; 
	$offset = $contact_coldform_options['coldform_offset'];?>

	<style type="text/css">
		.mm-panel-overview { padding-left: 115px; background: url(<?php echo plugins_url(); ?>/contact-coldform/contact-coldform.png) no-repeat 15px 0; }

		#mm-plugin-options h2 small { font-size: 60%; }
		#mm-plugin-options h3 { cursor: pointer; }
		#mm-plugin-options h4, 
		#mm-plugin-options p { margin: 15px; line-height: 18px; }
		#mm-plugin-options ul { margin: 15px 15px 25px 40px; line-height: 16px; }
		#mm-plugin-options li { margin: 8px 0; list-style-type: disc; }
		#mm-plugin-options abbr { cursor: help; border-bottom: 1px dotted #dfdfdf; }
		
		.mm-table-wrap { margin: 15px; }
		.mm-table-wrap td { padding: 5px 10px; vertical-align: middle; }
		.mm-table-wrap .mm-table {}
		.mm-table-wrap .widefat th { padding: 10px 15px; vertical-align: middle; }
		.mm-table-wrap .widefat td { padding: 10px; vertical-align: middle; }

		.mm-item-caption { margin: 3px 0 0 3px; font-size: 11px; color: #777; line-height: 17px; }
		.mm-radio-inputs { margin: 5px 0; }
		.mm-code { background-color: #fafae0; color: #333; font-size: 14px; }

		#setting-error-settings_updated { margin: 10px 0; }
		#setting-error-settings_updated p { margin: 5px; }
		#mm-plugin-options .button-primary { margin: 0 0 15px 15px; }

		#mm-panel-toggle { margin: 5px 0; }
		#mm-credit-info { margin-top: -5px; }
		#mm-iframe-wrap { width: 100%; height: 250px; overflow: hidden; }
		#mm-iframe-wrap iframe { width: 100%; height: 100%; overflow: hidden; margin: 0; padding: 0; }
	</style>

	<div id="mm-plugin-options" class="wrap">
		<h2><?php echo $contact_coldform_plugin; ?> <small><?php echo 'v' . $contact_coldform_version; ?></small></h2>
		<div id="mm-panel-toggle"><a href="<?php get_admin_url() . 'options-general.php?page=' . $contact_coldform_path; ?>"><?php _e('Toggle all panels', 'coldform'); ?></a></div>

		<form method="post" action="options.php">
			<?php $contact_coldform_options = get_option('contact_coldform_options'); settings_fields('contact_coldform_plugin_options'); ?>

			<div class="metabox-holder">
				<div class="meta-box-sortables ui-sortable">
					<div id="mm-panel-overview" class="postbox">
						<h3><?php _e('Overview', 'coldform'); ?></h3>
						<div class="toggle">
							<div class="mm-panel-overview">
								<p>
									<strong><?php echo $contact_coldform_plugin; ?></strong> <?php _e(' delivers a lightweight, clean-markup contact-form that doesn&rsquo;t require JavaScript.', 'coldform'); ?>
									<?php _e('Use the shortcode to display the Coldform on a post or page. Use the template tag to display the Coldform anywhere in your theme template.', 'coldform'); ?>
								</p>
								<ul>
									<li><?php _e('To configure the Coldform, visit the', 'coldform'); ?> <a id="mm-panel-primary-link" href="#mm-panel-primary"><?php _e('Coldform Options', 'coldform'); ?></a>.</li>
									<li><?php _e('For the shortcode and template tag, visit', 'coldform'); ?> <a id="mm-panel-secondary-link" href="#mm-panel-secondary"><?php _e('Shortcodes &amp; Template Tags', 'coldform'); ?></a>.</li>
									<li><?php _e('To customize the contact form, visit', 'coldform'); ?> <a id="mm-panel-tertiary-link" href="#mm-panel-tertiary"><?php _e('Appearance &amp; Styles', 'coldform'); ?></a>.</li>
									<li><?php _e('For more information check the <code>readme.txt</code> and', 'coldform'); ?> <a href="<?php echo $contact_coldform_homeurl; ?>"><?php _e('Coldform Homepage', 'coldform'); ?></a>.</li>
									<li><?php _e('If you like this plugin, please', 'coldform'); ?> 
										<a href="http://wordpress.org/support/view/plugin-reviews/<?php echo basename(dirname(__FILE__)); ?>?rate=5#postform" title="<?php _e('Click here to rate and review this plugin on WordPress.org', 'coldform'); ?>" target="_blank">
											<?php _e('rate it at the Plugin Directory', 'coldform'); ?>&nbsp;&raquo;
										</a>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<div id="mm-panel-primary" class="postbox">
						<h3><?php _e('Coldform Options', 'coldform'); ?></h3>
						<div class="toggle<?php if (!isset($_GET["settings-updated"])) { echo ' default-hidden'; } ?>">
							<p><?php _e('Use these settings to configure and customize Contact Coldform.', 'coldform'); ?></p>
							<h4><?php _e('General options', 'coldform'); ?></h4>
							<div class="mm-table-wrap">
								<table class="widefat mm-table">
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_email]"><?php _e('Your Email', 'coldform'); ?></label></th>
										<td><input type="text" size="50" maxlength="200" name="contact_coldform_options[coldform_email]" value="<?php echo $contact_coldform_options['coldform_email']; ?>" />
										<div class="mm-item-caption"><?php _e('Where shall Coldform send your messages?', 'coldform'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_name]"><?php _e('Your Name', 'coldform'); ?></label></th>
										<td><input type="text" size="50" maxlength="200" name="contact_coldform_options[coldform_name]" value="<?php echo $contact_coldform_options['coldform_name']; ?>" />
										<div class="mm-item-caption"><?php _e('To whom shall Coldform address your messages?', 'coldform'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_website]"><?php _e('Your Website', 'coldform'); ?></label></th>
										<td><input type="text" size="50" maxlength="200" name="contact_coldform_options[coldform_website]" value="<?php echo $contact_coldform_options['coldform_website']; ?>" />
										<div class="mm-item-caption"><?php _e('What is the name of your blog or website?', 'coldform'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_subject]"><?php _e('Default Subject', 'coldform'); ?></label></th>
										<td><input type="text" size="50" maxlength="200" name="contact_coldform_options[coldform_subject]" value="<?php echo $contact_coldform_options['coldform_subject']; ?>" />
										<div class="mm-item-caption"><?php _e('This will be the subject of the email if none is specified.', 'coldform'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_prefix]"><?php _e('Subject Prefix', 'coldform'); ?></label></th>
										<td><input type="text" size="50" maxlength="200" name="contact_coldform_options[coldform_prefix]" value="<?php echo $contact_coldform_options['coldform_prefix']; ?>" />
										<div class="mm-item-caption"><?php _e('This will be prepended to any subject specified by the sender.', 'coldform'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_question]"><?php _e('Challenge Question', 'coldform'); ?></label></th>
										<td><input type="text" size="50" maxlength="200" name="contact_coldform_options[coldform_question]" value="<?php echo $contact_coldform_options['coldform_question']; ?>" />
										<div class="mm-item-caption"><?php _e('This question must be answered correctly before mail is sent.', 'coldform'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_response]"><?php _e('Challenge Response', 'coldform'); ?></label></th>
										<td><input type="text" size="50" maxlength="200" name="contact_coldform_options[coldform_response]" value="<?php echo $contact_coldform_options['coldform_response']; ?>" />
										<div class="mm-item-caption"><?php _e('This is the only correct answer to the challenge question.', 'coldform'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_casing]"><?php _e('Case Sensitivity', 'coldform'); ?></label></th>
										<td><input type="checkbox" name="contact_coldform_options[coldform_casing]" value="1" <?php if (isset($contact_coldform_options['coldform_casing'])) { checked('1', $contact_coldform_options['coldform_casing']); } ?> /> 
										<?php _e('Check this box if the challenge response should be case-insensitive.', 'coldform'); ?></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[display_captcha]"><?php _e('Display Captcha', 'coldform'); ?></label></th>
										<td><input type="checkbox" name="contact_coldform_options[display_captcha]" value="1" <?php if (isset($contact_coldform_options['display_captcha'])) { checked('1', $contact_coldform_options['display_captcha']); } ?> /> 
										<?php _e('Check this box to display the anti-spam/captcha field.', 'coldform'); ?></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_trust]"><?php _e('Trust Registered Users', 'coldform'); ?></label></th>
										<td><input type="checkbox" name="contact_coldform_options[coldform_trust]" value="1" <?php if (isset($contact_coldform_options['coldform_trust'])) { checked('1', $contact_coldform_options['coldform_trust']); } ?> /> 
										<?php _e('Check this box to disable the challenge question for registered users.', 'coldform'); ?></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_carbon]"><?php _e('Carbon Copies', 'coldform'); ?></label></th>
										<td><input type="checkbox" name="contact_coldform_options[coldform_carbon]" value="1" <?php if (isset($contact_coldform_options['coldform_carbon'])) { checked('1', $contact_coldform_options['coldform_carbon']); } ?> /> 
										<?php _e('Check this box if you want to enable users to receive carbon copies.', 'coldform'); ?></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[display_website]"><?php _e('Display Website Field', 'coldform'); ?></label></th>
										<td><input type="checkbox" name="contact_coldform_options[display_website]" value="1" <?php if (isset($contact_coldform_options['display_website'])) { checked('1', $contact_coldform_options['display_website']); } ?> /> 
										<?php _e('Check this box if you want to display the &ldquo;Website&rdquo; field in the contact form.', 'coldform'); ?></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_offset]"><?php _e('Time Offset', 'coldform'); ?></label></th>
										<td>
											<input type="text" size="50" maxlength="200" name="contact_coldform_options[coldform_offset]" value="<?php echo $contact_coldform_options['coldform_offset']; ?>" />
											<div class="mm-item-caption">
												<?php _e('Please specify any time offset here. For example, +7 or -7. If no offset or unsure, enter "0" (zero).', 'coldform'); ?><br />
												<?php _e('Current Coldform time:', 'coldform'); ?> <?php echo date("l, F jS, Y @ g:i a", time()+$offset*60*60); ?>
											</div>
										</td>
									</tr>
								</table>
							</div>
							<h4><?php _e('Coldform captions', 'coldform'); ?></h4>
							<div class="mm-table-wrap">
								<table class="widefat mm-table">
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_nametext]"><?php _e('Caption for Name Field', 'coldform'); ?></label></th>
										<td><input type="text" size="50" maxlength="200" name="contact_coldform_options[coldform_nametext]" value="<?php echo $contact_coldform_options['coldform_nametext']; ?>" />
										<div class="mm-item-caption"><?php _e('This is the caption that corresponds with the Name field.', 'coldform'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_mailtext]"><?php _e('Caption for Email Field', 'coldform'); ?></label></th>
										<td><input type="text" size="50" maxlength="200" name="contact_coldform_options[coldform_mailtext]" value="<?php echo $contact_coldform_options['coldform_mailtext']; ?>" />
										<div class="mm-item-caption"><?php _e('This is the caption that corresponds with the Email field.', 'coldform'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_sitetext]"><?php _e('Caption for Website Field', 'coldform'); ?></label></th>
										<td><input type="text" size="50" maxlength="200" name="contact_coldform_options[coldform_sitetext]" value="<?php echo $contact_coldform_options['coldform_sitetext']; ?>" />
										<div class="mm-item-caption"><?php _e('This is the caption that corresponds with the Website field.', 'coldform'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_subjtext]"><?php _e('Caption for Subject Field', 'coldform'); ?></label></th>
										<td><input type="text" size="50" maxlength="200" name="contact_coldform_options[coldform_subjtext]" value="<?php echo $contact_coldform_options['coldform_subjtext']; ?>" />
										<div class="mm-item-caption"><?php _e('This is the caption that corresponds with the Subject field.', 'coldform'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_messtext]"><?php _e('Caption for Message Field', 'coldform'); ?></label></th>
										<td><input type="text" size="50" maxlength="200" name="contact_coldform_options[coldform_messtext]" value="<?php echo $contact_coldform_options['coldform_messtext']; ?>" />
										<div class="mm-item-caption"><?php _e('This is the caption that corresponds with the Message field.', 'coldform'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_copytext]"><?php _e('Caption for Carbon Copy', 'coldform'); ?></label></th>
										<td><input type="text" size="50" maxlength="200" name="contact_coldform_options[coldform_copytext]" value="<?php echo $contact_coldform_options['coldform_copytext']; ?>" />
										<div class="mm-item-caption"><?php _e('This caption corresponds with the Carbon Copy checkbox.', 'coldform'); ?></div></td>
									</tr>
								</table>
							</div>
							<h4><?php _e('Success &amp; error messages', 'coldform'); ?></h4>
							<p><?php _e('Note: use single quotes for attributes, for example: <code>style=\'margin:10px;color:red;\'</code>', 'coldform'); ?></p>
							<div class="mm-table-wrap">
								<table class="widefat mm-table">
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_welcome]"><?php _e('Welcome Message', 'coldform'); ?></label></th>
										<td><textarea class="textarea" rows="3" cols="50" name="contact_coldform_options[coldform_welcome]"><?php echo esc_textarea($contact_coldform_options['coldform_welcome']); ?></textarea>
										<div class="mm-item-caption"><?php _e('This text/markup will appear before the Coldform, in the <code>&lt;legend&gt;</code> tag.', 'coldform'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_success]"><?php _e('Success Message', 'coldform'); ?></label></th>
										<td><textarea class="textarea" rows="3" cols="50" name="contact_coldform_options[coldform_success]"><?php echo esc_textarea($contact_coldform_options['coldform_success']); ?></textarea>
										<div class="mm-item-caption"><?php _e('When the form is sucessfully submitted, this success message will be displayed to the sender.', 'coldform'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_thanks]"><?php _e('Thank You Message', 'coldform'); ?></label></th>
										<td><textarea class="textarea" rows="3" cols="50" name="contact_coldform_options[coldform_thanks]"><?php echo esc_textarea($contact_coldform_options['coldform_thanks']); ?></textarea>
										<div class="mm-item-caption"><?php _e('When the form is sucessfully submitted, this thank-you message will be displayed to the sender.', 'coldform'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_spam]"><?php _e('Incorrect Response', 'coldform'); ?></label></th>
										<td><textarea class="textarea" rows="3" cols="50" name="contact_coldform_options[coldform_spam]"><?php echo esc_textarea($contact_coldform_options['coldform_spam']); ?></textarea>
										<div class="mm-item-caption"><?php _e('When the challenge question is answered incorrectly, this message will be displayed.', 'coldform'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_error]"><?php _e('Error Message', 'coldform'); ?></label></th>
										<td><textarea class="textarea" rows="3" cols="50" name="contact_coldform_options[coldform_error]"><?php echo esc_textarea($contact_coldform_options['coldform_error']); ?></textarea>
										<div class="mm-item-caption"><?php _e('If the user skips a required field, this message will be displayed.', 'coldform'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_style]"><?php _e('Error Fields', 'coldform'); ?></label></th>
										<td><textarea class="textarea" rows="3" cols="50" name="contact_coldform_options[coldform_style]"><?php echo esc_textarea($contact_coldform_options['coldform_style']); ?></textarea>
										<div class="mm-item-caption"><?php _e('Here you may specify the default CSS for error fields, or add other attributes.', 'coldform'); ?></div></td>
									</tr>
								</table>
							</div>
							<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'coldform'); ?>" />
						</div>
					</div>
					<div id="mm-panel-tertiary" class="postbox">
						<h3><?php _e('Appearance &amp; Styles', 'coldform'); ?></h3>
						<div class="toggle<?php if (!isset($_GET["settings-updated"])) { echo ' default-hidden'; } ?>">
							<h4><?php _e('Coldskin', 'coldform'); ?></h4>
							<p><?php _e('Default Coldskin styles are enabled by default. Here you may choose different Coldskin and/or add your own custom CSS styles. Note: for a complete list of CSS hooks for the Coldform, visit:', 'coldform'); ?> 
								<a href="http://m0n.co/b" target="_blank">http://m0n.co/b</a></p>
							<div class="mm-table-wrap">
								<table class="widefat mm-table">
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_coldskin]"><?php _e('Choose a Coldskin', 'coldform'); ?></label></th>
										<td>
											<?php if (!isset($checked)) $checked = '';
												foreach ($coldform_coldskins as $coldform_coldskin) {
													$radio_setting = $contact_coldform_options['coldform_coldskin'];
													if ('' != $radio_setting) {
														if ($contact_coldform_options['coldform_coldskin'] == $coldform_coldskin['value']) {
															$checked = "checked=\"checked\"";
														} else {
															$checked = '';
														}
													} ?>
													<div class="mm-radio-inputs">
														<input type="radio" name="contact_coldform_options[coldform_coldskin]" value="<?php esc_attr_e($coldform_coldskin['value']); ?>" <?php echo $checked; ?> /> 
														<?php echo $coldform_coldskin['label']; ?>
													</div>
											<?php } ?>
										</td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_styles]"><?php _e('Enable Coldskin?', 'coldform'); ?></label></th>
										<td><input name="contact_coldform_options[coldform_styles]" type="checkbox" value="1" <?php if (isset($contact_coldform_options['coldform_styles'])) { checked('1', $contact_coldform_options['coldform_styles']); } ?> /> 
										<?php _e('Here you may enable/disable the Coldskin selected above.', 'coldform'); ?></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_custom]"><?php _e('Custom Styles', 'coldform'); ?></label></th>
										<td><textarea class="textarea" rows="3" cols="50" name="contact_coldform_options[coldform_custom]"><?php echo esc_textarea($contact_coldform_options['coldform_custom']); ?></textarea>
										<div class="mm-item-caption"><?php _e('Here you may use any additional CSS to style the Coldform. For example:', 'coldform'); ?>
										<code>#coldform { margin: 10px; }</code> <?php _e('(do not include', 'coldform'); ?> <code>&lt;style&gt;</code> <?php _e('tags). Leave blank to disable.', 'coldform'); ?></div></td>
									</tr>
									<tr>
										<th scope="row"><label class="description" for="contact_coldform_options[coldform_url]"><?php _e('Coldform URL', 'coldform'); ?></label></th>
										<td><input type="text" size="50" maxlength="200" name="contact_coldform_options[coldform_url]" value="<?php echo $contact_coldform_options['coldform_url']; ?>" />
										<div class="mm-item-caption"><?php _e('By default, Coldform displays enabled styles on <em>every</em> page. To prevent this, and to display CSS styles only for the Coldform, enter the URL where it&rsquo;s displayed.', 'coldform'); ?></div></td>
									</tr>
								</table>
							</div>
							<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'coldform'); ?>" />
						</div>
					</div>
					<div id="mm-panel-secondary" class="postbox">
						<h3><?php _e('Shortcodes &amp; Template Tags', 'coldform'); ?></h3>
						<div class="toggle<?php if (!isset($_GET["settings-updated"])) { echo ' default-hidden'; } ?>">
							<h4><?php _e('Shortcode', 'coldform'); ?></h4>
							<p><?php _e('Use this shortcode to display the Coldform on a post or page:', 'coldform'); ?></p>
							<p><code class="mm-code">[coldform]</code></p>
							<h4><?php _e('Template tag', 'coldform'); ?></h4>
							<p><?php _e('Use this template tag to display the Coldform anywhere in your theme template:', 'coldform'); ?></p>
							<p><code class="mm-code">&lt;?php if (function_exists('contact_coldform_public')) contact_coldform_public(); ?&gt;</code></p>
						</div>
					</div>
					<div id="mm-restore-settings" class="postbox">
						<h3><?php _e('Restore Default Options', 'coldform'); ?></h3>
						<div class="toggle<?php if (!isset($_GET["settings-updated"])) { echo ' default-hidden'; } ?>">
							<p>
								<input name="contact_coldform_options[default_options]" type="checkbox" value="1" id="mm_restore_defaults" <?php if (isset($contact_coldform_options['default_options'])) { checked('1', $contact_coldform_options['default_options']); } ?> /> 
								<label class="description" for="contact_coldform_options[default_options]"><?php _e('Restore default options upon plugin deactivation/reactivation.', 'coldform'); ?></label>
							</p>
							<p>
								<small>
									<?php _e('<strong>Tip:</strong> leave this option unchecked to remember your settings. Or, to go ahead and restore all default options, check the box, save your settings, and then deactivate/reactivate the plugin.', 'coldform'); ?>
								</small>
							</p>
							<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'coldform'); ?>" />
						</div>
					</div>
					<div id="mm-panel-current" class="postbox">
						<h3><?php _e('Updates &amp; Info', 'coldform'); ?></h3>
						<div class="toggle">
							<div id="mm-iframe-wrap">
								<iframe src="https://perishablepress.com/current/index-cc.html"></iframe>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="mm-credit-info">
				<a target="_blank" href="<?php echo $contact_coldform_homeurl; ?>" title="<?php echo $contact_coldform_plugin; ?> Homepage"><?php echo $contact_coldform_plugin; ?></a> by 
				<a target="_blank" href="http://twitter.com/perishable" title="Jeff Starr on Twitter">Jeff Starr</a> @ 
				<a target="_blank" href="http://monzilla.biz/" title="Obsessive Web Design &amp; Development">Monzilla Media</a>
			</div>
		</form>
	</div>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			// toggle panels
			jQuery('.default-hidden').hide();
			jQuery('#mm-panel-toggle a').click(function(){
				jQuery('.toggle').slideToggle(300);
				return false;
			});
			jQuery('h3').click(function(){
				jQuery(this).next().slideToggle(300);
			});
			jQuery('#mm-panel-primary-link').click(function(){
				jQuery('.toggle').hide();
				jQuery('#mm-panel-primary .toggle').slideToggle(300);
				return true;
			});
			jQuery('#mm-panel-secondary-link').click(function(){
				jQuery('.toggle').hide();
				jQuery('#mm-panel-secondary .toggle').slideToggle(300);
				return true;
			});
			jQuery('#mm-panel-tertiary-link').click(function(){
				jQuery('.toggle').hide();
				jQuery('#mm-panel-tertiary .toggle').slideToggle(300);
				return true;
			});
			// prevent accidents
			if(!jQuery("#mm_restore_defaults").is(":checked")){
				jQuery('#mm_restore_defaults').click(function(event){
					var r = confirm("<?php _e('Are you sure you want to restore all default options? (this action cannot be undone)', 'coldform'); ?>");
					if (r == true){  
						jQuery("#mm_restore_defaults").attr('checked', true);
					} else {
						jQuery("#mm_restore_defaults").attr('checked', false);
					}
				});
			}
		});
	</script>

<?php }
