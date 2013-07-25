<?php
/*
Plugin Name: CF Popup Plugin
Plugin URI: http://crowdfavorite.com/
Description: This allows customized settings under "CF Popup" for logic on when to show this popup.
Version: 0.2
Author: Crowd Favorite
Author URI: http://crowdfavorite.com/
*/

class CF_Popup {
	static $ver = '0.2';
	static $i = null;
	public static function i() {
		if (self::$i == null) {
			self::$i = new CF_Popup;
		}
		return self::$i;
	}

	public function add_actions() {
		$this->plugin_url = plugins_url(null, __FILE__);

		add_action('wp_head', array($this, 'get_assets'));
		add_action('wp_footer', array($this, 'output_hidden_popup_content'));

		add_action('admin_menu', array($this, 'register_settings_page'));
		add_action('admin_init', array($this, 'settings_init'));
	}
	public function get_assets() {

		wp_register_script(
			'cookie',
			$this->plugin_url . '/js/jquery-cookie.js',
			array('jquery'),
			'1.3.1'
		);
		wp_enqueue_script(
			'colorbox',
			$this->plugin_url . '/js/colorbox/jquery.colorbox-min.js',
			array('jquery'),
			'1.4.24'
		);
		wp_enqueue_script(
			'cf-popup',
			$this->plugin_url . '/js/cf-popup.js',
			array('jquery', 'cookie', 'colorbox'),
			self::$ver
		);
		wp_localize_script(
			'cf-popup',
			'_cfPopup',
			array(
				'showWhen' => $this->get_setting('show_when'),
				'interval' => $this->get_setting('interval'),
				'cookieName' => $this->get_setting('cookie_name'),
				'debug' => $this->get_setting('debug'),
				'pages' => $this->get_setting('pages'),
				'categories' => $this->get_setting('categories'),
				'postTypes' => $this->get_setting('post_types'),
				'popupOnLinkClick' => $this->get_setting('on_link_click'),
				'domain' => home_url(),
			)
		);

		wp_enqueue_style(
			'colorbox',
			$this->plugin_url . '/css/colorbox.css',
			array(),
			self::$ver
		);
	}
	public function get_settings() {
		return (array) get_option('cf_popup_settings');
	}
	public function get_setting($key) {
		$settings = $this->get_settings();
		$val = '';
		if (!empty($settings[$key])) {
			$val = $settings[$key];
		}
		return $val;
	}

	public function register_settings_page() {
		//create new top-level menu
		add_submenu_page(
			'options-general.php',
			__('CF Popup Settings', 'cf_popup'),
			__('CF Popup', 'cf_popup'),
			'manage_options',
			'cfpopup_settings_page',
			array($this, 'output_settings_page')
		);
	}
	public function output_settings_page() {
		?>
		<div class="wrap">
			<?php screen_icon('options-general'); ?>
			<h2><?php _e('CF Popup Settings', 'cf_popup'); ?></h2>

			<form method="post" action="options.php">
				<?php settings_fields('cfpopup_settings_page'); ?>
				<?php do_settings_sections('cfpopup_settings_page'); ?>
				<?php submit_button(); ?>
			</form>

		</div><!-- /wrap -->
		<?php
	}
	public function settings_init() {
		$settings_page = 'cfpopup_settings_page';
		$settings_section = 'cf_popup_settings';
		add_settings_section(
			$settings_section,
			__('Newsletter Popup Settings', 'cf_popup'),
			array($this, 'settings_section_intro'),
			$settings_page
		);

		add_settings_field(
			'newsletter_popup_when_settings', // actual option name
			__('Show Popup When?', 'cf_popup'),
			array($this, 'show_popup_when_field'),
			$settings_page,
			$settings_section
		);
		add_settings_field(
			'newsletter_popup_content_settings', // actual option name
			__('Popup Content', 'cf_popup'),
			array($this, 'show_popup_content_field'),
			$settings_page,
			$settings_section
		);
		add_settings_field(
			'newsletter_popup_interval_settings', // actual option name
			__('Popup Interval', 'cf_popup'),
			array($this, 'show_popup_interval_field'),
			$settings_page,
			$settings_section
		);
		add_settings_field(
			'newsletter_popup_page_settings', // actual option name
			__('Popup Page(s)', 'cf_popup'),
			array($this, 'show_popup_page_field'),
			$settings_page,
			$settings_section
		);
		add_settings_field(
			'newsletter_popup_categories_settings', // actual option name
			__('Post Categories', 'cf_popup'),
			array($this, 'show_popup_categories_field'),
			$settings_page,
			$settings_section
		);
		add_settings_field(
			'newsletter_popup_post_types_settings', // actual option name
			__('Post Type(s)', 'cf_popup'),
			array($this, 'show_popup_post_types_field'),
			$settings_page,
			$settings_section
		);
		add_settings_field(
			'newsletter_popup_cookie_settings', // actual option name
			__('Cookie Name', 'cf_popup'),
			array($this, 'show_popup_cookie_field'),
			$settings_page,
			$settings_section
		);
		add_settings_field(
			'newsletter_popup_on_link_click_settings', // actual option name
			__('Show on Link Click', 'cf_popup'),
			array($this, 'show_popup_onlinkclick_field'),
			$settings_page,
			$settings_section
		);
		add_settings_field(
			'newsletter_popup_debug_settings', // actual option name
			__('Debug', 'cf_popup'),
			array($this, 'show_popup_debug_field'),
			$settings_page,
			$settings_section
		);
		register_setting(
			$settings_page,
			'cf_popup_settings',
			array($this, 'sanitize_settings')
		);
	}
	public function settings_section_intro() {
		?>
		<p>Settings for the Popup Box.</p>
		<?php
	}
	public function show_popup_when_field() {
		$options = array(
			'never' => 'Never (i.e., Turn Off)',
			'enter' => 'On Enter',
			'exit' => 'On Exit',
		);
		$settings = $this->get_settings();
		$show_when = 'never';
		if (!empty($settings['show_when']) && in_array($settings['show_when'], array_keys($options))) {
			$show_when = $settings['show_when'];
		}
		?>
		<select name="cf_popup_settings[show_when]">
			<?php
			foreach ($options as $val => $friendly) {
				?>
				<option value="<?php echo esc_attr($val); ?>"<?php selected($show_when, $val); ?>><?php echo esc_html($friendly); ?></option>
				<?php
			}
			?>
		</select>
		<?php
	}
	public function show_popup_content_field() {
		$settings = $this->get_settings();
		$content = '';
		if (!empty($settings['content'])) {
			$content = $settings['content'];
		}
		?>
		<textarea name="cf_popup_settings[content]" style="width: 500px; height: 200px;"><?php echo esc_textarea($content); ?></textarea>
		<?php
	}
	public function show_popup_interval_field() {
		$settings = $this->get_settings();
		$interval = 1;
		if (!empty($settings['interval'])) {
			$interval = $settings['interval'];
		}
		?>
		<input name="cf_popup_settings[interval]" value="<?php echo esc_attr($interval); ?>"> <?php echo _n('Day', 'Days', $interval, 'cf_popup'); ?>
		<p class="help">Number of days between someone seeing the popup again.</p>
		<?php
	}
	public function show_popup_page_field() {
		$pages = $this->get_setting('pages');
		?>
		<input name="cf_popup_settings[pages]" value="<?php echo esc_attr($pages); ?>" type="text" placeholder="all" />
		<p class="help">Page IDs separated by comma.  Leave empty for all pages.</p>
		<?php
	}
	public function show_popup_categories_field() {
		$categories = $this->get_setting('categories');
		?>
		<input name="cf_popup_settings[categories]" value="<?php echo esc_attr($categories); ?>" type="text" placeholder="any" />
		<p class="help">Category slugs separated by comma.  Leave empty for any category.  (e.g., uncategorized, video, etc.)</p>
		<?php
	}
	public function show_popup_post_types_field() {
		$post_types = $this->get_setting('post_types');
		?>
		<input name="cf_popup_settings[post_types]" value="<?php echo esc_attr($post_types); ?>" type="text" placeholder="any" />
		<p class="help">Post Types separated by comma.  Leave empty for any post type.  (e.g., post, page, etc.)</p>
		<?php
	}
	public function show_popup_cookie_field() {
		$settings = $this->get_settings();
		$cookie_name = 'cf_popup';
		if (!empty($settings['cookie_name'])) {
			$cookie_name = $settings['cookie_name'];
		}
		?>
		<input name="cf_popup_settings[cookie_name]" value="<?php echo esc_attr($cookie_name); ?>">
		<?php
	}
	public function show_popup_onlinkclick_field() {
		$$on_link_click = $this->get_setting('on_link_click');
		?>
		<input type="checkbox" name="cf_popup_settings[on_link_click]" id="cf_popup_settings_onlinkclick" value="1"<?php checked(1, $$on_link_click); ?> /> <label for="cf_popup_settings_onlinkclick">Display on any <?php echo esc_html(home_url()); ?> link click?</label>
		<p class="help">Displays the popup when a visitor clicks a link within the <?php echo esc_html(home_url()); ?> domain.</p>
		<?php
	}
	public function show_popup_debug_field() {
		$debug = $this->get_setting('debug');
		?>
		<input type="checkbox" name="cf_popup_settings[debug]" id="cf_popup_settings_debug" value="1"<?php checked(1, $debug); ?> /> <label for="cf_popup_settings_debug">Debug?</label>
		<p class="help">Ignore the cookie entirely, and show popup at each interaction</p>
		<?php
	}
	public function sanitize_settings($settings) {
		// Pages
		$pages = $settings['pages'];
		if (!empty($pages)) {
			$_pages = explode(',', $pages);
			$_pages = array_filter(array_map('intval', $_pages));
			$pages = implode(',', $_pages);
			$settings['pages'] = $pages;
		}
		return $settings;
	}
	public function output_hidden_popup_content() {
		$cats = get_the_category();
		$categories = array();
		foreach ($cats as $cat) {
			$categories[] = $cat->slug;
		}
		?>
		<div
			id="js-cfpopup-content"
			style="display:none"
			data-post-type="<?php echo esc_attr(get_post_type()); ?>"
			data-categories="<?php echo esc_attr(implode(' ', $categories)); ?>"
			data-bodyClasses="<?php echo esc_attr(implode(' ', get_body_class())); ?>"
			>
			<?php echo $this->get_setting('content'); ?>
		</div>
		<?php
	}
}
CF_Popup::i()->add_actions();