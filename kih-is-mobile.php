<?php
/* ======================================================================================================
	Plugin Name: is_mobile
	Plugin URI: http://www.codestuff.com/projects/kih-is-mobile
	Description: Provides an is_mobile() function for detecting site visitors using a mobile phone.
	Version: 2.0.2
	Author: Gerry Ilagan
	Author URI: http://gerry.ws

=========================================================================================================
1.0.0 - 2008-06-11 - Initial version
1.0.1 - 2008-06-27 - Added 'MOT-RAZR' to default mobile type strings to detect Motorola Razors
1.0.2 - 2008-07-05 - Added validation and length limit on HTTP_USER_AGENT
2.0.0 - 2009-03-16 - Converted to kih project
2.0.2 - 2009-05-26 - Updated some info
=========================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to,
the implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no
event shall the copyright owner or contributors be liable for any direct, indirect, incidental, special,
exemplary, or consequential damages (including, but not limited to, procurement of substitute goods or
services; loss of use, data, or profits; or business interruption) however caused and on any theory of
liability, whether in contract, strict liability, or tort (including negligence or otherwise) arising
in any way out of the use of this software, even if advised of the possibility of such damage.

For full license details see license.txt
====================================================================================================== */

function is_mobile() {

    return kih_is_mobile::check($_SERVER["HTTP_USER_AGENT"]);
}

class kih_is_mobile {

	/**
	 * This will put a link to the plugin settings page in the plugin list
	 *
	 * @param array $existinglinks - an array containing plugin action links
	 * @param string $currentplugin - the plugin that is currently being listed
	 * @return array - returns an updated links array for this plugin
	 */
	function plugin_links( $existinglinks, $currentplugin ){
		static $myplugin;

		//Static so we don't call plugin_basename on every plugin row.
		if ( ! $myplugin ) $myplugin = plugin_basename(__FILE__);

		// check if the plugin currently being listed is this plugin
		if ( $currentplugin == $myplugin ){

			// A link to the settings page of the plugin
			$settings = '<a href="options-general.php?page=kih-is-mobile">' .
								__('Settings') . '</a>';

			// place the settings link as the first item
			array_unshift( $existinglinks, $settings );
		}

		// return the new list of settings
		return $existinglinks;
	}

    /**
     * Test if the string identifies a mobile device
     *
     * @param string $str - the string to be tested
     * @return true if mobile, false if not
     */
    function check( $str ) {
    	$invalids = array( 	'.','+','*','?','^','$','(',')','[',']',
    						'&','*','%','/',"'",'"',';','<','>','\\');

        if (!get_option('kih_is_mobile_on')) return false;

        // get only 128 characters and delete characters not needed
        $agent = str_replace($invalids,'',substr($str,0,128));

        $mobiletypes = get_option('kih_is_mobile_types');

        foreach ($mobiletypes as $mobiletype) {
        	if (strstr($agent, $mobiletype)) {
        	    return true;
        	}
        }
        return false;
    }

    /**
     * Create the admin page of this plugin on the options menu of wordpress.
     */
    function add_admin() {
        // Create a submenu under Options:
        add_options_page( __('Options for is_mobile()'), __('is_mobile()'), 8,
        				'kih-is-mobile', array('kih_is_mobile','do_admin') );
    }

    /**
     * Display the options page for the plugin
     */
    function do_admin() {

        if (isset($_POST['action']) && $_POST['action'] == 'save') {
    		check_admin_referer('kih-ismobile-opts');
    		update_option('kih_is_mobile_on', $_POST['kih_is_mobile_on']);
    		kih_is_mobile::update_mobiletypes($_POST['kih_is_mobile_types']);

    		// display update message
    		echo "<div class='updated fade'><p>" . __('Options updated.') . "</p></div>";
    	}

	?>
	<div class="wrap">
		<h2><?php _e('Options for is_mobile()'); ?></h2>

		<form method="post">
		<?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('kih-ismobile-opts'); ?>
		<table class="form-table" cellspacing="2" cellpadding="5" width="100%">
			<tr valign="top">
				<th style="width:20%;">
					<label for="kih_is_mobile_on">Enable is_mobile()</label>
				</th>
				<td>
				<input type="checkbox" value="1" name="kih_is_mobile_on" id="kih_is_mobile_on"
        		<?php echo (get_option('kih_is_mobile_on') == 1 ? 'checked="checked"' : "" ); ?>
        		/>
				</td>
			</tr>
			<tr valign="top">
				<th style="width:20%;">
					<label for="kih_is_mobile_types">Mobile Browser Strings</label>
				</th>
				<td>
				<textarea name="kih_is_mobile_types" id="kih_is_mobile_types"
	            cols="64" rows="6"
				><?php echo kih_is_mobile::get_mobiletypes(); ?></textarea>
				</td>
			</tr>
		</table>

        <p class="submit"><input type="submit" class="button"
        	name="submitform" value="<?php _e('Save'); ?>" />
         	<input type="hidden" name="action" id="action" value="save" />
     	</p>
		</form>

		<?php kih_is_mobile::show_donate(); ?>
	</div>
	<?php
    }

    /**
     * Save the mobile types list as an array into the WP option
     *
     * @param string $csv - the comma-delimeted string of mobile types
     */
    function update_mobiletypes( $csv ) {
    	$invalids = array( 	'.','+','*','?','^','$','(',')','[',']',
    						'&','*','%','/',"'",'"',';','<','>','\\');

        // delete characters that are not valid
        $csv = str_replace($invalids,'',$csv);

    	$mobiletypes = explode(',',$csv);

    	$trimmed_mobiletypes = array();

    	// remove leading and trailing spaces just in case
        foreach( $mobiletypes as $mobiletype )
        	$trimmed_mobiletypes[] = trim( $mobiletype );

        update_option('kih_is_mobile_types', $trimmed_mobiletypes);
    }

    /**
     * retrieve the mobile types array and return as a string for
     * display in the admin form
     *
     * @return string - mobile type comma-delimited list
     */
    function get_mobiletypes() {
        $mobiletypes = (array) get_option( 'kih_is_mobile_types' );
        return( implode(',',$mobiletypes) );
    }

    /**
     * Show the donate button
     */
    function show_donate() {
?>
<style type="text/css">
.paypaldonate {	clear:both;}
.paypaldonate p { color:black;float:left;font-size:17px;font-weight:normal;margin:5px; }
.paypaldonate form {margin-top:10px;}
</style>
<div class="paypaldonate">
<p>Please
donate thru PayPal to support the continued development of this plugin.</p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="4676436">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
</div>
<?php
    }
}

// Create the hooks to Wordpress
add_action('admin_menu', array('kih_is_mobile','add_admin'));
add_filter( 'plugin_action_links', array( 'kih_is_mobile', 'plugin_links'), 10, 2 );

add_option('kih_is_mobile_on', 0 );
add_option('kih_is_mobile_types',
array('SymbianOS','Symbian OS','SonyEricsson','MOT-V','Nokia','MOT-RAZR') );

?>
