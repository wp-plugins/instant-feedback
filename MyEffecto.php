<?php
/*
Plugin Name: MyEffecto
Plugin URI: www.myeffecto.com
Description: Getting customized and interactive feedback for your blog.
Version: 2.2.5
Author: MyEffecto
Author URI: www.myeffecto.com
*/

require('DBFunctions.php');
require('PostConfiguration.php');

/* ------------------------------------------------------------- */
$hostString="http://www.myeffecto.com";
$myeJSLoc="js";
$myeCDN ="//cdn-files.appspot.com";

/*$hostString="http://localhost:8888";
$myeCDN =$hostString;
$myeJSLoc="p-js";*/
/* ------------------------------------------------------------- */

/* Add MyEffecto link to Setting Tab. */
add_action('admin_menu', 'myeffecto_admin_actions');

add_filter('the_content', 'echoEndUserPlugin');
add_action('wp_footer', 'echo_eff_plugin_homepage');

add_filter( 'plugin_action_links', 'ttt_wpmdr_add_action_plugin', 5, 10 );
function ttt_wpmdr_add_action_plugin( $actions, $plugin_file ) 
{
	static $plugin;
	if (!isset($plugin))
		$plugin = plugin_basename(__FILE__);
	if ($plugin == $plugin_file) {
		$settings = array('settings' => '<a href="'. admin_url( 'options-general.php?page=eff_conf_nav' ).'" >Settings</a>');
		$site_link = array('Report' => '<a href="http://www.myeffecto.com/support_mail?site='.urlencode(get_site_url()).'" target="_blank">Report Issue</a>');
		$actions = array_merge($settings, $actions);
		$actions = array_merge($site_link, $actions);
		
	}	
	return $actions;
}

register_activation_hook(__FILE__, 'my_plugin_activate');
add_action('admin_init', 'my_plugin_redirect');

function my_plugin_activate() {
	if(function_exists("wp_remote_get") && function_exists("get_site_url")){
		wp_remote_get("http://www.myeffecto.com/effecto?action=acDc&stat=activation&site=".get_site_url(), array('method' => 'GET'));
	}
    add_option('my_plugin_do_activation_redirect', true);
}

function my_plugin_redirect() {
    if (get_option('my_plugin_do_activation_redirect', false)) {
        delete_option('my_plugin_do_activation_redirect');
         wp_redirect("options-general.php?page=eff_conf_nav");
         //wp_redirect() does not exit automatically and should almost always be followed by exit.
         exit;
    }
}



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
				$isCodeExist = $detail -> shortname;
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
					
					?><script type="text/javascript">
						window.location= <?php echo "'" . $postURL . "&action=edit&plugin=success'"; ?>;
					</script><?php

					} else {
					
						insertInMyEffectoDb($user_id, null, $data, null, $eff_shortname);
						if (isset($postURL) && !empty($postURL)) {
							?>
								<script type="text/javascript">
									window.location= <?php echo "'" . $postURL . "&action=edit&plugin=success'"; ?>;
							   </script>
							<?php
						} else {
							// After Plugin Config
							exit( wp_redirect( admin_url( 'options-general.php?page=eff_conf_nav')));
						}
					}
			} else {
				insertInMyEffectoDb($user_id, null, $data, $postID, $eff_shortname);
				?>
					<script type="text/javascript">
				  
						window.location= <?php echo "'" . $postURL . "&action=edit&plugin=success'"; ?>;
				   
				   </script>
				<?php
			}
		} else {
			$addType = $_GET['pluginType'];
			if ($addType == "postAdd") {
					updateMyeffectoEmbedCode(null, $postID, $eff_shortname);
					?>
					<script type="text/javascript">
						window.location= <?php echo "'" . $postURL . "&action=edit&plugin=success'"; ?>;</script>
				<?php
			}else {

                $isToInsert = $_GET['isToInsert'];
                if(isset($isToInsert) && $isToInsert=="true")    {
                    insertInMyEffectoDb($user_id, null, $data, $postID, $eff_shortname);    
                    ?>
                    <script type="text/javascript">window.location= <?php echo "'" . $postURL . "&action=edit&plugin=success'"; ?>;</script><?php return;
                }
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
						$isFirstUser=false;
					} else {
						$myeffectoArray['userID']=$user_id ;
						//$myeffectoArray['apiKey']=$apiEmbedArray->apiKey;
						$myeffectoArray['embedCode']=$apiEmbedArray;
						//update_option('myeffecto_apikeys'.'#@#'.$user_id, $apiEmbedArray);
					}
				}

				$p_type=$_GET['pluginType'];
				if ($isFirstUser ||  $p_type=='defaultEdit' || $p_type=='postAdd' ) {
					echoUserScript();
					return;
				}
				else if($p_type=="editExist"){
					include 'editExisting.php';
					return;
				}
				else{
					allSetCode($embedCode, null);
				}
			}

		
	?>
		</div>
	<?php
		}
	}

	function configurationScript($shortname, $globalPostID, $title) {
	    global $hostString,$eff_settings_page;
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

				function rcvMyeMsg(event) {
					if(event.origin==="'.$hostString.'"){
						var rcvdMsg = event.data;
						var msg = rcvdMsg.split("#~#");	
						if(msg[0]==="setHt") {
							ifrm.style.height=msg[1];
							ifrm.height=msg[1];
						} else if (msg[0] === "save") {
							jQuery(\'#load\').show();
							postIframeCode(msg[1]);
						} else if(msg[0] === "loggedIn") {
							afterLoginSuccess();
						} else if (msg[0] === "error") {
							alert("Error occured");
						} else if (msg[0] === "pluginLoggedIn") {
							showButtonCode(shortname);
						} else if (msg[0] === "") { 
							showButtonCode(shortname);
						} else if (msg[0] === "validated") {
							jQuery(\'#load\').show();
						}else if (msg[0] === "saveWidget") {
                            postWidgetForm(msg[1]);
                            jQuery(\'#generate\').remove();
						}
					}                  
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
                    jQuery(\'.generate\').remove();
                    var efrm=jQuery(\'#effectoFrame\');
                    var init=efrm.attr("w-init")!="1";
                    if (shortname === "no") {
                     return;
                     }else {
                     	var doneStyle="font-size: 20px;margin-right: 15px;padding: 5px 25px;padding-bottom: 33px;line-height: 27px;";
                     	var canStyle="font-size: 16px;margin-right: 15px;padding: 5px 18px;padding-bottom: 33px;line-height: 28px;";
                     	var cancelLink="'.get_site_url().'/wp-admin/admin.php?page='.$eff_settings_page.'";

                     	if(init){
                        	efrm.before(jQuery(\'<h2 style="float:left;">Myeffecto Configure</h2>\'));
                        	efrm.attr("w-init","1"); 

                        	efrm.before(jQuery(\'<div><h3 id="before_button_bar" style="float:right;"></h3></div>\'));
                        	efrm.after(jQuery(\'<h3 id="after_button_bar" style="float:right;"></h3>\'));                        
                
                        }
                		
                		var beforBar=jQuery("#before_button_bar");
                		var aftrBar=jQuery("#after_button_bar");
              	
              			var c=false;
                		if(beforBar.find("a").length==0){c=true;
                			beforBar.append(jQuery(\'<a id="mye_cancel" href="\'+cancelLink+\'" style="\'+canStyle+\'" class="button conf-close">Cancel</a>\'));
                		}
                		if(aftrBar.find("a").length==0){c=true;
                			aftrBar.append(jQuery(\'<a id="mye_cancel" href="\'+cancelLink+\'" style="\'+canStyle+\'" class="button conf-close">Cancel</a>\'));
                		}
                		if(c){
                		jQuery(".conf-close").click(function(){jQuery(\'#load\').show();});	
                		}

                		var l=false;
                		if(beforBar.find("input").length==0){
                			l=true;
                			beforBar.append(jQuery(\'<input style="\'+doneStyle+\'" type="button" 2 value="Finish!" class="button-primary generate" />\'));     	
                		}
                		if(aftrBar.find("input").length==0){
                			l=true;
                			aftrBar.append(jQuery(\'<input style="\'+doneStyle+\'" type="button" 2 value="Finish!" class="button-primary generate" />\'));     	
                		}

                     	if(l){
                     	jQuery(".generate").click(function(){save(shortname);});	
                     	}
                     	
                    }
                }

				function afterLoginSuccess() {
					ifrm.setAttribute("src", "'.$hostString.'/confgEmoji?l='.get_option('siteurl').'outside=true&postTitle=" + postTitle);
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
                    var url=location.href;
					ifrm = document.getElementById("effectoFrame");
                                        if(url.indexOf("true")>0){
                                            ifrm.setAttribute("src", "'.$hostString.'/auth?action=extAcess&from=wp&callback=config_trend");
                                            ifrm.onload=function(){ 
                                            ifrm.contentWindow.postMessage("init_effecto","'.$hostString.'"); 
                                            }
                                        }
                                        else{
                                            ifrm.setAttribute("src", "'.$hostString.'/auth?action=extAcess&from=wp&sname='.$shortname.'&l='.get_option('siteurl').'&email='.get_option('admin_email').'&uname='.get_option('blogname').'");
                                        }
					window.addEventListener("message", rcvMyeMsg, false);
				};
			</script>
			<div id="load" style="display:none;"></div>
			<iframe id="effectoFrame" width="100%" height="500"  seamless scrolling="no" frameborder="0" allowtransparency="true"/>';
	}
	
	function eff_is_html($string) {
	  return preg_match("/<[^<]+>/",$string,$m) != 0;
	}

	$this_page_shortname="";
	function getThisPageShortName(){
		global $this_page_shortname;
		if(empty($this_page_shortname)){
			$postId = get_the_ID();
			$apiPluginDetailsArray = getMyEffectoPluginDetails($postId);

			if ($apiPluginDetailsArray == null) {
				$apiPluginDetailsArray = getMyEffectoPluginDetails(0);
			}
			$p_shortname="";
			foreach($apiPluginDetailsArray as $detail) {
				$p_shortname = $detail -> shortname;
			}
			$p_shortname=trim($p_shortname);
			$this_page_shortname=$p_shortname;
		}
		return $this_page_shortname;
	}

	function getEffectoDataJSON($atts){
		global $current_user;
		global $myeJson;
		if(!isset($myeJson) || isset($att)){
			$getPostTitle = get_the_title();
			if(isset($atts) && $atts['id']!='0'){
				$postId = $atts['id'];
			}else{
				$postId = get_the_ID();	
			}

			$wpSite = get_site_url();
			$postUrl = get_permalink($postId);
			$effectoPreview = "false";
			$effectoAuthor = effecto_get_author();
			$eff_category = effecto_get_category($postId);
			$eff_tags = "";
			if(function_exists('effecto_get_tags')){
				$eff_tags=effecto_get_tags($postId);
			}
			
			$p_shortname=getThisPageShortName();

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
			
			$myeJson = '{"t_img":"'.$thumb_img.'","effecto_uniquename":"'.$p_shortname.'","effectoPostId":"'.$postId.'","effectoPreview":"'.$effectoPreview.'","effectoPagetitle":"'.$getPostTitle.'", "effectoPublDate":"'.$effDate_published.'","effectoAuthorName":"'.$effectoAuthor.'","effectoTag":"'.$eff_tags.'","effectoCategory":"'.$eff_category.'","effUserInfo": {"isLoggedIn": "'.$eff_cur_loggedIn.'","loginAs": "'.$eff_user_role.'","email": "'.$eff_user_email.'","dpName": "'.$eff_user_display.'","fName": "'.$eff_user_fname.'","lName": "'.$eff_user_lname.'"}}';
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
		$eff_loadtype = "";
		$eff_height = "";
		
		if ($mye_plugin_visib!=null && isset($mye_plugin_visib)) {
			$mye_plugin_visib = json_decode($mye_plugin_visib, true);
			if($mye_plugin_visib['isOnPost']){$eff_isOnPost = true;}else{$eff_isOnPost = false;}
			if($mye_plugin_visib['isOnPage']){$eff_isOnPage = true;}
			if($mye_plugin_visib['isOnCustom']){$eff_isOnCustom = true;}
			if($mye_plugin_visib['mye_load_on']){$eff_loadtype=$mye_plugin_visib['mye_load_on'];}
		}

		$cur_post_typ = get_post_type(get_the_ID());
		$effisPageOrPost = $cur_post_typ==="post" || $cur_post_typ==="page";
		if (is_single() || is_page())
		{	
			echo "<span mye_instal='1' typ='". $cur_post_typ."'></span>";
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
					$cust_ptype=$mye_plugin_visib['isOnCustomList'];
					if (array_key_exists($cur_post_typ, $mye_plugin_visib['isOnCustomList'])) {
						if($cust_ptype[$cur_post_typ]){
							$effisPageOrPost = true;	
						}
						else{
							$effisPageOrPost = false;
						}
					} else {
						$effisPageOrPost = false;
					}
				} else {
					$effisPageOrPost = false;
				}
			}

			if($effisPageOrPost) {
				//User Info
				global $myeCDN;
				global $myeJSLoc;
				
				$p_shortname=getThisPageShortName();
			if(isset($p_shortname) && !empty($p_shortname)){
					$myeJson=getEffectoDataJSON(null);
					$eff_json="<div id='effecto_bar' V='2.2.5' style='text-align:center;".$eff_height."' data-json='".$myeJson."'></div>";
					$eff_json=$eff_json.getMyeScript($eff_loadtype);
				}
				else{
					$eff_json=$eff_json."<div id='effecto_bar' V='2.2.5'></div>";
				}

				return $text.$eff_json;
			}
			else{
				return $text;
			}
		} else {
			return $text;
		}
	}
	
	function getMyeScript($eff_loadtype){
		$eff_json="";
		$pageSpeed_script="//1-ps.googleusercontent.com/xk/L66fZog1l-dbbe1GxD7gjIXP94/s.cdn-files.appspot.com/cdn-files.appspot.com/js/mye-wp.js.pagespeed.jm.7QLAn0uD4Dg9RsZl1qc9.js";
		if($eff_loadtype=="dom"){
			$eff_json=$eff_json.'<script type="text/javascript">(function(){
					var eMeth = window.addEventListener ? "addEventListener" : "attachEvent";
					var loadEv = eMeth == "attachEvent" ? "onload" : "DOMContentLoaded";
					window[eMeth](loadEv,function(){ var s=document.createElement("script");s.async = "true";
					s.type = "text/javascript";';
			$eff_json=$eff_json."s.src='".$pageSpeed_script."'; s.onerror='this.src=\"".$myeCDN."/".$myeJSLoc."/mye-wp.js\"';";
			$eff_json=$eff_json.'var a=document.getElementsByTagName("head")[0] || document.getElementsByTagName("body")[0];
				a.appendChild(s);
				},false);
			})()</script>';
		}else if($eff_loadtype=="async"){
			$eff_json=$eff_json."<script type='text/javascript'>window.onload=function(){var s=document.createElement('script');
			s.type='text/javascript';s.src ='".$pageSpeed_script."';";
			$eff_json=$eff_json."s.onerror='this.src=\"".$myeCDN."/".$myeJSLoc."/mye-wp.js\"';";
			$eff_json=$eff_json.'var a=document.getElementsByTagName("head")[0] || document.getElementsByTagName("body")[0];a.appendChild(s);
			}</script>';
		}
		else{
			$eff_json=$eff_json."<script id='effecto-code' src='".$pageSpeed_script."' type='text/javascript' async='true'></script>";
		}

		return $eff_json;
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
			echo do_shortcode( '[effecto-bar id="home"]' );
		}
	}

	function getEffectoCustomTag($atts){
		$atts = shortcode_atts(array('id' => '0'), $atts, 'effecto-bar' );
		$fetchScript=false;
		$div_identify="id='effecto_cust_bar'";
		$atts['id']=trim($atts['id']);
		if($atts['id']!='0'){
			$fetchScript=true;
			$div_identify="id='effecto_bar'";
		}

		$data_val=getEffectoDataJSON($atts);
		$mye_plugin_visib = get_option('mye_plugin_visib');
		$eff_isOnPost=true;
		$eff_isOnCustom=false;
		$eff_loadtype="";

		if ($mye_plugin_visib!=null && isset($mye_plugin_visib)) {
			$mye_plugin_visib = json_decode($mye_plugin_visib, true);
			if($mye_plugin_visib['isOnPost']){$eff_isOnPost = true;}else{$eff_isOnPost = false;}
			
			if($mye_plugin_visib['isOnCustom']){$eff_isOnCustom = true;}
			if($mye_plugin_visib['mye_load_on']){$eff_loadtype=$mye_plugin_visib['mye_load_on'];}
		}
		$p_html="<div ".$div_identify." data-json='".$data_val."' style='text-align:center;' att='".$atts['id']."' ></div>";
		if($fetchScript || $eff_isOnPost==false || ($eff_isOnPost==false && $eff_isOnCustom==false)){
			$p_html=$p_html.getMyeScript($eff_loadtype);
		}
		return $p_html;
	}
	 function getEffectoTrendTag(){
		//$data_val=getEffectoDataJSON();
                $trendyFrame="";
         
                $trendy=get_option("trending_url");
				if(isset($trendy) && !empty($trendy)){
					$trendyFrame="<iframe id='effWidget' src='".$trendy."' style='width:100%;height:300px;border:none;overflow: hidden;' scrolling='no'></iframe>";
                }
		return $trendyFrame;
	 } 
	
	function register_effectoTag(){
	   add_shortcode('effecto-bar', 'getEffectoCustomTag');
	  // add_shortcode('effecto-trend', 'getEffectoTrendTag');
	}
	add_action( 'init', 'register_effectoTag');


	/* Simple string replace function */
	function replaceText ($text) {
		$text = str_replace('\"','', $text);
		return $text;
	}

	function addAlert($pluginStatus) {
		if (isset($pluginStatus) && !empty($pluginStatus)) {
?><script type="text/javascript">
				$j = jQuery;
				$j().ready(function() {
					$j('.wrap > h2').parent().prev().after('<div class="update-nag"><h3>Congrats! You have successfully configured New Emotion-Set for this post.</h3></div>');
				});
			</script><?php
		
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
		delete_option('mye_load_on');
		$wpdb->query("DROP TABLE IF EXISTS $table");
	}
	register_uninstall_hook( __FILE__, 'eff_pluginUninstall' );
?>