<?php
/*
Plugin Name: mySimpleAds Wordpress Ad Manager
Plugin URI: http://www.clippersoft.net
Description: The wordpress plugin will allow you to place your mySimpleAds Ads into posts and templates.  This plugin requires mySimpleAds from http://www.clippersoft.net .
Version: 1.0
Author: clippersoft.net
Author URI: http://www.clippersoft.net
License: GPL2
*/
DEFINE('DEBUG',false);


/**
 * msa_load_ad() Function to replace the ads in the content of a post
 * 
 * @param mixed $content  Current post content
 * @return string Content with the ads inserted
 */
function msa_load_ad($content)  {

	// No msa tags found, just return
	if (stripos ($content,'[msa_') === false) return $content;
	
	$msa_url = '';
	$msa_options = get_option('msa_options');
	$msa_url = $msa_options['msa_url'];
	if ($msa_url == '') return $content;
	
	$content_to_return = '';
	$start = 0;
	$end = 0;
	$insert = '';
	$strings = array();
	$strings_id = array();
	$strings_code = array();
	$aid = '0';
	$gid = '0';
	$code = 'p';
	$ad = '';
	$content_to_return = $content;
	while (false !== ($start = stripos ($content_to_return,'[msa_'))){
		
		$end = stripos ($content_to_return,']', $start+2);
		if ($end === false) { break; }
		$end++;
			
		if (DEBUG){echo '<br/ >$content_to_return='.$content_to_return;}
		if (DEBUG){echo '<br/ >$start='.$start;}
		if (DEBUG){echo '<br/ >$end='.$end;}
		
		if (!((is_home()) || (is_single()) || (is_page()))) {
			// Place the ad in the content
			$content_to_return = substr_replace($content_to_return,'',$start,$end - $start);
			continue;
		}
		
		$insert = '';
		$insert = substr($content_to_return,$start,$end - $start);
		
		$strings = explode(',',$insert);
		if (count($strings) == 0){ continue; }
		if (DEBUG){echo '<br/ >$strings=';print_r($strings);}
	
		// Get ad or group ID
		$aid = '0';
		$gid = '0';
		$strings_id = explode('=',trim($strings[0]));
		if (DEBUG){echo '<br/ >$strings_id=';print_r($strings_id);}
		if (count($strings_id) < 2){ continue; }
		if (DEBUG){echo '<br/ >count $strings_id='.count($strings_id);}
		$strings_id[1] = trim($strings_id[1],']');
		if (strtolower($strings_id[0]) == '[msa_aid'){
			$aid = $strings_id[1];
		} else if (strtolower($strings_id[0]) == '[msa_gid'){
			$gid = $strings_id[1];
		} else {
			continue;	
		}
		if (DEBUG){echo '<br />$strings_id[1]='.$strings_id[1];}
		if (DEBUG){echo '<br/ >$aid='.$aid;}
		if (DEBUG){echo '<br/ >$gid='.$gid;}

		// Get any possibly requested code type
		$code = 'p';
		if (count($strings) == 2){
			$strings_code = explode('=',$strings[1]);
			if (DEBUG){echo '<br/ >$strings_code=';print_r($strings_code);}
			if (count($strings_code) == 2){
				$strings_code[1] = trim(strtolower($strings_code[1]), ']');
				$strings_code[1] = trim($strings_code[1]);
				if (DEBUG){echo '<br/ >$strings_code[1]='.trim(strtolower($strings_code[1]));}
				if ((trim(strtolower($strings_code[1])) == 'p') || (trim(strtolower($strings_code[1])) == 'j') || (trim(strtolower($strings_code[1])) == 'a')){
					$code = trim(strtolower($strings_code[1]));
				}
			}
		}
		if (DEBUG){echo '<br/ >$code='.$code;}
		
		// Fetch ad
		$ad = '';
		$ad = _fetch_ad($msa_url,$aid,$gid,$code);
		
		// Place the ad in the content
		$content_to_return = substr_replace($content_to_return,$ad,$start,$end - $start);
	}

	return $content_to_return;
}


/**
 * msa_show_ad_id() Show an Ad using an Ad ID in a template
 * 
 * @param string $aid Ad ID
 * @param string $code (optional) Ad code type : p,j,a defaults to p
 * @return void
 */
function msa_show_ad_id($aid='0',$code='p'){
	
	$msa_url = '';
	$msa_options = get_option('msa_options');
	$msa_url = $msa_options['msa_url'];
	if ($msa_url == '') return;
	
	// Fetch ad
	$ad = '';
	$ad = _fetch_ad($msa_url,$aid,'0',$code);
	echo $ad;
	
	return;
}


/**
 * msa_show_group_id() Show an Ad Group using an Ad Group ID in a template
 * 
 * @param string $gid Ad Group ID
 * @param string $code (optional) Ad code type : p,j,a defaults to p
 * @return void
 */
function msa_show_group_id($gid='0',$code='p'){
	
	$msa_url = '';
	$msa_options = get_option('msa_options');
	$msa_url = $msa_options['msa_url'];
	if ($msa_url == '') return;
	
	// Fetch ad
	$ad = '';
	$ad = _fetch_ad($msa_url,'0',$gid,$code);
	echo $ad;
	
	return;
}


/**
 * _fetch_ad()  Fetches and displays the ad from the mysimpleads server
 * 
 * @param string $mysa_url URL of the mysimpleads install
 * @param string $aid Ad ID (or '0' if using a group)
 * @param string $gid Ad Group ID (or '0' if using an ad)
 * @param string $code Ad Code type (p,j,a)
 * @return string advertisement code
 */
function _fetch_ad($mysa_url='',$aid='0',$gid='0',$code='p'){
	
	$ad = '';
	
	if ($mysa_url == '') { return $ad; }
	
	if ((!is_numeric($aid)) && (!is_numeric($gid))){ return $ad; }
		
	$show = '';
	$aid = trim($aid);
	$gid = trim ($gid);
	if ($aid!='0') {
		$show = 'show_ad='.$aid;
	} else if ($gid!='0') {
		$show = 'show_ad_group='.$gid;
	} else {
		return $ad;	
	}

	$code = trim(strtolower($code));
	$spanid = '';
	if (($code == 'p') || ($code == '')){
		$ad = @file_get_contents($mysa_url.'/mysa_output.php?r='.$_SERVER['REMOTE_ADDR'].'&h='.urlencode($_SERVER['HTTP_HOST']).'&rf='.urlencode($_SERVER['HTTP_REFERER']).'&ua='.urlencode($_SERVER['HTTP_USER_AGENT']).'&'.$show);
	} else if ($code == 'j') {
		$spanid = _javascript_span_id();
		$ad = '<span id="'.$spanid.'"><script type="text/javascript"> var jsons_'.$spanid.'=null;var item_'.$spanid.'=-1;function handleJson_'.$spanid.'(mysa_json){if(jsons_'.$spanid.'==null){jsons_'.$spanid.'=mysa_json}item_'.$spanid.'++;if(jsons_'.$spanid.'.items[item_'.$spanid.']==null){item_'.$spanid.'=0};var span=document.getElementById(jsons_'.$spanid.'.items[item_'.$spanid.'].span);span.innerHTML=jsons_'.$spanid.'.items[item_'.$spanid.'].code}</script><script type="text/javascript"src="'.$mysa_url.'/mysa_output.php?callback=handleJson_'.$spanid.'&sid='.$spanid.'&'.$show.'"></script></span>';
	} else if ($code == 'a') {
		$spanid = _javascript_span_id();
		$ad = '<span id="'.$spanid.'"><script type="text/javascript"> var jsons_'.$spanid.'=null;var item_'.$spanid.'=-1;function handleJson_'.$spanid.'(mysa_json){if(jsons_'.$spanid.'==null){jsons_'.$spanid.'=mysa_json}item_'.$spanid.'++;if(jsons_'.$spanid.'.items[item_'.$spanid.']==null){item_'.$spanid.'=0}var span=document.getElementById(jsons_'.$spanid.'.items[item_'.$spanid.'].span);span.innerHTML=jsons_'.$spanid.'.items[item_'.$spanid.'].code}function aRotate_'.$spanid.'(mysa_json){jsons_'.$spanid.'=mysa_json;handleJson_'.$spanid.'(jsons_'.$spanid.');setInterval(handleJson_'.$spanid.',5000)}</script><script type="text/javascript"src="'.$mysa_url.'/mysa_output.php?callback=aRotate_'.$spanid.'&sid='.$spanid.'&'.$show.'"></script></span>';	
	} else {
		return $ad;
	}
	
	return $ad;
}

/**
 * _javascript_span_id() Random string to make function/var/span's uniquely named in current page
 * 
 * @return random span ID
 */
function _javascript_span_id (){

	$token = '';

	//Random string to make function/var/span's uniquely named in current page
	$length = 5;
	$string = md5(time());
	$highest_startpoint = 32 - $length;
	$token = 'i' . substr($string, rand(0, $highest_startpoint), $length);	
	
	return $token;
}


function msa_load_plugins(){

	// Add filter to add load ad
	add_filter('the_content', 'msa_load_ad');

}

// Execute outside of admin
if ( !is_admin() ){
    add_action('wp', 'msa_load_plugins');
}

function msa_admin_menu() {
	add_options_page( __("mySimpleAds Ad Manager Settings", "msa"), __("mySimpleAds Ad Manager Settings", "msa"), 9, __FILE__, "msa_options_page");
}

function msa_options_page() {
	
	$msa_options = get_option('msa_options');
	?>
	<div class="wrap">
 
		<h2><?php _e("mySimpleAds Wordpress Ad Manager Settings", "msa"); ?></h2>
 
		<h3><?php _e("Default Options", "msa"); ?></h3>
		
		<form method="post" action="options.php">
			<table class="form-table">
				<tbody>
					<tr valign="top"> 
						<th scope="row" style="width:20%;"><label for="msa_options[msa_url]"><?php _e("Your mySimpleAds URL (no trailing slash)", "msa"); ?></label></th> 
						<td><input type="text" name="msa_options[msa_url]" value="<?php echo $msa_options['msa_url']; ?>" class="regular-text code" /></td>
					</tr>
				</tbody>				
			</table>
 
			<p class="submit">
				<input type="submit" name="Submit" value="<?php _e('Save Changes'); ?>" />
			</p>

		<?php settings_fields('msa-group'); ?>
		</form>
		<h3>To use ...</h3>
		<strong>Posts/Pages:</strong><br />
		Place [msa_aid=x] for an Ad ID or [msa_gid=x] for an Ad Group ID in your post, where 'x' is the Ad or Ad Group ID and.<br />
		You can also optionally specify the type of ad code to use - (p) PHP Remote Read, (j) Javascript Injection, (a) Ajax Javascript, like:<br />
		[msa_gid=x,c=a] for Ajax Javascript.  If you do not specify, it will use PHP Remote Read by default.<br />
		<br />
		<strong>Templates:</strong><br />
		Place msa_show_ad_id($aid,$code); for an Ad ID or msa_show_group_id($gid,$code); for an Ad Group ID in your template PHP code (you may need to surround the function by PHP tags, if it's outside an existing
		 PHP tag section).  The $aid is the Ad ID, the $gid is the Ad Group ID, and $code is type type of ad code to use - 'p' PHP Remote Read, 'j' Javascript Injection, 'a' Ajax Javascript.  It defaults to PHP Remote Read.<br />

 
	</div>

	<?php
}

function activate_msa() {
	$msa_options = get_option('msa_options');
	if (!$msa_options){
		$msa_options_array = array(	'msa_url'=>''
								);	
		add_option('msa_options',$msa_options_array);
	}
}

// adding admin menus
if ( is_admin() ){ // admin actions
	register_activation_hook( __FILE__, 'activate_msa' );
	add_action('admin_menu', 'msa_admin_menu');
	add_action( 'admin_init', 'register_msa_settings' ); 
} 
function register_msa_settings() {
	register_setting( 'msa-group', 'msa_options' );
}
