<?php
	add_action( 'add_meta_boxes', 'effectoBox' );  

	function effectoBox() {
		if (isset($mye_plugin_visib) && $mye_plugin_visib) {
			$mye_plugin_visib = json_decode($mye_plugin_visib, true);

			if($mye_plugin_visib['isOnPost']){
				add_meta_box( 'effecto_meta_box', 'MyEffecto Configuration (Open for more options)', 'showEffectoBox', 'post', 'normal', 'core' );
			} else {
				$isOnPost="";
				return;
			}
			if($mye_plugin_visib['isOnPage']){
				add_meta_box( 'effecto_meta_box', 'MyEffecto Configuration (Open for more options)', 'showEffectoBox', 'page', 'normal', 'core' ); 
			}
		} else {
			add_meta_box( 'effecto_meta_box', 'MyEffecto Configuration (Open for more options)', 'showEffectoBox', 'post', 'normal', 'core' );
		}
	}
	
	function eff_applyMinHeight() {
		echo "<style>
				#effecto_bar{
					min-height:200px;
				}
				#effecto_bar > iframe {
					min-height:200px;
				}
			</style>";
	}

	$p_shortname = null;
	function showEffectoBox() {
		global $hostString, $eff_settings_page;
		echo "<script>
				jQuery(function($){
					$('#effecto_meta_box').addClass('closed');
				});
			</script>";

		eff_applyMinHeight();
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

		$postCode = null;
		$postUrl=$_SERVER['REQUEST_URI'];
		$postUrl = str_replace('post-new.php','post.php', $postUrl);

		$eff_details = getMyEffectoPluginDetails($getPostID);
		foreach($eff_details as $detail) {
			$postCode = $detail -> embedCode;
			$p_shortname = $detail -> shortname;
		}
		/* Check if there is plugin for current post. */
		if (!isset($postCode) || empty($postCode)) {
			/* If not found, check for AllPost code. */
			//$allPostCode = getMyeffectoEmbedCodeByPostID(0);

			$eff_details = getMyEffectoPluginDetails(0);
			foreach($eff_details as $detail) {
				$allPostCode = $detail -> embedCode;
				$p_shortname = $detail -> shortname;
			}
			if (isset($allPostCode) && !empty($allPostCode)) {
				/* allSetCode($allPostCode, $getPostTitle);
				echo '<h1>
						<center>OR</center>
					</h1>'; */

					$getPostID = get_the_ID();
					$getPostTitle = get_the_title();
					//$getPostTitle = substr($getPostTitle, 0, 10);
					if ((!isset($getPostID) || empty($getPostID)) && (!isset($getPostTitle) || empty($getPostTitle))) {
						$getPostID = 0;
						$getPostTitle = "preview";
					}

					$eff_category = effecto_get_category(get_the_ID());
					$effectoAuthor = effecto_get_author();

					/* $allPostCode = str_replace("var effectoPreview=''","var effectoPreview='true'", $allPostCode);
					$allPostCode = str_replace("var effectoCategory = ''","var effectoCategory='".$eff_category."'", $allPostCode);
					$allPostCode = str_replace("var effectoPostId=''","var effectoPostId='0'", $allPostCode);
					$allPostCode = str_replace("var effectoPagetitle =''","var effectoPagetitle='".$getPostTitle."'", $allPostCode);
					$allPostCode = str_replace("var effectoPageurl = ''","var effectoPageurl='".$wpSite."?p=".$getPostID."'", $allPostCode);
					$allPostCode = str_replace("var effectoPublDate = ''","var effectoPublDate='".$effDate_published."'", $allPostCode); */
					
					$getPostTitle = str_replace("'","\'", $getPostTitle);
					$eff_category = str_replace("'","\'", $eff_category);

					$eff_json = "<div id='effecto_bar' style='text-align:center;'></div>
						<script>
							var eff_json = {
								'ext_path':'".plugins_url( '' , __FILE__ )."',
								'effecto_uniquename':'".$p_shortname."', 
								'effectoPostId':'0',  
								'effectoPreview': 'true', 
								'effectoPagetitle':'".$getPostTitle."', 
								'effectoPageurl':'".$wpSite.'?p='.$getPostID."', 
								'effectoPublDate':'".$effDate_published."', 
								'effectoAuthorName':'".$effectoAuthor."', 
								'effectoCategory':'".$eff_category."', 
							};
						</script><script src='//cdn-files.appspot.com/js/mye-wp.js' type='text/javascript' async='true'></script>";
					

					echo '<h2>
						<center>
							(PREVIEW-ONLY)<br />
							Your default emotion set is 
						</center>
					</h2> '.$eff_json;
			} else {
				echo '<h1>
						<center>
							You don\'t have any emotion sets configured right now.<br />
						</center>
					</h1>

					<h2>
						<center>
							<a style="cursor:pointer; text-decoration:none;" href="'.get_site_url().'/wp-admin/admin.php?page='.$eff_settings_page.'&postName='.$wpSite.'&pluginType=defaultAdd&postURL='.$_SERVER['REQUEST_URI'].'?post_id='.$getPostID.'">Add a default emotion set </a> <br /> OR 
						</center>
					</h2>';
			}
			echo '<h2 style="margin-top:-16px;">
					<center>
						<a class="effectoConfig" style="cursor:pointer;" href="'.get_site_url().'/wp-admin/admin.php?page='.$eff_settings_page.'&postID='.$getPostID.'&postName='.$wpSite.'&shortname='.$p_shortname.'&pluginType=postAdd&postURL='.$postUrl.'?post='.$getPostID.'">You can aslo configure different set for this post.</a>
					</center>
				</h2>';
		} else {
			$eff_category = effecto_get_category(get_the_ID());
			$effectoAuthor = effecto_get_author();

			/* $postCode = str_replace("var effectoPreview=''","var effectoPreview='true'", $postCode);
			$postCode = str_replace("var effectoPostId=''","var effectoPostId='".$getPostID."'", $postCode);
			$postCode = str_replace("var effectoPagetitle =''","var effectoPagetitle='".$getPostTitle."'", $postCode);
			$postCode = str_replace("var effectoPageurl = ''","var effectoPageurl='".$wpSite."?p=".$getPostID."'", $postCode);
			$postCode = str_replace("var effectoPublDate = ''","var effectoPublDate='".$effDate_published."'", $postCode); */
			
			
			$getPostTitle = str_replace("'","\'", $getPostTitle);
			$eff_category = str_replace("'","\'", $eff_category);

				$eff_json = "<div id='effecto_bar' style='text-align:center;'></div>
						<script>
							var eff_json = {
								'ext_path':'".plugins_url( '' , __FILE__ )."',
								'effecto_uniquename':'".$p_shortname."', 
								'effectoPostId':'0',  
								'effectoPreview': 'true', 
								'effectoPagetitle':'".$getPostTitle."', 
								'effectoPageurl':'".$wpSite.'?p='.$getPostID."', 
								'effectoPublDate':'".$effDate_published."', 
								'effectoAuthorName':'".$effectoAuthor."', 
								'effectoCategory':'".$eff_category."', 
							};
						</script><script src='//cdn-files.appspot.com/js/mye-wp.js' type='text/javascript' async='true'></script>";
					
			echo '<h2>
					<center>(PREVIEW-ONLY) <br>Your current emotion set for this post is </center>
				</h2> '.$eff_json;
			echo '<h2>
					<center>
						<a class="effectoConfig" style="cursor:pointer;" href="'.get_site_url().'/wp-admin/admin.php?page='.$eff_settings_page.'&postID='.$getPostID.'&postName='.$wpSite.'&pluginType=postEdit&postURL='.$_SERVER['REQUEST_URI'].'?post_id='.$getPostID.'&shortname='.$p_shortname.'">Change emotion set of this post</a>
					<center>
				</h2>';
		}
		//showEffModal();
	}

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

	function allSetCode($allPostCode, $getPostTitle) {
	    global $hostString, $eff_settings_page;

		$shortname = "";
		$eff_details = getMyEffectoPluginDetails(0);
		foreach($eff_details as $detail) {
			$shortname = $detail -> shortname;
		}

		eff_applyMinHeight();
		$eff_json = "<div id='effecto_bar' style='text-align:center'></div>
					<script>
						var eff_json = {
							'ext_path':'".plugins_url( '' , __FILE__ )."',
							'effecto_uniquename':'".$shortname."', 
							'effectoPostId':'0',  
							'effectoPreview': 'true', 
							'effectoPagetitle':'preview', 
							'effectoPageurl':'', 
						};
					</script><script src='//cdn-files.appspot.com/js/mye-wp.js' type='text/javascript' async='true'></script>";
		
		$mye_plugin_visib = get_option('mye_plugin_visib');
		$eff_isJsonPresent = false;
		$eff_isOnPost = "checked";
		$eff_isOnPage = "";
		$eff_isOnHome = "";
		$eff_isCustom = "";
		$eff_custom_list = "";
		$eff_shCode_style = "display:none;";
		$eff_should_be_disabled = "style='display:none'";
		// print_r($mye_plugin_visib);
		$eff_custom_post_html = "";
		if (isset($mye_plugin_visib) && $mye_plugin_visib) {
			$eff_isJsonPresent = true;
			$mye_plugin_visib = json_decode($mye_plugin_visib, true);
			

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
			
			if ($eff_isJsonPresent && array_key_exists($eff_cName, $mye_plugin_visib['isOnCustomList'])) {
				$checked = "checked";
			}
			
			$eff_custom_post_html .= '<input type="checkbox" c-name="'.$eff_cName.'" '.$checked.' class="eff_customPostList"  />'.$post_type->label.'&nbsp;&nbsp;';
		}
		$eff_custom_post_html .= "</span>";
		
		echo '<h2>
				<center>
					<a href="'.$hostString.'/dashboard-overview" target="_blank">Visit Dashboard</a> <br /><br />
					
					(PREVIEW-ONLY) <br>
					Your default emotion set is 
				</center>
			</h2> '.$eff_json;
		echo '<h2>
				<center>
					<a class="effectoConfig button-primary" style="cursor:pointer;font-weight:bold;font-size: 20px;margin-bottom: 10px;" href="'.get_site_url().'/wp-admin/admin.php?page='.$eff_settings_page.'&postName='.$getPostTitle.'&pluginType=defaultEdit&postURL='.$_SERVER['REQUEST_URI'].'&shortname='.$shortname.'" title="Default emotion set appears on all posts.">Reset</a>
					
					<span style="font-size:15px;padding:0px 10px;">OR</span> 
					
					<a class="effectoConfig button-primary" style="cursor:pointer;font-weight:bold;font-size: 20px;margin-bottom: 10px;" href="'.$hostString.'/login?callback=plugin_editor&sname='.$shortname.'" target="_blank" title="Edit plugin styles">Edit</a>
				</center>
			</h2>
			<hr style="border-color: #917F7F;">
			<h3>
				<center>
					<h5 id="eff_p_opt" style="margin: 0;">Show plugin on :-
						&nbsp;&nbsp;
						<input type="checkbox" id="posts" name="postType" '.$eff_isOnPost.' />Posts
						&nbsp;&nbsp;
						<input type="checkbox" id="pages" name="postType" '.$eff_isOnPage.' />Pages/ Articles
						&nbsp;&nbsp;
						<input type="checkbox" id="home" name="postType" '.$eff_isOnHome.' />Home Page
						&nbsp;&nbsp;
						<input type="checkbox" id="custom" name="postType" '.$eff_isCustom.' />Custom Posts
						&nbsp;&nbsp;
						'.$eff_custom_post_html.'
						<button style="font-size: 15px;margin-top:10px;cursor:pointer;" id="eff_visib">Save</button>
					</h5>
					<p id="eff_msg" style="display:none;font-size: 14px;"></p>
					<p id="eff_shCode" style="'.$eff_shCode_style.'">Copy <span style="font-size: 18px;background-color: #fff;padding: 6px;color: rgb(127, 128, 124);">[effecto-bar]</span> shortcode and paste in homepage where required</p>
					<br />
				</center>
			</h3>';
		?>
			<script type="text/javascript" >
				jQuery("#eff_visib").click(function() {
					var eff_isPost = jQuery("#posts").is(":checked");
					var eff_isPage = jQuery("#pages").is(":checked");
					var eff_isHome = jQuery("#home").is(":checked");
					var eff_isCustom = jQuery("#custom").is(":checked");

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
					var data = {
						'action': 'mye_update_view',
						'isPost': eff_isPost,
						'isPage': eff_isPage,
						'isHome': eff_isHome,
						'isCustom': eff_isCustom,
						'eff_custom_list': eff_custom_list,
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
	// add_action( 'save_post', 'updateEff_title' );	
	add_action( 'wp_ajax_mye_update_view', 'mye_visibUpdt_callback' );
	function mye_visibUpdt_callback() {
		$eff_isOnPost = $_POST['isPost'];
		$eff_isOnPage = $_POST['isPage'];
		$eff_isOnHome = $_POST['isHome'];
		$eff_isCustom = $_POST['isCustom'];
		$eff_custom_list = $_POST['eff_custom_list'];
		
		$escapers = array("\\");
		$replacements = array("");
		$eff_custom_list = str_replace($escapers, $replacements, $eff_custom_list);
		
		// error_log($eff_custom_list." and ".$result);
		

		update_option('mye_plugin_visib', '{"isOnPost":'.$eff_isOnPost.', "isOnPage":'.$eff_isOnPage.', "isOnHome":'.$eff_isOnHome.', "isOnCustom":'.$eff_isCustom.', "isOnCustomList":'.$eff_custom_list.'}');

		wp_die(); // this is required to terminate immediately and return a proper response
	}
?>