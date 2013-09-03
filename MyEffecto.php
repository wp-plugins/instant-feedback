<?php
/*
Plugin Name: My Effecto
Plugin URI: www.myeffecto.com
Description: Getting customized and interactive feedback for your blog
Version: 1.0
Author URI: www.myeffecto.com
*/
	require('DBFunctions.php');
	require('PostConfiguration.php');

	/* Add MyEffecto link to Setting Tab. */
	add_action('admin_menu', 'myeffecto_admin_actions');

	add_filter( 'the_content', 'echoEndUserPlugin');
	$embedCode = null;

	/* Show plugin on Menu bar */
	function myeffecto_admin_actions() {
		add_options_page('MyEffecto', 'MyEffecto', 'manage_options', _FILE_, 'myeffecto_admin', null, '59.5');
	}

	wp_enqueue_script("jquery");

	function myeffecto_admin() {
		 $user_id = get_current_user_id();
		 $data=$_POST['dataToSend'];

		 $postID = $_GET['postID'];
		 $postName = $_GET['postName'];
		 $postURL = $_GET['postURL'];
	?>
		 <form id="submitForm" action="" method="post" style="display:none;"><input name="isToInsert" value="true" id="isToInsert" type="hidden"/><input name="dataToSend" id="dataToSend" type="hidden"/><input type='submit'/></form>
		 <form id="reloadForm" action="" method="post" style="display:none;"><input type='submit'/></form>
	<?php

		if(isset($data)) {
			$isCodeExist = getEmbedCodeByPostID($postID);
			if ($isCodeExist == null) {
				if (!isset($postID)) {
					$defaultEdit = $_GET['pluginType'];
					if (isset($defaultEdit) && $defaultEdit == "defaultEdit") {
						updateEmbedCode($data, 0);
						?>
							<script type="text/javascript">
						   <!--
						      	window.location= <?php echo "'" . $postURL . "&action=edit&plugin=success'"; ?>;
						   //-->
						   </script>
						<?php
						
						//header("Location:$postURL&action=edit&plugin=success");
					} else {
						insertInDb($user_id, null, $data, null);
						if (isset($postURL)) {
							?>
								<script type="text/javascript">
							   <!--
							      	window.location= <?php echo "'" . $postURL . "&action=edit&plugin=success'"; ?>;
							   //-->
							   </script>
							<?php
							//header("Location:$postURL&action=edit&plugin=success");
						} else {
							echo "<h1><center>Your emoticon set has been added on your posts successfully.</center></h1>";
						}
					}
				} else {
					insertInDb($user_id, null, $data, $postID);
					?>
						<script type="text/javascript">
					   <!--
					      	window.location= <?php echo "'" . $postURL . "&action=edit&plugin=success'"; ?>;
					   //-->
					   </script>
					<?php
					// header("Location:$postURL&action=edit&plugin=success");
				}
			} else {
				$addType = $_GET['pluginType'];
				if ($addType == "postEdit") {
					updateEmbedCode($data, $postID);
					?>
						<script type="text/javascript">
					   <!--
					      	window.location= <?php echo "'" . $postURL . "&action=edit&plugin=success'"; ?>;
					   //-->
					   </script>
					<?php
					// header("Location:$postURL&action=edit&plugin=success");
				}
			}
		 } else {
	?>
		<div class="wrap" style="overflow-x : hidden;">
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
				$table_name = $wpdb->prefix . "effecto";
				if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
					createEffectoTable();
				} else {
					$apiEmbedArray = getEmbedCodeByPostID(0);
					$embedCode=$apiEmbedArray;

					if (!isset($embedCode)) {
						$isFirstUser=true;
					} else {
						$myeffectoArray['userID']=$user_id ;
						//$myeffectoArray['apiKey']=$apiEmbedArray->apiKey;
						$myeffectoArray['embedCode']=$apiEmbedArray;
						update_option('myeffecto_apikeys'.'#@#'.$user_id, $apiEmbedArray);
					}
				}

				if ($isFirstUser) {
					echoFirstUserScript();
					return;
				}
			}

			if (isset($embedCode) && !isset($postURL)) {
				allSetCode($embedCode, null);
			} else {
				echoUserScript();
			}

	?>
		</div>
	<?php
		}
	}

	function echoFirstUserScript() {
		global $postName;
		echo '
			 <script type="text/javascript">
					var ifrm = null;
					window.onload=function(){
						ifrm = document.getElementById("effectoFrame");
						   ifrm.setAttribute("src", "http://www.myeffecto.com/loginForPlugin?callback=configureplug&postName='.$postName.'");
						   ifrm.setAttribute("frameborder","0");
						   ifrm.setAttribute("allowtransparency","true");

						   ifrm.style.width = "100%";
						   ifrm.style.height = "465";
						   window.addEventListener("message", receiveMessage, false);
					};
				</script>
				<iframe id="effectoFrame" src ="http://www.myeffecto.com/loginForPlugin?callback=configureplug&postName='.$postName.'" width="100%" height="465">';
	}

	function echoUserScript() {
		global $postName;
		echo '
				<script type="text/javascript">
					var ifrm= null;
					window.onload=function(){
						ifrm = document.getElementById("effectoFrame");
						   ifrm.setAttribute("src", "http://www.myeffecto.com/loginForPlugin?callback=configureplug&postName='.$postName.'");
						   ifrm.setAttribute("frameborder","0");
						   ifrm.setAttribute("allowtransparency","true");

						   ifrm.style.width = "100%";
						   ifrm.style.height = "465";
						   window.addEventListener("message", receiveMessage, false);
					};
				</script>
				<iframe id="effectoFrame" src ="http://www.myeffecto.com/loginForPlugin?callback=configureplug&postName='.$postName.'" width="100%" height="465"/>';
	}

	/* Simple string replace function */
	function replaceText ($text) {
		$text = str_replace('\"','', $text);
		return $text;
	}

	/* Show plugin in posts. */
	function echoEndUserPlugin($text) {
		$postId = get_the_ID();
		$apiEmbedArray = getEmbedCodeByPostID($postId);
		if ($apiEmbedArray == null) {
			$apiEmbedArray = getEmbedCodeByPostID(0);
		}

		if (strpos($_SERVER['REQUEST_URI'],'?p=') !== false) {
			$apiEmbedArray = str_replace("var effectoPostId=''","var effectoPostId='".$postId."'", $apiEmbedArray);
			$apiEmbedArray = str_replace("var effectoPreview=''","var effectoPreview='false'", $apiEmbedArray);
			$text =  $text.$apiEmbedArray;
			return $text;
		} else {
			return $text;
		}

	}

	function addAlert($pluginStatus) { 
		if (isset($pluginStatus)) {
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
?>

<script type="text/javascript">
	function save() {
		ifrm.contentWindow.postMessage("Save","http://www.myeffecto.com");
	}

	function receiveMessage(event) {
		var rcvdMsg = event.data;
		var msg = rcvdMsg.split("#~#");

		if (msg[0] == "save") {
			postIframeCode(msg[1]);
		} else if(msg[0] == "loggedIn") {
			afterLoginSuccess();
		} else if (msg[0] == "error") {
			alert("Error occured");
		} else if (msg[0] == "pluginLoggedIn") {
			showButtonCode();
		} /*else if(msg[0] == "apiKey") {
			addKey(msg[1]);
		}*/
	}

	function postIframeCode(rcvdMsg) {
		var dataToSend = { "insert":"true", "data" : rcvdMsg};

		jQuery("#dataToSend").val(rcvdMsg);
		jQuery('#submitForm').submit();
	}

	function showButtonCode() {
		jQuery('#generate').remove();
		jQuery('#effectoFrame').after(jQuery('<center><h3><input type="button" id="generate" onclick="save()" value="Apply Emotion Set" style="font-size : 22px; padding-top : 7px; padding-bottom : 30px;" class="button-primary" /></h3></center>'));
	}

	function afterLoginSuccess() {
		jQuery('#effectoFrame').parent().prepend(jQuery('<input type="button" id="generate" onclick="save()" value="Generate Plugin" class="button-primary"/>'));
		ifrm.setAttribute("src", "http://www.myeffecto.com/configureplug");
	}

	function addKey(key)
	{
		//var a = document.getElementById("kk").value;

		jQuery.ajax({
			type : 'get',
			url : "<?php get_site_url(); ?>/wp-content/plugins/changeVar.php?apiKey=" + key,
			success : function(data){
				//window.location.reload(true);
			},
			error : function(data){
				alert(data);
			}
		});
	}

</script>
