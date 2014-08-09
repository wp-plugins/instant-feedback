<?php
	add_action( 'add_meta_boxes', 'effectoBox' );  

	// $hostString="http://www.myeffecto.com";
	// $hostString="http://localhost:8888";

	function effectoBox() {
		add_meta_box( 'effecto_meta_box', 'MyEffecto Configuration (Open for more options)', 'showEffectoBox', 'post', 'normal', 'core' );  
	}
	$p_shortname = null;
	function showEffectoBox() {
		global $hostString;
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

					$eff_json = "<div id='effecto_bar'></div>
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
						</script>";
					wp_enqueue_script("wp-pluginJs", plugins_url( 'mye-wp.js' , __FILE__ ));

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
							<a style="cursor:pointer; text-decoration:none;" href="'.get_site_url().'/wp-admin/admin.php?page=_FILE_&postName='.$wpSite.'&pluginType=defaultAdd&postURL='.$_SERVER['REQUEST_URI'].'?post_id='.$getPostID.'">Add a default emotion set </a> <br /> OR 
						</center>
					</h2>';
			}
			echo '<h2 style="margin-top:-16px;">
					<center>
						<a class="effectoConfig" style="cursor:pointer;" href="'.get_site_url().'/wp-admin/admin.php?page=_FILE_&postID='.$getPostID.'&postName='.$wpSite.'&shortname='.$p_shortname.'&pluginType=postAdd&postURL='.$postUrl.'?post='.$getPostID.'">You can aslo configure different set for this post.</a>
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

				$eff_json = "<div id='effecto_bar'></div>
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
						</script>";
					wp_enqueue_script("wp-pluginJs", plugins_url( 'mye-wp.js' , __FILE__ ));
			echo '<h2>
					<center>(PREVIEW-ONLY) <br>Your current emotion set for this post is </center>
				</h2> '.$eff_json;
			echo '<h2>
					<center>
						<a class="effectoConfig" style="cursor:pointer;" href="'.get_site_url().'/wp-admin/admin.php?page=_FILE_&postID='.$getPostID.'&postName='.$wpSite.'&pluginType=postEdit&postURL='.$_SERVER['REQUEST_URI'].'?post_id='.$getPostID.'&shortname='.$p_shortname.'">Change emotion set of this post</a>
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
	    global $hostString;

		$shortname = "";
		$eff_details = getMyEffectoPluginDetails(0);
		foreach($eff_details as $detail) {
			$shortname = $detail -> shortname;
		}
		
		$eff_json = "<div id='effecto_bar' style='text-align:center'><img id='mye_load' src='".plugins_url( 'loading.gif' , __FILE__ )."' ></div>
					<script>
						var eff_json = {
							'ext_path':'".plugins_url( '' , __FILE__ )."',
							'effecto_uniquename':'".$shortname."', 
							'effectoPostId':'0',  
							'effectoPreview': 'true', 
							'effectoPagetitle':'preview', 
							'effectoPageurl':'', 
						};
					</script>";
				wp_enqueue_script("wp-pluginJs", plugins_url( 'mye-wp.js' , __FILE__ ));

		echo '<h2>
				<center>
					<a href="'.$hostString.'/dashboard-overview" target="_blank">Visit Dashboard</a> <br /><br />
					(PREVIEW-ONLY) <br>
					Your default emotion set is 
				</center>
			</h2> '.$eff_json;
		echo '<h2>
				<center>
					<a class="effectoConfig" style="cursor:pointer;" href="'.get_site_url().'/wp-admin/admin.php?page=_FILE_&postName='.$getPostTitle.'&pluginType=defaultEdit&postURL='.$_SERVER['REQUEST_URI'].'&shortname='.$shortname.'" title="Default emotion set appears on all posts.">Change your default emotion set </a>
				</center>
			</h2>';
			
	}

	add_action( 'save_post', 'updateEff_title' );
?>