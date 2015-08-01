<?php

	add_action( 'add_meta_boxes', 'effectoBox' );  

	function effectoBox() {
		if (isset($mye_plugin_visib) && $mye_plugin_visib) {
			$mye_plugin_visib = json_decode($mye_plugin_visib, true);

			if($mye_plugin_visib['isOnPost']){
				add_meta_box( 'effecto_meta_box', 'MyEffecto Configuration', 'showEffectoBox', 'post', 'normal', 'core' );
			} else {
				$isOnPost="";
				return;
			}
			if($mye_plugin_visib['isOnPage']){
				add_meta_box( 'effecto_meta_box', 'MyEffecto Configuration', 'showEffectoBox', 'page', 'normal', 'core' ); 
			}
		} else {
			add_meta_box( 'effecto_meta_box', 'MyEffecto Configuration', 'showEffectoBox', 'post', 'normal', 'core' );
		}
	}
	

	$p_shortname = null;
	function showEffectoBox() {
		global $hostString, $eff_settings_page;
		echo "<script>
				jQuery(function($){
					$('#effecto_meta_box').addClass('closed');
				});
			</script>";

		$pluginStatus = $_GET["plugin"];
		if ($pluginStatus == 'success') {
			addAlert($pluginStatus);
		}

		$getPostID = get_the_ID();
		if (!isset($getPostID) || empty($getPostID)) {
			$getPostID = $_GET['post_id'];
		}

		$getPostTitle = get_the_title();
		$wpSite = get_site_url();
		$effDate_published = get_the_date("l,F d,Y");
		//$getPostTitle = substr($getPostTitle, 0, 10);

		$postUrl=$_SERVER['REQUEST_URI'];
		$postUrl = str_replace('post-new.php','post.php', $postUrl);
		$p_shortname =null;
		$eff_details = getMyEffectoPluginDetails($getPostID);
		foreach($eff_details as $detail) {
			$p_shortname = $detail -> shortname;
		}
		/* Check if there is plugin for current post. */
		if (!isset($p_shortname) || empty($p_shortname)) {
			/* If not found, check for AllPost code. */
			//$allPostCode = getMyeffectoEmbedCodeByPostID(0);
			$p_shortname =null;
			$eff_details = getMyEffectoPluginDetails(0);
			foreach($eff_details as $detail) {
				$p_shortname = $detail -> shortname;
			}
			if (isset($p_shortname) && !empty($p_shortname)) {
				echo '<h3><center>This Post Show Default Emotion-set (Check on Post Preview)</center> </h3>
					<div >Note : In post-preview most of the plugin functionality are disabled  <br>(click limit, share, recommendation)</div><br>'.$eff_json;
			
			} else {
				echo '<h2>
						<center>
							Please configure Myeffecto to view emotion-set below your post
						</center>
					</h2>
					<a class="button" href="'.get_site_url().'/wp-admin/admin.php?page='.$eff_settings_page.'&postName='.$wpSite.'&pluginType=defaultAdd&postURL='.$_SERVER['REQUEST_URI'].'?post_id='.$getPostID.'">Add a default emotion set </a><br><br>
					';
			}
		
		} else {	
			echo '<h2><center>Emotion-Set Configured only for this post</center></h2>'.$eff_json;
		
		}
		echo '<a class="effectoConfig button" href="'.get_site_url().'/wp-admin/admin.php?page='.$eff_settings_page.'&postID='.$getPostID.'&postName='.$wpSite.'&shortname='.$p_shortname.'&pluginType=postAdd&postURL='.urlencode($postUrl).'?post='.$getPostID.'">Confiure New Plugin For this Post</a>';
			/*echo '<a id="mye_disable"class="button">Disable myeffecto for this post</a> <span style="line-height: 28px;padding: 0px 8px;">or</span>
				<a class="effectoConfig button" href="'.get_site_url().'/wp-admin/admin.php?page='.$eff_settings_page.'&postID='.$getPostID.'&postName='.$wpSite.'&shortname='.$p_shortname.'&pluginType=postAdd&postURL='.urlencode($postUrl).'?post='.$getPostID.'">Confiure New Plugin For this Post</a>
				';*/
		/*	echo "<script>
				jQuery('#mye_disable').click(function(){
					alert('clicked');
					var data = {'action': 'mye_post_disable','post_id':'".$getPostID."'};
					jQuery.post(ajaxurl, data);
				});
				</script>";*/
		
	}

	/*function mye_post_disable_action() {
		error_log("Post Disabled yo");
		$postId = $_POST['post_id'];
		if(isset($postId)){
			$eff_details = getMyEffectoPluginDetails($postId);
			foreach($eff_details as $detail) {
				$post_shortname = $detail -> shortname;
			}
			if(isset($post_shortname) && !empty($post_shortname)){
				error_log("update shortname id : "+$postID);
				updateMyeffectoEmbedCode(null, $postID, "no");
			}
			else{
				error_log("insert shortname");
				insertInMyEffectoDb("1", null, null, $postID, "no");
			}
		}
		wp_die(); 
	}
	add_action( 'wp_ajax_mye_post_disable', 'mye_post_disable_action' );
*/
	function effecto_get_category($postId) {
		$categories = get_the_category($postId);
		$eff_category = "";
		if($categories){
			foreach($categories as $category) {
				$eff_category .= $category->name . ",";
			}
		}
		
		return $eff_category;
	}
	
	function effecto_get_tags($postId) {
		$effectoposttags = wp_get_post_tags($postId);
		$eff_tags = "";
		if($effectoposttags){
			foreach($effectoposttags as $effposttag) {
				$eff_tags .= $effposttag->name . ",";
			}
		}
		
		return $eff_tags;
	}
	
	function effecto_get_author() {
		$user_id = get_current_user_id();
		return get_the_author_meta('user_email', $user_id );
	}

	function showEffModal() {
		echo '<div id="effecto-confirm" title="Change emotion Set?" style="display : none;">
				<p><span class="" style="float: left; margin: 0 7px 20px 0;">Changing your set will erase your current emotion set data. <br/><br/> Do you want to continue?</span></p>
			</div>

			<script type="text/javascript">
				window.onload=function() {
					jQuery(".effectoConfig").click(function(e) {
						e.preventDefault();
						var targetUrl = jQuery(this).attr("effectohref");
						jQuery( "#effecto-confirm" ).dialog({
							resizable: false,
							height:220,
							modal: false,
							buttons: {
								Ok: function() {
								   window.location.href = targetUrl;
								  //return true;
								},
								Cancel: function() {
								  jQuery( this ).dialog( "close" );
								}
							}
						});
						return false;
					});
				};
			</script>';
	}

	function updateEff_title() {
		global $hostString;
		$eff_id = get_the_ID();
		$wpress_post = get_post($eff_id);
		$wpress_title = $wpress_post->post_title;
		/* $shortname = getShortnameByPostID($eff_id);
		if (!isset($shortname)) {
			$shortname = getShortnameByPostID(0);
		} */
		if (isset($eff_id) && !empty($eff_id)) {
			$args = array(
				'body' => array('action' => 'updateContentTitle', 'title' => $wpress_title, 'post_id' => $eff_id),
			);
			wp_remote_post($hostString.'/contentdetails', $args);
		}
	}

/*	function createDefaultPlugin(){
		$apiPluginDetailsArray = getMyEffectoPluginDetails(0);
			$p_shortname="";
			foreach($apiPluginDetailsArray as $detail) {
				$p_shortname = $detail -> shortname;
			}

		if($p_shortname==null || !isset($p_shortname)){

			global $hostString;
			$args = array(
				'body' => array('action' => 'defaultContent', 'email' => get_option( 'admin_email' ), 'site' => get_site_url()),
			);
			$resp = wp_remote_post($hostString.'/contentdetails', $args);
			if ( is_wp_error( $resp ) ) {
				echo print_r($resp);
			}
			else{
				$eff_shortname= $resp["body"];
				if(isset($eff_shortname) && !empty($eff_shortname)){
					$eff_shortname=trim($eff_shortname);
					insertInMyEffectoDb('1', null, "<div>", null, $eff_shortname);		
				}		
			
			}
		}
	}*/

	function allSetCode($allPostCode, $getPostTitle) {
		global $hostString, $eff_settings_page;

		$shortname = "";
		$eff_details = getMyEffectoPluginDetails(0);
		foreach($eff_details as $detail) {
			$shortname=$detail -> shortname;
		}

		$ad_email=urlencode (get_option('admin_email'));
		$b_url=urlencode (get_option('siteurl'));

		$prev_ifrm_url=$hostString."/ep?ty=preview&wadm=1&email=".$ad_email."&l=".$b_url."&s=";
		if($shortname==null || !isset($shortname)){
			echo "<script type='text/javascript'>
			var eMeth=window.addEventListener ? 'addEventListener':'attachEvent';
			var msgEv = eMeth == 'attachEvent' ? 'onmessage' : 'message';var detect = window[eMeth];
			 detect(msgEv,mye_logHandle,false);
			 function mye_logHandle(e){
			 	 var m = e.data;
			 	 if(e.origin=='".$hostString."' && m.indexOf('mye_log')>-1){
			 	 	m=m.split('#');
			 	 	jQuery('#load').css('display','');
			 	 	var h=jQuery('#mye_editEmo').attr('href'); 
			 	 	h=h+m[1]; jQuery('#mye_editEmo').attr('href',h);
			 	 	var data = {'action': 'mye_sname_store','s':m[1]};
			 	 	jQuery.post(ajaxurl, data);
			 	 }
			 }
			</script>";
	    }
		
		
		$eff_json ="<div id='effecto_bar'style='text-align:center;max-height:175px;position:;'>";
		
		$eff_json = $eff_json."<div id='wp_mye_preview'><div id='load'></div><script>function delLoad(){jQuery('#load').css('display','none');}</script>
				<iframe id='mye_prev_frame' onload='delLoad();' src='".$prev_ifrm_url.$shortname."' width='100%' frameborder='0' scrolling='no' style='min-height:175px;width: 100%; border: 0px; overflow: hidden; clear: both; margin: 0px; background: transparent;'></iframe></div>";	
		
		
		
		$eff_json = $eff_json."</div>";
		$mye_plugin_visib = get_option('mye_plugin_visib');
		$eff_isJsonPresent = false;
		$eff_isOnPost = "checked";
		$eff_Load="checked";
		$eff_dom_load="";
		$eff_asyncLoad="";
		$eff_isOnPage = "";
		$eff_isOnHome = "";
		$eff_isCustom = "";
		$eff_custom_list = "";
		$eff_shCode_style = "display:none;";
		$eff_should_be_disabled = "style='display:none'";
		// print_r($mye_plugin_visib);
		$eff_custom_post_html = "";
		if (isset($mye_plugin_visib) && $mye_plugin_visib != null) {
			$eff_isJsonPresent = true;
			$mye_plugin_visib = json_decode($mye_plugin_visib, true);
			
			if($mye_plugin_visib['load_on']){
				if($mye_plugin_visib['load_on']=="async"){
					$eff_asyncLoad="checked"; $eff_Load=""; $eff_dom_load="";
				}
				else if($mye_plugin_visib['load_on']=="dom"){
					$eff_asyncLoad="";$eff_Load="";$eff_dom_load="checked";
				}
			}
			if($mye_plugin_visib['isOnPost']){$eff_isOnPost = "checked";}else{$eff_isOnPost="";}
			if($mye_plugin_visib['isOnPage']){$eff_isOnPage = "checked";}
			if($mye_plugin_visib['isOnHome']){$eff_isOnHome = "checked";$eff_shCode_style="";}
			if($mye_plugin_visib['isOnCustom']){$eff_isCustom = "checked";$eff_should_be_disabled="";}

		}
		$eff_cstm_args = array(
		   'public'   => true,
		   '_builtin' => false
		);
		
		$eff_output = 'objects'; // names or objects
		$eff_custom_post_html_first = "<br /><span id='eff_customPostList' ".$eff_should_be_disabled.">";
		$eff_custom_post_html = $eff_custom_post_html_first;
		$post_types = get_post_types( $eff_cstm_args, $eff_output );

		foreach ( $post_types  as $post_type ) {
			$eff_cName = $post_type->name;
			$checked = "";
			
			if ($eff_isJsonPresent && !is_null($mye_plugin_visib['isOnCustomList']) &&array_key_exists($eff_cName, $mye_plugin_visib['isOnCustomList'])) {
				$checked = "checked";
			}
			
			$eff_custom_post_html .= '<input type="checkbox" c-name="'.$eff_cName.'" '.$checked.' class="eff_customPostList"  />'.$post_type->label.'&nbsp;&nbsp;';
		}
		$eff_custom_post_html .= "</span>";
		/*<span style="font-size:15px;padding:0px 10px;"> | </span>*/
			
		echo '<h2><center>Your Default Emotion-Set (Preview Mode)</center></h2>'.$eff_json;
		echo '<h2><style>.mye_btn{font-weight:bold;padding-top: 5px !important;padding-bottom: 31px !important;}</style>
				<center>
					<a style="margin-bottom:-12px !important; " class="effectoConfig button-primary mye_btn" href="'.get_site_url().'/wp-admin/admin.php?page='.$eff_settings_page.'&postName='.$getPostTitle.'&pluginType=defaultEdit&postURL='.$_SERVER['REQUEST_URI'].'&shortname='.$shortname.'" title="Configure New Plugin for your blog">Create New</a>
					<span style="font-size:15px;padding:0px 10px;">OR</span> 
					
					<a id="mye_editEmo" class="effectoConfig button mye_btn" href="'.get_site_url().'/wp-admin/admin.php?page='.$eff_settings_page.'&pluginType=editExist&sname='.$shortname.'" title="Edit/Update existing default Emotion-Set">Edit</a>
					
					<span style="font-size:15px;padding:0px 10px;"> | </span>
					<a class="effectoConfig mye_btn" href="'.$hostString.'/dashboard-overview" target="_blank" title="Myeffecto Dashboard">Dashboard</a>
				</center>
			</h2>
			<hr style="border-color: #B3B3B3;">
			<h2 align="center">More Settings</h2>
				<div style="margin-top:5px;"><style>.m_hp{cursor:pointer;margin-left:5px;} mye_chk{margin-right:8px;} .mye_fset > span{margin-right:35px;} .mye_fset{border: 1px solid #DBDBDB;padding: 15px;} .mye_leg{font-weight:600;width:auto;font-size:15px;}</style>
					<form>
						<fieldset class="mye_fset" style="margin-bottom:20px">
							<legend class="mye_leg">Show plugin on</legend>
							<span><input class="mye_chk" type="checkbox" id="posts" name="postType" '.$eff_isOnPost.' />Posts</span>
							<span><input class="mye_chk" type="checkbox" id="pages" name="postType" '.$eff_isOnPage.' />Pages/ Articles</span>
							<span><input class="mye_chk" type="checkbox" id="home" name="postType" '.$eff_isOnHome.' />Home Page</span>
							<span><input class="mye_chk" type="checkbox" id="custom" name="postType" '.$eff_isCustom.' />Custom Posts</span>
						</fieldset>
						<fieldset class="mye_fset">
							<legend class="mye_leg">Plugin Performance</legend>
							<span><input class="mye_chk m_lod" type="radio" value="sync" name="p_load" '.$eff_Load.' />Fast<a onclick="alert(\'Note : Loads plugin along with post/page.\nWith minimum delay in page load.\')" class="m_hp">(?)</a></span>
							<span><input class="mye_chk m_lod" type="radio" value="dom" name="p_load" '.$eff_dom_load.' />Medium<a onclick="alert(\'Note : Loads plugin after HTML content in page has loaded. With partial delay in plugin load.\')" class="m_hp">(?)</a></span>
							<span><input class="mye_chk m_lod" type="radio" value="async" name="p_load" '.$eff_asyncLoad.' />Slow<a onclick="alert(\'Note : Enabling this option gives priority to page/post load.\ni.e loads plugin after page is loaded\')" class="m_hp">(?)</a></span>
						</fieldset>
					</form>
						'.$eff_custom_post_html.'
					<center>
					<a href="#eff_msg" style="font-size: 15px;margin-top:10px;cursor:pointer;" class="button-primary" id="eff_visib">Save Settings</a>
					<p id="eff_msg" style="display:none;font-size: 14px;"></p>
					<p id="eff_shCode" style="'.$eff_shCode_style.'">Copy <span style="font-size: 18px;background-color: #fff;padding: 6px;color: rgb(127, 128, 124);">[effecto-bar]</span> shortcode and paste in homepage where required</p>
					</center>					
			</div>';
		?>
			<script type="text/javascript" >
			jQuery("#eff_visib").click(function() {
					var eff_isPost = jQuery("#posts").is(":checked");
					var eff_isPage = jQuery("#pages").is(":checked");
					var eff_isHome = jQuery("#home").is(":checked");
					var eff_isCustom = jQuery("#custom").is(":checked");
					var lod_on=jQuery(".m_lod:checked").val();
					var eff_custom_list = {};
					if (eff_isCustom) {
						jQuery("input[class=eff_customPostList]:checked").each(function() {
							eff_custom_list[jQuery(this).attr('c-name')] = true;
						});
					}
					//alert(JSON.stringify(eff_custom_list));
					
					eff_custom_list = JSON.stringify(eff_custom_list);
					
					var eff_msg_ele = jQuery("#eff_msg");
					// console.log(eff_isPost + ", " + eff_isPage);

					eff_msg_ele.show();
					eff_msg_ele.html("Saving......");
					var data = {'action': 'mye_update_view',
						'isPost': eff_isPost,
						'isPage': eff_isPage,
						'isHome': eff_isHome,
						'isCustom': eff_isCustom,
						'eff_custom_list': eff_custom_list,
						'load_on':lod_on,
					};

					// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
					jQuery.post(ajaxurl, data, function(response) {
						eff_msg_ele.html("Settings Saved");
						if (eff_isHome) {jQuery("#eff_shCode").show();} else {jQuery("#eff_shCode").hide();}
					});
				});
				
				jQuery("#custom").click(function() {
					if (jQuery(this).is(":checked")) {
						jQuery("#eff_customPostList").show();
					} else {
						jQuery("#eff_customPostList").hide();
					}
				});
			</script>
		<?php
	}


	add_action( 'wp_ajax_mye_sname_store', 'mye_sname_store' );
	function mye_sname_store() {
		$eff_shortname = $_POST['s'];
		if(isset($eff_shortname) && !empty($eff_shortname)){
			$eff_shortname=trim($eff_shortname);
			insertInMyEffectoDb('1', null, "<div>", null, $eff_shortname);		
		}
		wp_die(); // this is required to terminate immediately and return a proper response
	}


	// add_action( 'save_post', 'updateEff_title' );	
	add_action( 'wp_ajax_mye_update_view', 'mye_visibUpdt_callback' );
	function mye_visibUpdt_callback() {
		$eff_isOnPost = $_POST['isPost'];
		$eff_isOnPage = $_POST['isPage'];
		$eff_isOnHome = $_POST['isHome'];
		$eff_isCustom = $_POST['isCustom'];
		$eff_custom_list = $_POST['eff_custom_list'];
		$load_on=$_POST['load_on'];
		
		$escapers = array("\\");
		$replacements = array("");
		$eff_custom_list = str_replace($escapers, $replacements, $eff_custom_list);

		update_option('mye_plugin_visib', '{"load_on":"'.$load_on.'","isOnPost":'.$eff_isOnPost.', "isOnPage":'.$eff_isOnPage.', "isOnHome":'.$eff_isOnHome.', "isOnCustom":'.$eff_isCustom.', "isOnCustomList":'.$eff_custom_list.'}');

		wp_die(); // this is required to terminate immediately and return a proper response
	}
?>