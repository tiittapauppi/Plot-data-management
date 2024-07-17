<?php
/*
Plugin Name:        Plot data management
Plugin URI:         
Description:        Provides Wordpress plugin to find instructions for players in a larp
Version:            2.0
Requires at least:  5.2
Requires PHP:       7.2 or later
Author:             Riffiria
License:            MIT License
*/

/**
 * Enqueue scripts and basic plugin styling
 */
function my_airtable_scripts() {
    $plugin_url = plugin_dir_url( __FILE__ );
    wp_enqueue_style( 'mwt-style', $plugin_url . 'css/mwt-style.css'  );

    // Enqueue JQueryUI Date picker to ensure common Date Picker UX across main browsers e.g. Safari
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
    

    // Send info to page for Javascript to use.
    wp_localize_script('main_js', 'mwtWebtech', array(
        'nonce' => wp_create_nonce('wp_rest'),
        'siteURL' => get_site_url(),
    ));
}

add_action( 'wp_enqueue_scripts', 'my_airtable_scripts' );

// Register an endpoint on the WP REST API to enable an Airtable Event to be updated

add_action( 'rest_api_init', function () {
    register_rest_route( 'mwtwebtech/v1', '/events', array(
      'methods' => 'POST',
      'callback' => 'updateAirtable',
    ) );
  } );

//   Add the JQuery datepicker script to the footer
  function add_datepicker_in_footer(){ ?>

        <script type="text/javascript">
        jQuery(document).ready(function(){
            jQuery('.date').datepicker({
                dateFormat: 'yy-mm-dd'
            });
        });
        </script>

    <?php
    } // close add_datepicker_in_footer() here

    //add an action to call add_datepicker_in_footer function
    add_action('wp_footer','add_datepicker_in_footer',10);

// Provide encrypt/decrypt functions for handling the api key - just to avoid plain text in database.
// Taken from: https://stackoverflow.com/questions/10154890/encrypting-strings-in-php
function encrypt($string, $key = 'PrivateKey', $secret = 'SecretKey', $method = 'AES-256-CBC') {
    // hash
    $key = hash('sha256', $key);
    // create iv - encrypt method AES-256-CBC expects 16 bytes
    $iv = substr(hash('sha256', $secret), 0, 16);
    // encrypt
    $output = openssl_encrypt($string, $method, $key, 0, $iv);
    // encode
    return base64_encode($output);
}

function decrypt($string, $key = 'PrivateKey', $secret = 'SecretKey', $method = 'AES-256-CBC') {
    // hash
    $key = hash('sha256', $key);
    // create iv - encrypt method AES-256-CBC expects 16 bytes
    $iv = substr(hash('sha256', $secret), 0, 16);
    // decode
    $string = base64_decode($string);
    // decrypt
    return openssl_decrypt($string, $method, $key, 0, $iv);
}


// Now add an admin facility to to enter the Airtable parameters
// Inspired by https://travis.media/where-do-i-store-an-api-key-in-wordpress/
// Creates a subpage under the Tools section
add_action('admin_menu', 'register_my_airtable_api_parameters');
function register_my_airtable_api_parameters() {
    add_submenu_page(
        'plugins.php',
        'AirTable game instructions database',
        'AirTable game instructions database',
        'manage_options',
        'airtable-api',
        'add_airtable_api_parameters' );
}
 
// The admin page containing the Airtable parameters form
function add_airtable_api_parameters() { 

	$allow_gm_access = get_option('allow_gm_access');
	
	if(empty($allow_gm_access)){
		add_option('allow_gm_access', "Yes");
	}

	$allow_manual_character_name = get_option('allow_manual_character_name');
	
	if(empty($allow_manual_character_name)){
		add_option('allow_manual_character_name', "Yes");
	}
	?>
    <div class="wrap"><div id="icon-tools" class="icon32"></div>
        <h2>AirTable game instructions database settings</h2>
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
            <h3>Your Airtable Personal Access Token</h3>      
			<?php
            // Provide User feedback to show when API Key is set - but don't display the actual key.
            if (get_option('api_key')) {
                echo "<p>PAT is set</p>";
            } else {
                echo "<p>PAT is NOT set</p>";
            }
            ?>
            <input type="text" name="api_key" placeholder="Enter PAT">
            <input type="hidden" name="action" value="process_api_key">			 
            <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update PAT"  />
        </form>
    </div>

    <div class="wrap"><div id="icon-tools" class="icon32"></div>
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
            <h3>Your Table ID</h3>
            <p>Currently: <?php echo get_option('api_table_name') ?></p>
            <input type="text" name="api_table_name" placeholder="Enter Table ID">
            <input type="hidden" name="action" value="process_table_name">			 
            <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update Table ID"  />
        </form> 
    </div>

    <div class="wrap"><div id="icon-tools" class="icon32"></div>
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
            <h3>Your GM group name</h3>
            <p>Currently: <?php echo get_option('gm_group_name') ?></p>
            <input type="text" name="gm_group_name" placeholder="Enter GM Group Name">
            <input type="hidden" name="action" value="process_gm_group_name">			 
            <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update GM Group name"  />
        </form> 
    </div>

    <div class="wrap"><div id="icon-tools" class="icon32"></div>
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
            <h3>Your Everyone group name</h3>
            <p>Currently: <?php echo get_option('everyone_group_name') ?></p>
            <input type="text" name="everyone_group_name" placeholder="Enter Everyone Group Name">
            <input type="hidden" name="action" value="process_everyone_group_name">			 
            <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update Everyone Group name"  />
        </form> 
    </div>

    <div class="wrap"><div id="icon-tools" class="icon32"></div>
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
            <h3>Allow manual character name entering</h3>
            <p>Currently: <?php echo get_option('allow_manual_character_name') ?></p>
            <input type="radio" name="allow_manual_character_name" id="true" value="Yes" <?php echo ($allow_manual_character_name== "Yes") ?  "checked" : "" ;  ?>> Yes
            <input type="radio" name="allow_manual_character_name" id="false" value="No" <?php echo ($allow_manual_character_name== "No") ?  "checked" : "" ;  ?>> No
            <input type="hidden" name="action" value="process_allow_manual_character_name">			 
            <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update Allow manual character name entering"  />
        </form> 
    </div>

    <div class="wrap"><div id="icon-tools" class="icon32"></div>
        <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="POST">
            <h3>Allow access to GM data without Wordpress login</h3>
            <p>Currently: <?php echo get_option('allow_gm_access') ?></p>
            <input type="radio" name="allow_gm_access" id="gmyes" value="Yes" <?php echo ($allow_gm_access== "Yes") ?  "checked" : "" ;  ?>> Yes
            <input type="radio" name="allow_gm_access" id="gmno" value="No" <?php echo ($allow_gm_access== "No") ?  "checked" : "" ;  ?>> No
            <input type="hidden" name="action" value="process_allow_gm_access">			 
            <input type="submit" name="submit" id="submit" class="update-button button button-primary" value="Update Allow access to GM data"  />
        </form> 
    </div>

    <?php
}

// Submit Airtable API key functionality
function submit_api_key() {
    if (isset($_POST['api_key'])) {
        $api_key = sanitize_text_field( $_POST['api_key'] );
        $api_key_secured = encrypt($api_key);
        $api_exists = get_option('api_key');
        if (!empty($api_key_secured) && !empty($api_exists)) {
            update_option('api_key', $api_key_secured);
        } else {
            add_option('api_key', $api_key_secured);
        }
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
}
add_action( 'admin_post_nopriv_process_api_key', 'submit_api_key' );
add_action( 'admin_post_process_api_key', 'submit_api_key' );


// Submit Airtable URL functionality
function submit_api_url() {
    if (isset($_POST['api_url'])) {
        $api_url = sanitize_text_field( $_POST['api_url'] );
        $url_exists = get_option('api_url');
        if (!empty($api_url) && !empty($url_exists)) {
            update_option('api_url', $api_url);
        } else {
            add_option('api_url', $api_url);
        }
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
}

add_action( 'admin_post_nopriv_process_url', 'submit_api_url' );
add_action( 'admin_post_process_url', 'submit_api_url' );

// Submit Airtable Volunteers Table Name functionality
function submit_api_table_name() {
    if (isset($_POST['api_table_name'])) {
        $api_table_name = sanitize_text_field( $_POST['api_table_name'] );
        $table_name_exists = get_option('api_table_name');
        if (!empty($api_table_name) && !empty($table_name_exists)) {
            update_option('api_table_name', $api_table_name);
        } else {
            add_option('api_table_name', $api_table_name);
        }
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
}

add_action( 'admin_post_nopriv_process_table_name', 'submit_api_table_name' );
add_action( 'admin_post_process_table_name', 'submit_api_table_name' );

function submit_gm_group_name() {
    if (isset($_POST['gm_group_name'])) {
        $gm_group_name = sanitize_text_field( $_POST['gm_group_name'] );
        $gm_group_exists = get_option('gm_group_name');
        if (!empty($gm_group_name) && !empty($gm_group_exists)) {
            update_option('gm_group_name', $gm_group_name);
        } else {
            add_option('gm_group_name', $gm_group_name);
        }
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
}

add_action( 'admin_post_nopriv_process_gm_group_name', 'submit_gm_group_name' );
add_action( 'admin_post_process_gm_group_name', 'submit_gm_group_name' );

function submit_everyone_group_name() {
    if (isset($_POST['everyone_group_name'])) {
        $everyone_group_name = sanitize_text_field( $_POST['everyone_group_name'] );
        $everyone_group_exists = get_option('everyone_group_name');
        if (!empty($everyone_group_name) && !empty($everyone_group_exists)) {
            update_option('everyone_group_name', $everyone_group_name);
        } else {
            add_option('everyone_group_name', $everyone_group_name);
        }
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
}

add_action( 'admin_post_nopriv_process_everyone_group_name', 'submit_everyone_group_name' );
add_action( 'admin_post_process_everyone_group_name', 'submit_everyone_group_name' );


function submit_allow_manual_character_name() {
    if (isset($_POST['allow_manual_character_name'])) {
        $allow_manual_character_name = $_POST['allow_manual_character_name'];
        $allow_manual_character_name_exists = get_option('allow_manual_character_name');
        if (!empty($allow_manual_character_name) && !empty($allow_manual_character_name_exists)) {
            update_option('allow_manual_character_name', $allow_manual_character_name);
        } 
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
}

add_action( 'admin_post_nopriv_process_allow_manual_character_name', 'submit_allow_manual_character_name' );
add_action( 'admin_post_process_allow_manual_character_name', 'submit_allow_manual_character_name' );

function submit_allow_gm_access() {
    if (isset($_POST['allow_gm_access'])) {
        $allow_gm_access = $_POST['allow_gm_access'];
        $allow_gm_access_exists = get_option('allow_gm_access');
        if (!empty($allow_gm_access) && !empty($allow_gm_access_exists)) {
            update_option('allow_gm_access', $allow_gm_access);
        } 
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
}

add_action( 'admin_post_nopriv_process_allow_gm_access', 'submit_allow_gm_access' );
add_action( 'admin_post_process_allow_gm_access', 'submit_allow_gm_access' );

// 
// // Register the shortcode that can be used to get the logged in user's profile - assuming that their wp username and airtable username match.
add_shortcode('my-airtable', 'myAirTable');


// Now we are all setup we can get the airtable data and display it where the shortcode is located
function myAirTable() {
    // Only show profile if eligible role

	$current_user = wp_get_current_user();
	$my_table_name = get_option('api_table_name');
	$my_airtable_api = 'https://api.airtable.com/v0/';
	$my_gm_group = strtolower(get_option('gm_group_name'));
	$my_everyone_group = strtolower(get_option('everyone_group_name'));
	$allow_manual_character_name = get_option('allow_manual_character_name');
	$allow_gm_access = get_option('allow_gm_access');	
	

	$pastCharacter = $_POST['character'];
	$lowCharacter = strtolower($pastCharacter);
	$searchUser = $current_user->user_login;
	$lowUser = strtolower($searchUser);

	$pastCode = $_POST['code'];
	$lowCode = strtolower($pastCode);

	$id_token = decrypt(get_option('api_key'));

	$args = array(
		'headers' => array(
			'Authorization' => 'Bearer ' . $id_token,
		)
	);
	if(!empty($lowCharacter)){
		$char_remote_url = $my_airtable_api . $my_table_name . '/Characters?filterByFormula=(LOWER(Character)%3D"' . $lowCharacter .'")'; // Get Character data
	} else {
		$lowCharacter = $lowUser;
		$char_remote_url = $my_airtable_api . $my_table_name . '/Characters?filterByFormula=(LOWER(Username)%3D"' . $lowUser .'")'; // Get Character data based on username
	}
	
	$char_result = wp_remote_get($char_remote_url, $args);
	$char_body = wp_remote_retrieve_body($char_result);
	$char_data = json_decode($char_body);
	$char_id = $char_data->records[0]->id;

	foreach($char_data->records as $char_item) {
		if(!empty($char_item->fields->Groups)){
			$char_groups = $char_item->fields->Groups;
		}
	}

	$gm_remote_url = $my_airtable_api . $my_table_name . '/Groups?filterByFormula=(LOWER(Group)%3D"' . $my_gm_group  .'")'; //Get ID of the GM group
	$gm_result = wp_remote_get($gm_remote_url, $args);
	$gm_body = wp_remote_retrieve_body($gm_result);
	$gm_data = json_decode($gm_body);
	$gm_group = $gm_data->records[0]->id;
	
	if(!empty($gm_group) && !(empty($char_groups))){
		$is_gm = in_array($gm_group,$char_groups);
 		if($allow_gm_access == "No" && $lowCharacter != $lowUser){
			$is_gm = false;
		}
	}
	
	$everyone_remote_url = $my_airtable_api . $my_table_name . '/Groups?filterByFormula=(LOWER(Group)%3D"' . $my_everyone_group  .'")'; //Get ID of the Everyone group
	$everyone_result = wp_remote_get($everyone_remote_url, $args);
	$everyone_body = wp_remote_retrieve_body($everyone_result);
	$everyone_data = json_decode($everyone_body);
	$everyone_group = $everyone_data->records[0]->id;	

	$code_remote_url = $my_airtable_api . $my_table_name . '/Information?filterByFormula=(LOWER(Code)%3D"' . $lowCode .'")&sort%5B0%5D%5Bfield%5D=Order&sort%5B0%5D%5Bdirection%5D=asc';
	$code_result = wp_remote_get($code_remote_url, $args);
	$code_body = wp_remote_retrieve_body($code_result);
	$code_data = json_decode($code_body, false);

	if (isset($_POST['character'], $_POST['code'])) {
		$character = $_POST['character'];
		$code = $_POST['code']; 

			// store session data
		$_SESSION['character'] = $character;
		$_SESSION['code'] = $code;
	}	

	$profile = '<div class=mwt-container>'
               . '<div class="mwt-item">'
	           . '<form id="search-form" action="" method="POST">';
	
	if($allow_manual_character_name== "Yes"){
		$profile .= '<input type="text" name="character" id="character" value="' . $_SESSION['character'] .'" placeholder="Character ID" />';
	}
	$profile .= '<input type="text" name="code" id="code" value="" placeholder="Id of the target" />'					 
             . '<input type="submit" class="submit-button" data-event="search-data "name="submit" value="Search" />'
             . '</form>';
		
	if(!empty($_SESSION['code'])){
		$profile .=  '<h5 class="profile-details__header">Results of latest search - <i>Character ID: ' . $lowCharacter . " / ID: " . $lowCode . '</i></h5>'	
                . '<table class="results">';

		$groupChars = "Characters (from Groups)"; 

		if($code_data) {
			$visibility = false;
				
			if($is_gm == true){
				$profile .= '<th>GM Description:' . $code_data->records[0]->fields->Description . '</th>';	
			} 
				
			foreach($code_data->records as $item) {			
				$value = false;
				$itemChars = array();
				$itemGroups = array();
				$itemGroupChars = array();
				if(!empty($item->fields->Groups)){
					$itemGroups = $item->fields->Groups;
					$value = in_array($everyone_group, $itemGroups);
				}	
				if(!empty($item->fields->Characters) && $value == false){
					$itemChars = $item->fields->Characters;
					$value = in_array($char_id, $itemChars);
				} 
				if(!empty($item->fields->$groupChars) && $value == false){
					$itemGroupChars = $item->fields->$groupChars;
					$value = in_array($char_id, $itemGroupChars);
				}
					
				if($value == true || $is_gm == true){
					$visibility = true;
					$profile .= '<tr>';
					if($is_gm == true){
						$profile .= '<td>';
						$character_vis_list = "";
						$group_vis_list = "";
						if(count($itemChars) >0){
							foreach($itemChars as $character_vis) {
								$character_remote_url = $my_airtable_api . $my_table_name . '/Characters/' . $character_vis; 
								$character_result = wp_remote_get($character_remote_url, $args);
								$character_body = wp_remote_retrieve_body($character_result);
								$character_data = json_decode($character_body);
								$character_vis_list .= $character_data->fields->Character . ", ";								
							}
							$profile .= '<b>Characters: </b>' . $character_vis_list. "";
						}
						if(count($itemGroups) >0 && count($itemChars) >0){
							$profile .= '<br>';	
						}
						if(count($itemGroups) >0){
							foreach($itemGroups as $group_vis) {
								$group_remote_url = $my_airtable_api . $my_table_name . '/Groups/' . $group_vis; 
								$group_result = wp_remote_get($group_remote_url, $args);
								$group_body = wp_remote_retrieve_body($group_result);
								$group_data = json_decode($group_body);
								$group_vis_list .= $group_data->fields->Group . ", ";	
							}	
							$profile .= '<b>Groups: </b>' . $group_vis_list . "";
						}
						$profile .= '</td>';
					}
						$profile .= '<td>' . $item->fields->Notes . '</td></tr>';
					}	
				
				if($visibility == false){
					$profile .= '<tr><td>No results with target ID "' . $_SESSION['code'] .'" </td></tr>';
				}
			}
		}
	}
		$profile .= '</table>';                
		return $profile;	

}
?>