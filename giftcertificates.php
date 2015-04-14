<?php
/**
 * Plugin Name: Gift Certificate Creator
 * Plugin URI: http://bobcares.com
 * Description: A plugin to allow users to add quotes for gift certificate creation.
 * Version: 1.0.0
 * Author: Bobcares <pm@bobcares.com>
 * Author URI: http://bobcares.com
 * License: 
 */

$path = dirname( dirname( dirname( dirname( __FILE__ ) ) ) );

//include require files
$dir = plugin_dir_path( __FILE__ );
require_once($dir.'gcm-config.php');
require_once($dir.'classes/gcm.class.php');

if (!function_exists('writeLog')) {

	/**
	 * Function to add the plugin log to wordpress log file, added by BDT
	 * @param object $log
	 */
	function writeLog($log, $line = "",$file = "")  {

		if (WP_DEBUG === true) {

			$pluginLog = $log ." on line [" . $line . "] of [" . $file . "]\n";

			if ( is_array( $pluginLog ) || is_object( $pluginLog ) ) {
				print_r( $pluginLog, true );
			} else {
				error_log( $pluginLog );
			}

		}
	}

}


/**
 * function to install the plugin database
 */
function gcManagerInstall() {
    global $wpdb;    
    $table = $wpdb->prefix."gift_certificates";
    $structure = "CREATE TABLE IF NOT EXISTS $table (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `cert_amount` float NOT NULL,
      `user_name` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
      `receip_name` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
      `cc_number` bigint(20) NOT NULL,
      `cc_exp` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
      `cc_sec_code` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
      `receip_address` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;";
    $wpdb->query($structure);
}

// add hook for activate plugin action 

//register activation hook for creating table on plugin activation.
register_activation_hook(__FILE__,'gcManagerInstall');

/*
 * Fuction to register our Array of settings
 * Author: Bobcares Dev Team
 */
function gcRegisterSettings() {
	register_setting('gc-settings-group', 'gc_options');
}

// Action hook to register our option settings
add_action('admin_init', 'gcRegisterSettings');

/**
 * gc menu displayed in admin section
 * Modified: Bobcares Dev Team
*/
function gcMenu() {
	$menuTitle = "Gift Certificates";
	add_menu_page($menuTitle, $menuTitle, 'edit_others_pages', 'gc_settings', 'gcSettingsPage', "dashicons-awards", 6);
	add_submenu_page('gc_settings', 'Settings', 'Settings', 'edit_others_pages', 'gc_settings', 'gcSettingsPage');
	add_submenu_page('gc_settings', 'Gift Certificate Details', 'Gift Certificate Details', 'edit_others_pages', 'gc_list_page', 'gcListManager');
}

// add hook for admin menu
add_action('admin_menu', 'gcMenu');

/**
 * action when click on the admin menu
 */
function gcListManager() {
    include_once('gc-list.php');
}

/**
 * Fuction to setup the settings page on the admin menu
 * Author: Bobcares Dev Team
 * @return string The admin settings page content
 */
function gcSettingsPage() {

	$gcOptionsArray = get_option('gc_options');
	echo '<div class="wrap">';
	echo '<h2>Settings</h2>';
	?>
    <form method="post" action="options.php">

        <h3>Email Options</h3>
        <?php settings_fields('gc-settings-group'); ?>
       
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('From E-mail'); ?></th>
                <td> <input type="text" name="gc_options[admin_email]" value="<?php echo $gcOptionsArray['admin_email']; ?>"/><br />
                    <span class="description">From email address</span>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes'); ?>" />
    </form>
    <?php
    echo '</div>';
}


/**
 * function to display shortcode for Gc form
 * @param Array $atts     Contains arguments to short code
 * @return string         The return content
 */
function gcFormShortCode($atts) {
    $success = 0;
    
    // check sections and include corresponding file
    if ($_REQUEST['action'] == 'Submit') {
    
        // chekc input values
        //if (!empty($_REQUEST['cert_amount']) && !empty($_REQUEST['cc_number']) && !empty($_REQUEST['cc_sec_code'])) {
   		if (!empty($_REQUEST['cert_amount']) && !empty($_REQUEST['cc_sec_code'])) {
            $gcmObj = New GCM();
            $gcmObj->createNewGCM($_REQUEST);
            $success = 1;
            writeLog(" amount ".$_REQUEST['cert_amount']." and email ".$_REQUEST['cc_sec_code']." are posted successfully", basename(__LINE__), basename(__FILE__));
            
            // send mail to admin
            $gcmObj->sendGCMReportEmail(GC_MAIL_TO, GC_MAIL_FROM, GC_MAIL_SUBJECT, $_REQUEST);
            $_REQUEST = array();
    
        } else {
        	writeLog(" Form values are not posted ", basename(__LINE__), basename(__FILE__));
            $success = -1;
        }
        
    } else {
    	writeLog(" Form not submitted ", basename(__LINE__), basename(__FILE__));
    }
    
    $formLink = get_site_url() . "/wp-admin/admin.php?page=gc_list_page";
        
    // if errorsocuured display errors
    if ($success == -1) {
    	writeLog(" Form values are not posted ", basename(__LINE__), basename(__FILE__));
        ?>
    	<div class="error">Please enter form details in proper format!</div>
    	<?php
	}
    
    // if messages are ready to show
    if ($success == 1) {
		?>
        <div class="updated">Request submitted successfully!</div>
        <?php
    }   
    ?>
    <style>
        .gc_form{margin-top: 20px;}
        .gc_form, .gc_form th, .gc_form td{border: 0px;padding: 6px 10px;font-size: 16px;}
        .gc_form th{text-align: right; color: #3A87AD;}
        div.error, div.updated {
            margin: 16px 0 15px;
            font-size: 13px;
            padding: 8px 12px;
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);
        }
        div.updated {
            color: #7AD03A;
            border-left: 4px solid #7AD03A;
        }
        div.error {
            border-left: 4px solid #DD3D36;
            color: #DD3D36;
        }
        
        input, select{height: auto; padding: 2px 6px; color: gray;}
        .gc_form select{width: 70px; height: 40px;}
        input[type=submit] {
            background-color: #5BB75B;
            background-image: -moz-linear-gradient(center top , #62C462, #51A351);
            background-repeat: repeat-x;
            border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
            color: #FFFFFF !important;
            text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
            height: 40px;
        }
    </style>
    <form method="post" name="gc_form" action="">
        <table class='gc_form'>
            <tr>
                <th>Certificate Amount:</th>
                <td><input type="text" name="cert_amount" value="<?php echo $_REQUEST['cert_amount']; ?>" placeholder ="$"></td>
            </tr>
            <tr>
                <th>Your Name:</th>
                <td><input type="text" name="user_name" value="<?php echo $_REQUEST['user_name']; ?>"> (optional)</td>
            </tr>
            <tr>
                <th>Recipient Name:</th>
                <td><input type="text" name="receip_name" value="<?php echo $_REQUEST['receip_name']; ?>"> (optional)</td>
            </tr>
            <tr>
                <th>Recipient Email:</th>
                <td><input type="text" name="cc_sec_code" value="<?php echo $_REQUEST['cc_sec_code']; ?>"></td>
            </tr>
            <tr>
                <th>Recipient Address:</th>
                <td><textarea name="receip_address" value="<?php echo $_REQUEST['receip_address']; ?>"></textarea>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;"><input type="submit" value="Submit" name="action"></td>
            </tr>
        </table>
    </form>
    <?php
}

// hook call to create short code
add_shortcode( 'gift_certificate_form', 'gcFormShortCode');
?>
