<?php
/*
Plugin Name: MyEffecto
Plugin URI: www.myeffecto.com
Description: Getting customized and interactive feedback for your blog.
Version: 1.0.59
Author: MyEffecto
Author URI: www.myeffecto.com
*/
//$eff_trending_url=null;
require('DBFunctions.php');
require('PostConfiguration.php');

/* Add MyEffecto link to Setting Tab. */
add_action('admin_menu', 'myeffecto_admin_actions');
add_filter('the_content', 'echoEndUserPlugin');
add_action('wp_footer', 'echo_eff_plugin_homepage');

/* ------------------------------------------------------------- */
 $hostString="http://www.myeffecto.com";
 $myeJSLoc="js";
$myeCDN ="//cdn-files.appspot.com";

/* $hostString="http://localhost:8888";
 $myeCDN =$hostString;
 $myeJSLoc="p-js";*/
/* ------------------------------------------------------------- */
$embedCode = null;
$eff_ssl_host = "https://myeffecto.appspot.com";

$eff_settings_page = "eff_conf_nav";
$widget_page="";
$myeJson=null;

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
        $eff_trending_url=null;
        if (isset($_POST['url'])) {  //for trending widget
            $eff_trending_url=$_POST['url'];
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

        <form id="submitWidgetForm" action="" method="post" style="display:none;">
		<input name="url" value="" id="url" type="hidden"/>
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
				
					//refresh code
				
			}
		}
	 }else if(isset($eff_trending_url) && !empty($eff_trending_url)) {
             update_option("trending_url", $eff_trending_url);
             ?>
            <script type="text/javascript">
                    window.location= <?php echo "'" . $postURL . "&action=edit&plugin=success'"; ?>;
            </script>
                 <?php
         }
         else {
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
                                        //alert(rcvdMsg);
					var msg = rcvdMsg.split("#~#");
                                        //alert(msg);
					if (msg[0] == "save") {
						postIframeCode(msg[1]);
						jQuery(\'#load\').show();
					} else if(msg[0] == "loggedIn") {
						afterLoginSuccess();
					} else if (msg[0] == "error") {
						alert("Error occured");
					} else if (msg[0] == "pluginLoggedIn") {
						showButtonCode(shortname);
					} else if (msg[0] == "") { 
						showButtonCode(shortname);
					}
                                        else if (msg[0] == "validated") {
						jQuery(\'#load\').show();
					}else if (msg[0] == "saveWidget") {
                                                //alert(msg[1]);
                                                postWidgetForm(msg[1]);
                                                jQuery(\'#generate\').remove();
					}
                                        /*else if(msg[0] == "apiKey") {
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
                                
                                function postWidgetForm(json)
                                {   jQuery("#url").val(json);
                                    //alert("val"+jQuery("#url").val());
                                    jQuery(\'#submitWidgetForm\').submit();
                                }

				function showButtonCode(shortname) {
					jQuery(\'#generate\').remove();

					if (shortname === null) {
					    shortname="";
						jQuery(\'#effectoFrame\').after(jQuery(\'<center><h3><input type="button" id="generate"  value="Apply Emotion Set" style="font-size : 22px; padding-top : 7px; padding-bottom : 30px;" class="button-primary" /></h3></center>\'));
					}
                                        else if(shortname === "no"){return;}
                                        else {
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
		/* src ="'.$hostString.'/register?callback=confgEmoji&outside=true&postTitle="+postTitle */
		echo '	var ifrm= null;
				window.onload=function(){
                                        var url=location.href;
					ifrm = document.getElementById("effectoFrame");
                                        if(url.indexOf("true")>0){
                                            ifrm.setAttribute("src", "'.$hostString.'/register?wp=1&callback=config_trend&outside=true&postTitle="+postTitle);
                                            ifrm.onload=function(){ 
                                            ifrm.contentWindow.postMessage("init_effecto","'.$hostString.'"); 
                                            }
                                        }
                                        else{
                                            ifrm.setAttribute("src", "'.$hostString.'/register?callback=confgEmoji&outside=true&postTitle="+postTitle);
                                        }
					ifrm.setAttribute("frameborder","0");
					ifrm.setAttribute("allowtransparency","true");

					ifrm.style.width = "100%";
					ifrm.style.height = "500";
					window.addEventListener("message", receiveMessage, false);
				};
			</script>
			<div id="load" style="display:none;"></div>
			<iframe id="effectoFrame" width="100%" height="500"/>';
	}
	
	function eff_is_html($string) {
	  return preg_match("/<[^<]+>/",$string,$m) != 0;
	}


	function getEffectoDataJSON(){
		global $current_user;
		global $myeJson;
		if(!isset($myeJson)){
			$postId = get_the_ID();
			$getPostTitle = get_the_title();
			$wpSite = get_site_url();
			$postUrl = get_permalink($postId);
			$effectoPreview = "false";
			$effectoAuthor = effecto_get_author();
			$eff_category = effecto_get_category($postId);
			$eff_tags = "";
			if(function_exists('effecto_get_tags')){
				$eff_tags=effecto_get_tags($postId);
			}
			$apiPluginDetailsArray = getMyEffectoPluginDetails($postId);
			if ($apiPluginDetailsArray == null) {
				$apiPluginDetailsArray = getMyEffectoPluginDetails(0);
			}
			//$apiEmbedArray="";
			$p_shortname="";
			foreach($apiPluginDetailsArray as $detail) {
				//$apiEmbedArray = $detail -> embedCode;
				$p_shortname = $detail -> shortname;
			}

			$effDate_published = get_the_date("l,F d,Y");
			if (is_preview()) {
				$effectoPreview = "true";
				$postId = 0;
			}

			/* $wpSite."?p=".$postId */
			$getPostTitle = str_replace("'",'\"', $getPostTitle);
			$getPostTitle = strip_tags($getPostTitle);
			$eff_category = str_replace("'",'\"', $eff_category);
			$eff_category = strip_tags($eff_category);
			$eff_tags = str_replace("'",'\"', $eff_tags);
			$eff_tags = strip_tags($eff_tags);
			get_currentuserinfo();
			$eff_cur_loggedIn = is_user_logged_in();
			$eff_user_role = $current_user->user_login;
			$eff_user_email = $current_user->user_email;
			$eff_user_display = str_replace("'",'\"', $current_user->display_name);
			$eff_user_fname = str_replace("'",'\"', $current_user->user_firstname);
			$eff_user_lname = str_replace("'",'\"', $current_user->user_lastname);	
			$thumb_img = "";
			if ( function_exists('has_post_thumbnail') && has_post_thumbnail(get_the_ID()) ) {
				$timg = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()));
				$thumb_img = $timg[0];
			}
			$myeJson = '{"t_img":"'.$thumb_img.'","effecto_uniquename":"'.$p_shortname.'","effectoPostId":"'.$postId.'","effectoPreview": "'.$effectoPreview.'","effectoPagetitle":"'.$getPostTitle.'","effectoPageurl":"'.$postUrl.'", "effectoPublDate":"'.$effDate_published.'","effectoAuthorName":"'.$effectoAuthor.'","effectoTag":"'.$eff_tags.'","effectoCategory":"'.$eff_category.'","effUserInfo": {"isLoggedIn": "'.$eff_cur_loggedIn.'","loginAs": "'.$eff_user_role.'","email": "'.$eff_user_email.'","dpName": "'.$eff_user_display.'","fName": "'.$eff_user_fname.'","lName": "'.$eff_user_lname.'"}}';
		}
	
		return $myeJson;
	}
	/* Show plugin in posts. */
	function echoEndUserPlugin($text) {
		global $hostString;
		global $eff_ssl_host;
		global $myeCDN;
		global $myeJSLoc;

		$mye_plugin_visib = get_option('mye_plugin_visib');
		$eff_isOnPost = true;
		$eff_isOnPage = false;
		$eff_isOnCustom = false;
		$eff_isPreview = is_preview();

		$eff_height = "";
		if ($eff_isPreview) {
			eff_applyMinHeight();
		}
		
		if (isset($mye_plugin_visib) && $mye_plugin_visib) {
			$mye_plugin_visib = json_decode($mye_plugin_visib, true);

			if($mye_plugin_visib['isOnPost']){$eff_isOnPost = true;}else{$eff_isOnPost = false;}
			if($mye_plugin_visib['isOnPage']){$eff_isOnPage = true;}
			if($mye_plugin_visib['isOnCustom']){$eff_isOnCustom = true;}
		}

		$cur_post_typ = get_post_type(get_the_ID());
		$effisPageOrPost = $cur_post_typ==="post" || $cur_post_typ==="page";

		if (is_single() || is_page())
		{

			if ($effisPageOrPost) {
				if ($cur_post_typ==="post" && $eff_isOnPost) {
					$effisPageOrPost = true;
				} else if ($cur_post_typ==="page" && $eff_isOnPage) {
					$effisPageOrPost = true;
				} else {
					$effisPageOrPost = false;
				}
			} else {
				if ($eff_isOnCustom) {
					if (array_key_exists($cur_post_typ, $mye_plugin_visib['isOnCustomList'])) {
						$effisPageOrPost = true;
					} else {
						$effisPageOrPost = false;
					}
				} else {
					$effisPageOrPost = false;
				}
			}

			if($effisPageOrPost) {
				// wp_enqueue_script("wp-mye-load",$myeCDN."/".$myeJSLoc."/mye-wp-load.js",null,null,false);

				//User Info
				global $myeCDN;
				global $myeJSLoc;

				$myeJson = getEffectoDataJSON();
				$eff_json = "<div id='effecto_bar' V='1.8' style='text-align:center;".$eff_height."' data-json='".$myeJson."'></div>
							<script id='effectp-code' src='https://1-ps.googleusercontent.com/xk/L66fZog1l-dbbe1GxD7gjIXP94/s.cdn-files.appspot.com/cdn-files.appspot.com/js/mye-wp.js.pagespeed.jm.7QLAn0uD4Dg9RsZl1qc9.js' onerror='this.src=\"".$myeCDN."/".$myeJSLoc."/mye-wp.js\"' type='text/javascript' async='true'></script>";
                                
                               /* $trend=get_option("trending_url");
                                if(isset($trend) && !empty($trend)){
                                    $arr=  explode("#@#",$trend);
                                        if($arr[1]!="false"){
                                            $eff_json.="<img src='".plugins_url( 'loading.gif' , __FILE__ )."' title='Activity Feed' alt=''/><h2>Activity Feed</h2><iframe id='effWidget' src='".$trend."' style='width:100%;height:300px;border:none;'></iframe>";
                                        }
                                }*/

				return $text.$eff_json;
			}
			else{
				return $text;
			}
		} else {
			return $text;
		}
	}
	
	function echo_eff_plugin_homepage() {
		$mye_plugin_visib = get_option('mye_plugin_visib');
		$isOnHome = false;
		if (isset($mye_plugin_visib) && $mye_plugin_visib) {
			$mye_plugin_visib = json_decode($mye_plugin_visib, true);
			if($mye_plugin_visib['isOnHome']){$isOnHome = true;}
		}

		if ($isOnHome && is_front_page()) {
			$apiPluginDetailsArray = getMyEffectoPluginDetails(0);
			$p_shortname="";
			foreach($apiPluginDetailsArray as $detail) {
				$p_shortname = $detail -> shortname;
			}
			global $myeCDN;
			global $myeJSLoc;
			echo do_shortcode( '[effecto-bar]' );
			echo "<script>var eff_json={'effecto_uniquename':'".$p_shortname."'};</script>
			<script id='effectp-code' src='".$myeCDN."/".$myeJSLoc."/mye-wp.js' type='text/javascript' async='true'></script>";
		}
	}

	function getEffectoCustomTag(){
		$data_val=getEffectoDataJSON();
		return "<div id='effecto_cust_bar' data-json='".$data_val."' style='text-align:center;'></div>";
	}
	 function getEffectoTrendTag(){
		//$data_val=getEffectoDataJSON();
                $trendyFrame="";
         
                $trendy=get_option("trending_url");
				// error_log("trendy ".$trendy);
				if(isset($trendy) && !empty($trendy)){
					$trendyFrame="<iframe id='effWidget' src='".$trendy."' style='width:100%;height:300px;border:none;overflow: hidden;' scrolling='no'></iframe>";
                }
		return $trendyFrame;
	 } 
	
	function register_effectoTag(){
	   add_shortcode('effecto-bar', 'getEffectoCustomTag');
	   add_shortcode('effecto-trend', 'getEffectoTrendTag');
	}
	add_action( 'init', 'register_effectoTag');


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
		if (!isset($shortname)) {
			$shortname = "";
		}

		global $hostString;
		$args = array(
			'body' => array('action' => 'updateStatus', 'status' => 'Deactivated', 'sname' => $shortname, 'utm' => get_option( 'admin_email' ), 'site' => get_site_url()),
		);
		wp_remote_post($hostString.'/contentdetails', $args);
	}
	register_deactivation_hook( __FILE__, 'eff_pluginDeactivate');

	function eff_pluginUninstall() {
        global $wpdb;
        $table = $wpdb->prefix."effecto";
		delete_option("effecto_db_version");
		delete_option('mye_plugin_visib');
		$wpdb->query("DROP TABLE IF EXISTS $table");
	}
	register_uninstall_hook( __FILE__, 'eff_pluginUninstall' );
?>