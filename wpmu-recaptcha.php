<?php
/*
    Plugin Name: WPMU reCAPTCHA
    Description: Add reCAPTCHA to your login pages. Works for WordPress Multisite. Please Network Activate this plugin. Only 1 api key is needed from Google as long as you do not domain map the wp-admin (backend) of your subsites. Already tested on a MU network of 900+ subsites.
    
    Version: 1.0.3
    Author: Jason Jersey
    Author URI: https://www.twitter.com/degersey
    License: GNU General Public License 3.0 
    License URI: http://www.gnu.org/licenses/gpl-3.0.txt
    
    Copyright 2015 Belkin Capital Ltd (contact: https://belkincapital.com/contact/)
	
    This plugin is opensource; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 3 of the License,
    or (at your option) any later version (if applicable).
	
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
	
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111 USA
*/


/* Checks if is WPMS
 * Since 1.0
 */
function wpmu_ic_init() {
    if ( !is_multisite() )
        exit( 'The WPMU reCAPTCHA plugin is only compatible with WordPress Multisite 3.5+.' );
}

/* Enable this plugin? */
define('WPMU_LOGIN_RECAPTCHA_ENABLED', true);

$wpmu_login_recaptcha_languages = array(
	'ar' => 'Arabic',
	'bg' => 'Bulgarian',
	'ca' => 'Catalan',
	'zh-CN' => 'Chinese (Simplified)',
	'zh-TW' => 'Chinese (Traditional)',
	'hr' => 'Croatian',
	'cs' => 'Czech',
	'da' => 'Danish',
	'nl' => 'Dutch',
	'en-GB' => 'English (UK)',
	'en' => 'English (US)',
	'fil' => 'Filipino',
	'fi' => 'Finnish',
	'fr' => 'French',
	'fr-CA' => 'French (Canadian)',
	'de' => 'German',
	'de-AT' => 'German (Austria)',
	'de-CH' => 'German (Switzerland)',
	'el' => 'Greek',
	'iw' => 'Hebrew',
	'hi' => 'Hindi',
	'hu' => 'Hungarain',
	'id' => 'Indonesian',
	'it' => 'Italian',
	'ja' => 'Japanese',
	'ko' => 'Korean',
	'lv' => 'Latvian',
	'lt' => 'Lithuanian',
	'no' => 'Norwegian',
	'fa' => 'Persian',
	'pl' => 'Polish',
	'pt' => 'Portuguese',
	'pt-BR' => 'Portuguese (Brazil)',
	'pt-PT' => 'Portuguese (Portugal)',
	'ro' => 'Romanian',
	'ru' => 'Russian',
	'sr' => 'Serbian',
	'sk' => 'Slovak',
	'sl' => 'Slovenian',
	'es' => 'Spanish',
	'es-419' => 'Spanish (Latin America)',
	'sv' => 'Swedish',
	'th' => 'Thai',
	'tr' => 'Turkish',
	'uk' => 'Ukrainian',
	'vi' => 'Vietnamese'
);

if ( is_multisite() ) {

    if (!function_exists('wpmu_login_recaptcha_add_pages')) {
	function wpmu_login_recaptcha_add_pages() {
		add_submenu_page('settings.php', 'reCAPTCHA', 'reCAPTCHA', 'manage_network_options', 'xwplr', 'wpmu_login_recaptcha_page');
	}
    }
    
} else {

    if (!function_exists('wpmu_login_recaptcha_add_pages_admin')) {
	function wpmu_login_recaptcha_add_pages_admin() {
		add_submenu_page('tools.php', 'reCAPTCHA', 'reCAPTCHA', 'manage_options', 'xwplr', 'wpmu_login_recaptcha_page');
	}
    }
    
}

// Display reCAPTCHA on login form
if (!function_exists('wpmu_login_recaptcha_form')) {
	function wpmu_login_recaptcha_form() {
		global $recaptcha;
		$login_recaptcha_err = 0;

		if (isset($_GET['login_recaptcha_err'])) {
			$login_recaptcha_err = intval($_GET['login_recaptcha_err']);
		}
		
		if ( is_multisite() ) {
		$blog_id = 1;
		$opt = get_blog_option($blog_id, 'wpmu_login_recaptcha_options');
		} else {
		$opt = get_blog_option('wpmu_login_recaptcha_options');
		}
		
		if (!isset($opt['theme'])) {
			$opt['theme'] = 'light';
		} else {
			if ('light' != $opt['theme'] && 'dark' != $opt['theme']) {
				$opt['theme'] = 'light';
			}
		}

		$x_s = '';
		
		if ('' != trim($opt['site_key']) && '' != trim($opt['secret_key'])) {
		$x_s .= '<div class="x_recaptcha_wrapper"><div class="g-recaptcha" data-sitekey="'.htmlentities(trim($opt['site_key'])).'" data-theme="'.$opt['theme'].'"></div>';
			if (1 == $login_recaptcha_err) {
				$x_s .= '<div class="x_recaptcha_error">Please pass reCAPTCHA verification</div>';
			}
			$x_s .= '</div>';
		}
		echo $x_s;
	}
}

if (!function_exists('wpmu_login_recaptcha_get_ip')) {
	function wpmu_login_recaptcha_get_ip() {
		return $_SERVER['REMOTE_ADDR'];
	}
}

if (!function_exists('wpmu_login_recaptcha_get_post')) {
	function wpmu_login_recaptcha_get_post($var_name) {
		if (isset($_POST[$var_name])) {
			return $_POST[$var_name];
		} else {
			return '';
		}
	}
}

if (!function_exists('wpmu_login_recaptcha_page')) {
	function wpmu_login_recaptcha_page() {
		global $wpmu_login_recaptcha_languages;
		if (!current_user_can('manage_options')) {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		if (isset($_POST['go'])) {
			update_option('wpmu_login_recaptcha_options', $_POST['wpmu_login_recaptcha_options']);
			_e('<div id="message" class="updated fade"><p>Options updated.</p></div>');
		}
		$opt = get_option('wpmu_login_recaptcha_options');
		if (!isset($opt['language']) || '' == $opt['language']) {
			$opt['language'] = 'en';
		}
		echo '<div class="wrap">';
		?>
		<h2>WPMU reCAPTCHA</h2>
		<p><a href="https://www.google.com/recaptcha/admin" target="_blank">Get the reCAPTCHA keys here</a>.</p>
		<p>Both keys must be filled to enable reCAPTCHA in login page.</p>
		<form name="form1" method="post" action="">
		<input type="hidden" name="go" value="1" />
		<table class="form-table">
			<tr valign="top">
				<th scope="row">Site Key (Public)</th>
				<td>
					<input type="text" name="wpmu_login_recaptcha_options[site_key]" size="40" value="<?php echo trim($opt['site_key']); ?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Secret Key (Private)</th>
				<td>
					<input type="text" name="wpmu_login_recaptcha_options[secret_key]" size="40" value="<?php echo trim($opt['secret_key']); ?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">reCAPTCHA theme</th>
				<td>
					<select name="wpmu_login_recaptcha_options[theme]">
					<option value="light">Light</option>
					<option value="dark"<?php if ('dark' == $opt['theme']) : ?> selected="selected"<?php endif; ?>>Dark</option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Language</th>
				<td>
					<select name="wpmu_login_recaptcha_options[language]">
					<?php foreach ($wpmu_login_recaptcha_languages as $language_code => $language) : ?>
					<option value="<?php echo $language_code; ?>" <?php if ($opt['language'] == $language_code) { echo 'selected="selected"'; } ?>><?php echo htmlentities($language); ?></option>
					<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</table>
		<p class="submit"><input type="submit" class="button-primary" title="Save Options" value="Save Options" /></p>
		<?php
		echo '</div>';
	}
}

// reCAPTCHA process
if (!function_exists('wpmu_login_recaptcha_process')) {
	function wpmu_login_recaptcha_process() {
		if (array() == $_POST) {
			return true;
		}

		$opt = get_option('wpmu_login_recaptcha_options');

		if ('' != trim($opt['site_key']) && '' != trim($opt['secret_key'])) {
			$parameters = array(
				'secret' => trim($opt['secret_key']),
				'response' => wpmu_login_recaptcha_get_post('g-recaptcha-response'),
				'remoteip' => wpmu_login_recaptcha_get_ip()
			);
			$url = 'https://www.google.com/recaptcha/api/siteverify?' . http_build_query($parameters);

			$response = wpmu_login_recaptcha_open_url($url);
			$json_response = json_decode($response, true);

			if (isset($json_response['success']) && true !== $json_response['success']) {
				header('Location: wp-login.php?login_recaptcha_err=1');
				exit();
			}
		}
	}
}

// reCAPTCHA open url
if (!function_exists('wpmu_login_recaptcha_open_url')) {
	function wpmu_login_recaptcha_open_url($url) {
		if (function_exists('curl_init') && function_exists('curl_setopt') && function_exists('curl_exec')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$response = curl_exec($ch);
			curl_close($ch);
		} else {
			$response = file_get_contents($url);
		}
		return trim($response);
	}
}

// Add custom links on plugins page
if (!function_exists('wpmu_login_recaptcha_plugin_row_meta')) {
	function wpmu_login_recaptcha_plugin_row_meta($links, $file) {
		if (strpos($file, basename(__FILE__) ) !== false ) {
			$new_links = array();
			$links = array_merge($links, $new_links);
		}

		return $links;
	}
}

if (!function_exists('wpmu_login_recaptcha_uninstall')) {
	function wpmu_login_recaptcha_uninstall() {
		delete_option('wpmu_login_recaptcha_options');
	}
}

if (!function_exists('wpmu_login_recaptcha_login_enqueue_script')) {
function wpmu_login_recaptcha_login_enqueue_script() {
	$opt = get_option('wpmu_login_recaptcha_options');
	if (!isset($opt['language']) || '' == $opt['language']) {
		$opt['language'] = 'en';
	}
	?>
	<script src="https://www.google.com/recaptcha/api.js?hl=<?php echo $opt['language']; ?>" async defer></script>
    <style type="text/css">
        #login {
            width: 350px !important;
        }
		.x_recaptcha_error {
			color:#F00;
			font-weight: 900;
		}
		.x_recaptcha_wrapper {
			padding-bottom: 4%;
		}
    </style>
<?php }
}

$plugin = plugin_basename(__FILE__);

if (WPMU_LOGIN_RECAPTCHA_ENABLED == true) {
	add_action('login_enqueue_scripts', 'wpmu_login_recaptcha_login_enqueue_script');
	add_action('login_form','wpmu_login_recaptcha_form');
	add_action('wp_authenticate', 'wpmu_login_recaptcha_process', 1);
}

add_action('admin_menu', 'wpmu_login_recaptcha_add_pages_admin');
add_action('network_admin_menu', 'wpmu_login_recaptcha_add_pages');

add_filter('plugin_row_meta', 'wpmu_login_recaptcha_plugin_row_meta', 10, 2);

register_uninstall_hook(ABSPATH.PLUGINDIR.'/wpmu-recaptcha/wpmu-recaptcha.php', 'wpmu_login_recaptcha_uninstall');
