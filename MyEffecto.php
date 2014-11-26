<?php
/*
Plugin Name: MyEffecto
Plugin URI: www.myeffecto.com
Description: Getting customized and interactive feedback for your blog.
Version: 1.0.36
Author URI: www.myeffecto.com
*/

require('DBFunctions.php');
require('PostConfiguration.php');

/* Add MyEffecto link to Setting Tab. */
add_action('admin_menu', 'myeffecto_admin_actions');
add_filter( 'the_content', 'echoEndUserPlugin');

$embedCode = null;
$hostString="http://www.myeffecto.com";
$eff_ssl_host = "https://myeffecto.appspot.com";
//$hostString="http://localhost:8888";
$eff_settings_page = "eff_conf_nav";

if (is_ssl()) {
	$hostString = $eff_ssl_host;
}

/* Show plugin on Menu bar */
function myeffecto_admin_actions() {
	global $eff_settings_page;
	add_options_page('MyEffecto', 'MyEffecto', 'manage_options', $eff_settings_page, 'myeffecto_admin', null, '59.5');
}

function effInitScripts($hook) {
	global $eff_settings_page;
	if (is_admin()) {
		if ($hook == "post.php" || $hook == "post-new.php" || $hook == "settings_page_".$eff_settings_page) {
			wp_enqueue_script("jquery");
			/* wp_enqueue_script("jquery-ui-dialog");
			wp_enqueue_style("wp-Myeffecto", "http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.min.css"); */
		}
	}
}
add_action("admin_enqueue_scripts", "effInitScripts");

function myeffecto_get_version() {
	$plugin_data = get_plugin_data( __FILE__ );
	$plugin_version = $plugin_data['Version'];
	return $plugin_version;
}

function myeffecto_admin() {
	 $user_id = get_current_user_id();
	 $data = null;
	if (isset($_POST['dataToSend'])) {
		$data=$_POST['dataToSend'];
	}

	$eff_shortname = null;
	if (isset($_POST['eff_shortname'])) {
		$eff_shortname = $_POST['eff_shortname'];
	}

	$postID = null;
	if (isset($_GET['postID'])) {
		$postID = $_GET['postID'];
	}

	$postName = null;
	if (isset($_GET['postName'])) {
		$postName = $_GET['postName'];
	}

	$postURL = null;
	if (isset($_GET['postURL'])) {
		$postURL = $_GET['postURL'];
	}

	$shortname = null;
	if (isset($_GET['shortname'])) {
		$shortname = $_GET['shortname'];
	}
?> 
	<form id="submitForm" action="" method="post" style="display:none;">
		<input name="isToInsert" value="true" id="isToInsert" type="hidden"/>
		<input name="dataToSend" id="dataToSend" type="hidden"/>
		<input name="eff_shortname" id="eff_shortname" type="hidden"/>
		<input type='submit'/>
	</form>
	<form id="reloadForm" action="" method="post" style="display:none;"><input type='submit'/></form>
<?php
	if(isset($data) && !empty($data)) {
		$isCodeExistArray = getMyEffectoPluginDetails($postID);
		$isCodeExist=null;
		
		if (isset($isCodeExistArray)) {
			foreach($isCodeExistArray as $detail) {
				$isCodeExist = $detail -> embedCode;
			}
		}
		if ($isCodeExist == null) {
			if (!isset($postID) || empty($postID)) {
					$defaultEdit = null;
					if (isset($_GET['pluginType'])) {
						$defaultEdit = $_GET['pluginType'];
					}
				    if (isset($defaultEdit) && $defaultEdit == "defaultEdit") {
						updateMyeffectoEmbedCode($data, 0, $eff_shortname);
					?>
						<script type="text/javascript">
								window.location= <?php echo "'" . $postURL . "&action=edit&plugin=success'"; ?>;
						</script>
				<?php
					} else {
						insertInMyEffectoDb($user_id, null, $data, null, $eff_shortname);
						if (isset($postURL) && !empty($postURL)) {
							?>
								<script type="text/javascript">
									window.location= <?php echo "'" . $postURL . "&action=edit&plugin=success'"; ?>;
							   </script>
							<?php
						} else {
							echo "<h1><center>Your emoticon set has been added on your posts successfully.<br><br>Go to your post to see the effect.</center></h1>";
						}
					}
			} else {
				insertInMyEffectoDb($user_id, null, $data, $postID, $eff_shortname);
				?>
					<script type="text/javascript">
				   <!--
						window.location= <?php echo "'" . $postURL . "&action=edit&plugin=success'"; ?>;
				   //-->
				   </script>
				<?php
			}
		} else {
			$addType = $_GET['pluginType'];
			if ($addType == "postEdit") {
					
					updateMyeffectoEmbedCode($data, $postID, $eff_shortname);
				?>
					<script type="text/javascript">
						window.location= <?php echo "'" . $postURL . "&action=edit&plugin=success'"; ?>;
					</script>
				<?php
			}
		}
	 } else {
?>
<style type="text/css">
	#load {
		background: url(" <?php echo plugins_url( 'loading.gif' , __FILE__ );?> ") no-repeat scroll center center #FFF;
		bottom: 0;
		left: 0;
		position: absolute;
		opacity: 0.63;
		right: 0;
		top: 0;
		width: 100%;
		z-index: 1000;
	}
</style>

		<div class="wrap" style="overflow-x : hidden; position : relative;">
			<h2>MyEffecto Admin</h2>
	<?php
			global $embedCode;
			$apiKey=null;
			$myeffectoArray = array();

			delete_option('myeffecto_apikeys'.'#@#'.$user_id);
			if (get_option('myeffecto_apikeys'.'#@#'.$user_id)) {
				$myeffectoArray = get_option('myeffecto_apikeys'.'#@#'.$user_id);
			} else {
				$isFirstUser=false;
				global $wpdb;
				$effecto_db_version = myeffecto_get_version();
				$table_name = $wpdb->prefix . "effecto";
				$eff_get_dbVersion = get_option('effecto_db_version');
				if ($eff_get_dbVersion != $effecto_db_version) {
					createEffectoTable($effecto_db_version);
					update_option('effecto_db_version', $effecto_db_version);
				} else {
					$apiEmbedArrayDtls = getMyEffectoPluginDetails(0);
					$apiEmbedArray="";
					foreach($apiEmbedArrayDtls as $detail) {
						$apiEmbedArray = $detail -> embedCode;
					}
					
					$embedCode=$apiEmbedArray;

					if (!isset($embedCode) || empty($embedCode)) {
						$isFirstUser=true;
					} else {
						$myeffectoArray['userID']=$user_id ;
						//$myeffectoArray['apiKey']=$apiEmbedArray->apiKey;
						$myeffectoArray['embedCode']=$apiEmbedArray;
						//update_option('myeffecto_apikeys'.'#@#'.$user_id, $apiEmbedArray);
					}
				}

				if ($isFirstUser) {
					echoUserScript();
					return;
				}
			}

			if (isset($embedCode) && !empty($embedCode) && (!isset($postURL) || empty($postURL))) {
				allSetCode($embedCode, null);
			} else {
				echoUserScript();
			}
	?>
		</div>
	<?php
		}
	}

	function configurationScript($shortname, $globalPostID, $title) {
	    global $hostString;
		echo '<script>
				var shortname = "'.$shortname.'";
				var effecto_identifier = "'.$globalPostID.'";
				var postTitle="'.$title.'";

				function save(shortname) {
					if (shortname == null || shortname === "" || shortname === "undefined") {
						ifrm.contentWindow.postMessage("Save#~#postTitle#~#"+postTitle,"'.$hostString.'");
					} else {
						ifrm.contentWindow.postMessage("Save#~#delete#~#"+shortname+"#~#"+effecto_identifier+"#~#postTitle#~#"+postTitle,"'.$hostString.'");
						shortname = "";
						effecto_identifier = "";
					}
				}

				function receiveMessage(event) {
					var rcvdMsg = event.data;
					var msg = rcvdMsg.split("#~#");

					if (msg[0] == "save") {
						postIframeCode(msg[1]);
						jQuery(\'#load\').show();
					} else if(msg[0] == "loggedIn") {
						afterLoginSuccess();
					} else if (msg[0] == "error") {
						alert("Error occured");
					} else if (msg[0] == "pluginLoggedIn") {
						showButtonCode(shortname);
					} else if (msg[0] == "validated") {
						jQuery(\'#load\').show();
					} /*else if(msg[0] == "apiKey") {
						addKey(msg[1]);
					}*/
				}

				function postIframeCode(rcvdMsg) {
					var dataToSend = { "insert":"true", "data" : rcvdMsg};
					var test = JSON.parse(rcvdMsg);
					jQuery("#dataToSend").val(test.embedCode);
					jQuery("#eff_shortname").val(test.shortName);
					jQuery(\'#submitForm\').submit();
				}

				function showButtonCode(shortname) {
					jQuery(\'#generate\').remove();

					if (shortname === null) {
					    shortname="";
						jQuery(\'#effectoFrame\').after(jQuery(\'<center><h3><input type="button" id="generate"  value="Apply Emotion Set" style="font-size : 22px; padding-top : 7px; padding-bottom : 30px;" class="button-primary" /></h3></center>\'));
					} else {
						jQuery(\'#effectoFrame\').after(jQuery(\'<center><h3><input type="button" id="generate" value="Apply Emotion Set" style="font-size : 22px; padding-top : 7px; padding-bottom : 30px;" class="button-primary" /></h3></center>\'));
					}
					
					jQuery("#generate").click(function(){
							save(shortname);
						
					});
				}

				function afterLoginSuccess() {
					ifrm.setAttribute("src", "'.$hostString.'/confgEmoji?outside=true&postTitle=" + postTitle);
				}';
	}

	function echoUserScript() {
		global $hostString;

		$shortname = null;
		if (isset($_GET['shortname'])) {
			$shortname = $_GET['shortname'];
		}

		$globalPostID = null;
		if (isset($_GET['postID'])) {
			$globalPostID = $_GET['postID'];
		}

		$postname = null;
		if (isset($_GET['postName'])) {
			$postname = $_GET['postName'];
		}

		configurationScript($shortname, $globalPostID, $postname);
		echo '	var ifrm= null;
				window.onload=function(){
					ifrm = document.getElementById("effectoFrame");
					ifrm.setAttribute("src", "'.$hostString.'/register?callback=confgEmoji&outside=true&postTitle="+postTitle);
					ifrm.setAttribute("frameborder","0");
					ifrm.setAttribute("allowtransparency","true");

					ifrm.style.width = "100%";
					ifrm.style.height = "500";
					window.addEventListener("message", receiveMessage, false);
				};
			</script>
			<div id="load" style="display:none;"></div>
			<iframe id="effectoFrame" src ="'.$hostString.'/register?callback=confgEmoji&outside=true&postTitle="+postTitle width="100%" height="500"/>';
	}
	
	function eff_is_html($string) {
	  return preg_match("/<[^<]+>/",$string,$m) != 0;
	}

	/* Show plugin in posts. */
	function echoEndUserPlugin($text) {
		global $hostString;
		global $eff_ssl_host;

		if (is_single())
		{
			wp_enqueue_script("wp-mye-load","//cdn-files.appspot.com/js/mye-wp-load.js",null,null,false);
			$postId = get_the_ID();
			$getPostTitle = get_the_title();
			$wpSite = get_site_url();
			$effectoPreview = "false";
			$effectoAuthor = effecto_get_author();
			$eff_category = effecto_get_category($postId);

			$apiPluginDetailsArray = getMyEffectoPluginDetails($postId);
			if ($apiPluginDetailsArray == null) {
				$apiPluginDetailsArray = getMyEffectoPluginDetails(0);
			}
			$apiEmbedArray="";
			$p_shortname="";
			foreach($apiPluginDetailsArray as $detail) {
				$apiEmbedArray = $detail -> embedCode;
				$p_shortname = $detail -> shortname;
			}

			$effDate_published = get_the_date("l,F d,Y");
			if (is_preview()) {
				$effectoPreview = "true";
				$postId = 0;
			}

			if (is_ssl()) {
				$hostString = $eff_ssl_host;
			}
			/* $apiEmbedArray = str_replace("var effectoPostId=''","var effectoPostId='".$postId."'", $apiEmbedArray);
			$apiEmbedArray = str_replace("var effectoPreview=''","var effectoPreview='".$effectoPreview."'", $apiEmbedArray);
			$apiEmbedArray = str_replace("var effectoPagetitle = ''","var effectoPagetitle='".$getPostTitle."'", $apiEmbedArray);
			$apiEmbedArray = str_replace("var effectoPageurl = ''","var effectoPageurl='".$wpSite."?p=".$postId."'", $apiEmbedArray);
			$apiEmbedArray = str_replace("var effectoPublDate = ''","var effectoPublDate='".$effDate_published."'", $apiEmbedArray);
			$apiEmbedArray = str_replace("var effectoAuthorName = ''","var effectoAuthorName='".$effectoAuthor."'", $apiEmbedArray);
			$apiEmbedArray = str_replace("var effectoCategory = ''","var effectoCategory='".$eff_category."'", $apiEmbedArray); */

			$getPostTitle = str_replace("'","\'", $getPostTitle);
			$eff_category = str_replace("'","\'", $eff_category);

			//User Info
			global $current_user;
			get_currentuserinfo();
			$eff_cur_loggedIn = is_user_logged_in();
			$eff_user_role = $current_user->user_login;
			$eff_user_email = $current_user->user_email;
			$eff_user_display = str_replace("'","\'", $current_user->display_name);
			$eff_user_fname = str_replace("'","\'", $current_user->user_firstname);
			$eff_user_lname = str_replace("'","\'", $current_user->user_lastname);

			$eff_json = "<div id='effecto_bar' V='1.5' style='text-align:center;'></div>
						<script>
							var eff_json = {
								'ext_path':'".plugins_url( '' , __FILE__ )."',
								'effecto_uniquename':'".$p_shortname."', 
								'effectoPostId':'".$postId."',  
								'effectoPreview': '".$effectoPreview."', 
								'effectoPagetitle':'".$getPostTitle."', 
								'effectoPageurl':'".$wpSite.'?p='.$postId."', 
								'effectoPublDate':'".$effDate_published."', 
								'effectoAuthorName':'".$effectoAuthor."', 
								'effectoCategory':'".$eff_category."', 
								'effUserInfo': {
									'isLoggedIn': '".$eff_cur_loggedIn."',
									'loginAs': '".$eff_user_role."',
									'email': '".$eff_user_email."',
									'dpName': '".$eff_user_display."',
									'fName': '".$eff_user_fname."',
									'lName': '".$eff_user_lname."'
								}
							};
						</script><script id='effectp-code' src='//cdn-files.appspot.com/js/mye-wp.js' type='text/javascript' async='true'></script>
						";
			// return $apiEmbedArray.$text;
			return $text.$eff_json;
		} else {
			return $text;
		}
	}

	/* Simple string replace function */
	function replaceText ($text) {
		$text = str_replace('\"','', $text);
		return $text;
	}

	function addAlert($pluginStatus) {
		if (isset($pluginStatus) && !empty($pluginStatus)) {
?>
			<script type="text/javascript">
				$j = jQuery;
				$j().ready(function() {
					$j('.wrap > h2').parent().prev().after('<div class="update-nag"><h3>MYEFFECTO Emotion Set has been added. Check-out <strong>MyEffecto Configuration</strong> panel below.</h3><br/> Note: If you cannot see plugin OR if an error message appears OR number of emoticons are not the same as you saw on your selected set, refresh the page to see the set. </div>');
				});
			</script>
<?php
		}
	}

	function eff_pluginDeactivate() {
		$shortname = getMyEffectoShortnames();
		if ($shortname) {
			global $hostString;
			$args = array(
				'body' => array('action' => 'updateStatus', 'status' => 'Deactivated', 'sname' => $shortname, 'utm' => get_option( 'admin_email' )),
			);
			wp_remote_post($hostString.'/contentdetails', $args);
		}
	}
	register_deactivation_hook( __FILE__, 'eff_pluginDeactivate');

	function eff_pluginUninstall() {
        global $wpdb;
        $table = $wpdb->prefix."effecto";
		delete_option("effecto_db_version");
		$wpdb->query("DROP TABLE IF EXISTS $table");
	}
	register_uninstall_hook( __FILE__, 'eff_pluginUninstall' );

	
?>