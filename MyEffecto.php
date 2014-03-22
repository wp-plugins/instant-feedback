<?php
/*
Plugin Name: My Effecto
Plugin URI: www.myeffecto.com
Description: Getting customized and interactive feedback for your blog.
Version: 1.0.18
Author URI: www.myeffecto.com
*/
//error_reporting(0);
require('DBFunctions.php');
require('PostConfiguration.php');
/* Add MyEffecto link to Setting Tab. */
add_action('admin_menu', 'myeffecto_admin_actions');
add_filter( 'the_content', 'echoEndUserPlugin');

$embedCode = null;

$hostString="http://www.myeffecto.com";
// $hostString="http://localhost:8888";
/* Show plugin on Menu bar */
function myeffecto_admin_actions() {
	add_options_page('MyEffecto', 'MyEffecto', 'manage_options', _FILE_, 'myeffecto_admin', null, '59.5');
}

function effInitScripts($hook) {
	if (is_admin()) {
		if ($hook == "post.php" || $hook == "post-new.php" || $hook == "settings_page__FILE_") {
			wp_enqueue_script("jquery");
			wp_enqueue_script("jquery-ui-dialog");
			wp_enqueue_style("wp-Myeffecto", "http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.min.css");
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
		foreach($isCodeExistArray as $detail) {
			$isCodeExist = $detail -> embedCode;
		}
		if ($isCodeExist == null) {
			if (!isset($postID) || empty($postID)) {
				    $defaultEdit = $_GET['pluginType'];
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
				
					jQuery(\'#effectoFrame\').parent().prepend(jQuery(\'<input type="button" id="generate" onclick="save("")" value="Generate Plugin" class="button-primary"/>\'));
					ifrm.setAttribute("src", "'.$hostString.'/confgEmoji?outside=true&postTitle=" + postTitle);
				}';
	}

	/*function echoFirstUserScript() {
		global $shortname;
		global $globalPostID;
		global $hostString;
		configurationScript($shortname, $globalPostID);
		echo '	var ifrm = null;
				window.onload=function(){
					ifrm = document.getElementById("effectoFrame");
					ifrm.setAttribute("src", "'.$hostString.'/login?callback=configureplug");
					ifrm.setAttribute("frameborder","0");
					ifrm.setAttribute("allowtransparency","true");

					ifrm.style.width = "100%";
					ifrm.style.height = "465";
					window.addEventListener("message", receiveMessage, false);
				};
			</script>
			<div id="load" style="display:none;"></div>
			<iframe id="effectoFrame" src ="'.$hostString.'/login?callback=configureplug" width="100%" height="465">';
	}*/

	function echoUserScript() {
		global $hostString;

		$shortname = null;
		if (isset($_GET['shortname'])) {
			$shortname = $_GET['shortname'];
		}

		$globalPostID = null;
		if ($_GET['postID']) {
			$globalPostID = $_GET['postID'];
		}

		$postname = null;
		if ($_GET['postName']) {
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

	/* Show plugin in posts. */
	function echoEndUserPlugin($text) {
		$postId = get_the_ID();
		$getPostTitle = get_the_title();
		$wpSite = get_site_url();
		$effectoPreview = "false";
		$user_id = get_current_user_id();
		$effectoAuthor = get_the_author_meta('user_email', $user_id );
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

		if (is_single())
		{
			$effDate_published = get_the_date("l,F d,Y");
			if (is_preview()) {
				$effectoPreview = "true";
				$postId = 0;
			}
			$apiEmbedArray = str_replace("var effectoPostId=''","var effectoPostId='".$postId."'", $apiEmbedArray);
			$apiEmbedArray = str_replace("var effectoPreview=''","var effectoPreview='".$effectoPreview."'", $apiEmbedArray);
			$apiEmbedArray = str_replace("var effectoPagetitle = ''","var effectoPagetitle='".$getPostTitle."'", $apiEmbedArray);
			$apiEmbedArray = str_replace("var effectoPageurl = ''","var effectoPageurl='".$wpSite."?p=".$postId."'", $apiEmbedArray);
			$apiEmbedArray = str_replace("var effectoPublDate = ''","var effectoPublDate='".$effDate_published."'", $apiEmbedArray);
			$apiEmbedArray = str_replace("var effectoAuthorName = ''","var effectoAuthorName='".$effectoAuthor."'", $apiEmbedArray);
			return $text.$apiEmbedArray;
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

	function pluginUninstall() {
        global $wpdb;
        $table = $wpdb->prefix."effecto";
		delete_option("effecto_db_version");
		$wpdb->query("DROP TABLE IF EXISTS $table");
	}

	register_uninstall_hook( __FILE__, 'pluginUninstall' );
	
?>