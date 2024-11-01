<?php
/*
Plugin Name: Slidorion
Plugin URI: http://wordpress.org/extend/plugins/slidorion/
Description: Slidorion comes to WordPress to enhance websites and provide a platform to combine images and text in a slider and accordion fashion!
Version: 1.0.2
Author: Ben Holland
Author URI: http://benholland.me
License: GLP2
*/

/*  Copyright 2012  Ben Holland  (email : benholland99@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

global $wpdb;

/* plugin database version - used to identify the need to "upgrade" database */
define('SLIDORION_DB_VERSION', 1);

/* plugin database version - used to identify the need to "upgrade" database */
define('SLIDORION_DB_TABLE', $wpdb->prefix."slidorion");

/* supported effects */
define('SLIDORION_EFFECTS', 'fade random slideLeft slideUp slideRight slideDown overLeft overRight overUp overDown wipeDown wipeUp wipeDownFade wipeUpFade slideEffects overEffects wipeEffects wipeFadeEffects wipeAllEffects');

/* supported easing */
define('EASING_EFFECTS', 'swing easeInQuad easeOutQuad easeInOutQuad easeInCubic easeOutCubic easeInOutCubic easeInQuart easeOutQuart easeInOutQuart easeInQuint easeOutQuint easeInOutQuint easeInSine easeOutSine easeInOutSine easeInExpo easeOutExpo easeInOutExpo easeInCirc easeOutCirc easeInOutCirc easeInElastic easeOutElastic easeInOutElastic easeInBack easeOutBack easeInOutBack easeInBounce easeOutBounce easeInOutBounce');

/*
 * define default values for all plugin options
 * the function can return the entire array or the value of a single option if opt_name is specified
 */
function slidorion_defaults($opt_name = null) {

	$defaults = Array(
		'slidorion_autoPlay' => 1,
		'slidorion_easing' => '',
		'slidorion_effect' => 'fade',
		'slidorion_first' => 1,
		'slidorion_interval' => 5000,
		'slidorion_hoverPause' => 0,
		'slidorion_speed' => 1000
	);

	/* return entire array if $opt_name is empty */
	if (empty($opt_name))
		return $defaults;

	/* otherwise return the value of the specified option from the array */
	return $defaults[$opt_name];
	
};


/*
 * plugin activation hook
 */
function slidorion_activate() {

	/* add/update all plugin options */
	update_option('SLIDORION_DB_VERSION', constant('SLIDORION_DB_VERSION'));
	foreach (slidorion_defaults() as $opt_name => $default_value) {
		add_option($opt_name, $default_value);
	}

}


/*
 * plugin deactivation hook
 */
function slidorion_deactivate() {

	/* remove deprecated options from the database on deactivation */ 
	delete_option('slidorion_slidorion_testtest');

}


/*
 * plugin installation hook
 */
function slidorion_install() {
	$sql = "CREATE TABLE ".SLIDORION_DB_TABLE." (
	  id int(11) NOT NULL AUTO_INCREMENT,
	  title varchar(255) NOT NULL,
	  shortcode varchar(255) NOT NULL,
	  details text NOT NULL,
	  UNIQUE KEY id (id)
	);";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}


/*
 * plugin uninstallation hook
 */
function slidorion_uninstall() {
	global $wpdb;

	/* delete all plugin options */
	delete_option('SLIDORION_DB_VERSION');
	foreach (slidorion_defaults() as $opt_name => $default_value) {
		delete_option($opt_name);
	}

	$sql = "DROP TABLE ".SLIDORION_DB_TABLE;
	$wpdb->query($sql);
}


/*
 * display an administrative notice when database is updated
 */
function slidorion_update_notice() {
	?>
		<div class="updated fade"><p>
			<strong>Slidorion:</strong> database was updated.
		</p></div>
	<?php
}


/*
 * handle plugin updates
 */
function slidorion_update_helper() {

	/*
	 * if current database 'SLIDORION_DB_VERSION' is lower that the plugin database version,
	 * update the plugin database by calling the deactivate() and activate() functions
	 */
	if (intval(get_option('SLIDORION_DB_VERSION')) < constant('SLIDORION_DB_VERSION')) {
		/* deactivate, activate will handle addition of new options to the database
		 * and update the db_version too */
		slidorion_deactivate();
		slidorion_activate();

		/* notify administrator of the update */
		add_action('admin_notices', 'slidorion_update_notice');
	}

}


/*
 * plugin admin_init action function
 */
function slidorion_admin_init() {

	register_setting('slidorion-settings', 'slidorion_autoPlay', 'intval');
	register_setting('slidorion-settings', 'slidorion_easing');
	register_setting('slidorion-settings', 'slidorion_effect');
	register_setting('slidorion-settings', 'slidorion_first', 'intval');
	register_setting('slidorion-settings', 'slidorion_interval', 'intval');
	register_setting('slidorion-settings', 'slidorion_hoverPause', 'intval');
	register_setting('slidorion-settings', 'slidorion_speed', 'intval');

	slidorion_update_helper();

}


/*
 * this is the function that actually provides the slider (called from theme)
 */
function slidorion_slider($name) {
	global $wpdb;

	$sql = 'SELECT * FROM '.SLIDORION_DB_TABLE.' WHERE shortcode="'.$name.'"';
	$slidorion = $wpdb->get_results($sql);
	$options = unserialize(base64_decode($slidorion[0]->details)); ?>

	<script type="text/javascript">
		jQuery(document).ready(function() {
	        jQuery('#slidorion_player').slidorion({
				autoPlay: <?php echo $options['slidorion_autoPlay']; ?>,
				easing: '<?php echo $options['slidorion_easing']; ?>',
				effect: '<?php echo $options['slidorion_effect']; ?>',
				first: <?php echo $options['slidorion_first']; ?>,
				interval: <?php echo $options['slidorion_interval']; ?>,
				hoverPause: <?php echo $options['slidorion_hoverPause']; ?>,
				speed: <?php echo $options['slidorion_speed']; ?>
			});
		});
	</script>
	<div id="slidorion_player">
		<div id="slider">
			<?php foreach($options['slidorion_slides'] as $img) { ?>
			<div class="slide">
				<img src="<?php echo $img; ?>" />
			</div>
			<?php } ?>
		</div>
		<div id="accordion">
			<?php foreach($options['slidorion_titles'] as $i => $title) { ?>
			<div class="link-header"><?php echo stripslashes($title); ?></div>
			<div class="link-content">
				<?php echo stripslashes($options['slidorion_accords'][$i]); ?>
			</div>
			<?php } ?>
		</div>
	</div>
	<?php
} 


/*
 * handle slidorion shortcode tags
 */
function slidorion_shortcode($atts) {
	extract(shortcode_atts(array(
		'name' => ''
	), $atts));

	# place call to main plugin function using specified options
	slidorion_slider($name);
}



/*
 * plugin admin_menu action function
 */
function slidorion_admin_menu() {
	if (function_exists('add_submenu_page')) {
		add_menu_page('View All Slidorion','Slidorion', 'manage_options', 'slidorion_menu', 'slidorion_admin_options');
		add_submenu_page('slidorion_menu', 'Add New', 'New Slidorion', 'manage_options', 'slidorion_menu_new', 'slidorion_admin_new');
	}
}


/*
 * plugin administrative new slidorion page
 */
function slidorion_admin_options() {
	global $wpdb;
	$sql = 'SELECT * FROM '.SLIDORION_DB_TABLE;
	$slidorions = $wpdb->get_results($sql);
	?>
	<div class="wrap" style="width:700px;">
		<div id="icon-options-general" class="icon32"><br></div>
		<h2>Slidorion Menu</h2>

		<table class="widefat">
			<thead>
				<th>ID</th>
				<th>Title</th>
				<th>Shortcode</th>
				<th>Edit</th>
				<th>Remove</th>
			</thead>
			<tbody>
				<?php foreach($slidorions as $slidorion) { ?>
				<tr>
					<td><?php echo $slidorion->id; ?></td>
					<td><?php echo stripslashes($slidorion->title); ?></td>
					<td><?php echo stripslashes($slidorion->shortcode); ?></td>
					<td>
						<a href="?page=slidorion_menu_new&edit=<?php echo $slidorion->id; ?>" class="button">Edit</a>
					</td>
					<td>
						<a href="?page=slidorion_menu_new&remove=<?php echo $slidorion->id; ?>" class="button">Remove</a>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<td colspan="4">
						Add a new Slidorion
						<a href="?page=slidorion_menu_new" class="button">Add</a>
					</td>
				</tr>
			</tbody>
		</table>

		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="text-align:center; width:100px;float:right;">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="GDZZRP7KWFDRN">
			<input type="image" src="https://www.paypalobjects.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal — The safer, easier way to pay online.">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1" />
		</form>
		<p style="float:right;">Like this plugin? Fancy buying me a beer -</p> 
	</div>
	<?php
}


/*
 * plugin administrative options page
 */
function slidorion_admin_new() {

	global $wpdb;
	$msg = '';

	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	if(isset($_POST['slidorion_effect'])) {


		if(isset($_POST['edit_slidorion']) && $_POST['edit_slidorion']) {

			$id = $_POST['edit_slidorion'];
			unset($_POST['edit_slidorion']);
			$title = $_POST['slidorion_title'];
			$shortcode = strtolower(str_replace(" ", "_", $title));
			unset($_POST['slidorion_title']);
			$input = base64_encode(serialize($_POST));

			$sql = 'UPDATE '.SLIDORION_DB_TABLE.' SET title="'.$title.'", shortcode="'.$shortcode.'", details="'.$input.'" WHERE id="'.$id.'"';

			$updated = $wpdb->query($sql);

			if($updated) {
				$msg = "Slidorion has been updated - <a href=\"?page=slidorion_menu\">back</a>";
			} else {
				$msg = "Something went wrong. Please try again";
			}
		} else {
			$title = $_POST['slidorion_title'];
			$shortcode = strtolower(str_replace(" ", "_", $title));
			unset($_POST['slidorion_title']);
			$input = base64_encode(serialize($_POST));

			if($wpdb->insert(SLIDORION_DB_TABLE, array('title'=>$title, 'shortcode'=>$shortcode, 'details'=>$input) )) {
				$msg = "Slidorion has been added - <a href=\"?page=slidorion_menu\">back</a>";
			} else {
				$msg = "Something went wrong. Please try again";
			}
		}
	}

	if(isset($_GET['edit'])) {
		$sql = 'SELECT * FROM '.SLIDORION_DB_TABLE.' WHERE id="'.$_GET['edit'].'"';
		$slidorion = $wpdb->get_results($sql);
		$options = unserialize(base64_decode($slidorion[0]->details));
	}

	if(isset($_GET['remove'])) {
		$sql = 'DELETE FROM '.SLIDORION_DB_TABLE.' WHERE id="'.$_GET['remove'].'"';
		if($wpdb->query($sql)) {
			die("Slidorion has been removed - <a href=\"?page=slidorion_menu\">back</a>");
		} else {
			$msg = "Something went wrong. Please try again";
		}
	}

	?>
	<script>var slidorion_imageholder_num = 1;</script>
	<div class="wrap" style="width:1000px;">
		<h2>Slidorion configuration</h2>
		<p class="message"><?php echo ($msg!='' ? $msg : ''); ?></p>
		<form method="post" action="" id="slidorion_config">
			<?php settings_fields('slidorion-settings'); ?>

			<div style="width:420px;float:left">
				<p>
					<table class="form-table">
						<tr>
							<th scope="row"><strong>Title</strong> - <em>the Slidorion's name</em></th>
							<td>
								<input type="text" id="slidorion_title" name="slidorion_title" value="<?php echo (isset($slidorion[0]->title) ? stripslashes($slidorion[0]->title) : ''); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><strong>Transition</strong> - <em>what tranisition do you want to use</em></th>
							<td>
								<select name="slidorion_effect">
								<?php
								$all_effects = explode(' ', constant('SLIDORION_EFFECTS'));
								foreach ($all_effects as $effect) {
									?>
									<option value="<?php echo $effect ?>" <?php echo (isset($options) && $options['slidorion_effect'] == $effect ? 'selected' : ''); ?>><?php echo $effect ?></option>
									<?php
								}
								?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><strong>Easing</strong> - <em>apply easing to your animations</em></th>
							<td>
								<select name="slidorion_easing">
								<?php
								if(!isset($options)) { echo "<option></option>"; }

								$easing_effects = explode(' ', constant('EASING_EFFECTS'));
								foreach ($easing_effects as $easing) {
									?>
									<option value="<?php echo $easing ?>" <?php echo (isset($options) && $options['slidorion_easing'] == $easing ? 'selected' : ''); ?>><?php echo $easing ?></option>
									<?php
								}
								?>
								</select>
							</td>
						</tr>

						<tr>
							<th scope="row"><strong>Autoplay</strong> - <em>plays on it's own</em></th>
							<td>
								<select name="slidorion_autoPlay">
									<option value="0" <?php echo (isset($options) && $options['slidorion_autoPlay'] == 0 ? 'selected' : ''); ?>>No</option>
									<option value="1" <?php echo (isset($options) && $options['slidorion_autoPlay'] == 1 ? 'selected' : ''); ?>>Yes</option>
								</select>
							</td>
						</tr>

						<tr>
							<th scope="row"><strong>Interval</strong> - <em>time between slides</em></th>
							<td>
								<input type="text" name="slidorion_interval" size="3" value="<?php echo (isset($options['slidorion_interval']) ? $options['slidorion_interval'] : get_option('slidorion_interval')); ?>">
							</td>
						</tr>

						<tr>
							<th scope="row"><strong>Speed</strong> - <em>the speed of the transitions</em></th>
							<td>
								<input type="text" name="slidorion_speed" size="3" value="<?php echo (isset($options['slidorion_speed']) ? $options['slidorion_speed'] : get_option('slidorion_speed')); ?>">
							</td>
						</tr>

						<tr>
							<th scope="row"><strong>First Slide</strong> - <em>which slide to start on</em></th>
							<td>
								<input type="text" name="slidorion_first" size="3" value="<?php echo (isset($options['slidorion_first']) ? $options['slidorion_first'] : get_option('slidorion_first')); ?>">
							</td>
						</tr>

						<tr>
							<th scope="row"><strong>Pause on Hover</strong> - <em>stops the Slidorion when the mouse is over it</em></th>
							<td>
								<select name="slidorion_hoverPause">
									<option value="0" <?php echo (isset($options['slidorion_hoverPause']) && $options['slidorion_hoverPause'] == 0 ? 'selected' : ''); ?>>No</option>
									<option value="1" <?php echo (isset($options['slidorion_hoverPause']) && $options['slidorion_hoverPause'] == 1 ? 'selected' : ''); ?>>Yes</option>
								</select>
							</td>
						</tr>
					</table>
				</p>
	 
				<p class="submit">
					<input type="hidden" id="edit_slidorion" name="edit_slidorion" value="<?php echo (isset($slidorion[0]->id) ? $slidorion[0]->id : ''); ?>" />
					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>

			</div>

			<div style="width:500px;float:left">
				<?php if(isset($options['slidorion_slides'])) { ?>

					<?php foreach($options['slidorion_slides'] as $i => $img) { $i = str_replace("'","",$i); ?>
					<div class="image-holder">
						<h3>Slide <?php _e(str_replace("slide","",$i)); ?></h3>
						<div>
							<a href="media-upload.php?type=image&amp;TB_iframe=true" class="thickbox" onclick="current_slidorion_slide='<?php _e($i); ?>';">
								<img id="img_preview_slide<?php _e(str_replace("slide","",$i)); ?>" class="img-preview" src="<?php _e($img); ?>" />
							</a>
							<input type="hidden" id="<?php _e($i); ?>" name="slidorion_slides[<?php _e($i); ?>]" value="<?php _e($img); ?>">
							<input type="text" name="slidorion_titles[<?php _e($i); ?>]" value="<?php echo stripslashes($options['slidorion_titles'][$i]); ?>" />
							<textarea name="slidorion_accords[<?php _e($i); ?>]" id="accordion_text_<?php _e($i); ?>"><?php echo stripslashes($options['slidorion_accords'][$i]); ?></textarea>
							<a href="#" class="clear">Clear</a>
							<a href="#" class="remove">Remove</a>
						</div>
					</div>
					<script>
					slidorion_imageholder_num = <?php _e(str_replace("slide","",$i)); ?>;
					</script>
					<?php }

				} else { ?>
					<div class="image-holder">
						<h3>Slide 1</h3>
						<div>
							<a href="media-upload.php?type=image&amp;TB_iframe=true" class="thickbox" onclick="current_slidorion_slide='slide1';">
								<img id="img_preview_slide1" class="img-preview" src="<?php _e(WP_CONTENT_URL); ?>/plugins/slidorion/images/upload_placeholder.jpg" />
							</a>
							<input type="hidden" id="slide1" name="slidorion_slides[slide1]" />
							<input type="text" name="slidorion_titles[slide1]" />
							<textarea name="slidorion_accords[slide1]" id="accordion_text_slide1"></textarea>
							<a href="#" class="clear">Clear</a>
							<a href="#" class="remove">Remove</a>
						</div>
					</div>
				<?php } ?>

				<div class="add-image-holder">
					<h3>Add new slide</h3>
				</div>

			</div>
 
		</form>
	</div>

	<script>
	var slidorion_image_placeholder = '<?php _e(WP_CONTENT_URL); ?>/plugins/slidorion/images/upload_placeholder.jpg';
	</script>
	<?php
}

function slidorion_register_plugin_scripts() {
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-effects-core');
	wp_enqueue_script('slidorion', plugins_url('/slidorion/slidorion.min.js', __FILE__), array('jquery','jquery-effects-core'));
}

function slidorion_register_plugin_styles() {
	wp_enqueue_style('slidorion_css', plugins_url('/slidorion/slidorion.css', __FILE__), false);
}

function slidorion_register_admin_scripts() {
	wp_enqueue_script('jquery');
	wp_enqueue_script('thickbox');
	wp_enqueue_script('media-upload');
	wp_enqueue_script('slidorion_uploader', plugins_url('/slidorion/slidorion_uploader.js', __FILE__), array('jquery','media-upload', 'thickbox'));
}

function slidorion_register_admin_styles() {
	wp_enqueue_style('thickbox');
	wp_enqueue_style('slidorion_admin_css', plugins_url('/slidorion/slidorion_admin.css', __FILE__), false);
}

/* install/uninstall */
register_activation_hook(__FILE__,'slidorion_install');
register_activation_hook( __FILE__, 'slidorion_activate' );
register_deactivation_hook( __FILE__, 'slidorion_deactivate' );
register_uninstall_hook( __FILE__, 'slidorion_uninstall' );

/* actions */
add_action('admin_init', 'slidorion_admin_init' );
add_action('admin_menu', 'slidorion_admin_menu');

/* shortcodes */
add_shortcode('slidorion', 'slidorion_shortcode');

// Load page scripts and styles
add_action('wp_print_scripts', 'slidorion_register_plugin_scripts');
add_action('wp_print_styles', 'slidorion_register_plugin_styles');

// Load admin scripts and styles
add_action('admin_print_scripts', 'slidorion_register_admin_styles');
add_action('admin_print_styles', 'slidorion_register_admin_scripts');


?>