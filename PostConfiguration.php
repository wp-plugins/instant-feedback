<?php
	
	add_action( 'add_meta_boxes', 'effectoBox' );  
	function effectoBox() {  
		add_meta_box( 'my-meta-box-id', 'MyEffecto Configuration', 'showEffectoBox', 'post', 'normal', 'high' );  
	}

	function showEffectoBox() {
		$pluginStatus = $_GET["plugin"];

		if ($pluginStatus == 'success') {
			addAlert($pluginStatus);
		}
		$getPostID = get_the_ID();

		if (!isset($getPostID)) {
			$getPostID = $_GET['post_id'];
		}
		
		$getPostTitle = get_the_title();
		$wpSite = get_site_url();

		//$getPostTitle = substr($getPostTitle, 0, 10);

		$postCode = null;

		$postUrl=$_SERVER['REQUEST_URI'];
		$postUrl = str_replace('post-new.php','post.php', $postUrl);

		$eff_details = getPluginDetails($getPostID);
		foreach($eff_details as $detail) {
			$postCode = $detail -> embedCode;
			$shortname = $detail -> shortname;
		}
		/* Check if there is plugin for current post. */
		if (!isset($postCode)) {
			/* If not found, check for AllPost code. */
			//$allPostCode = getEmbedCodeByPostID(0);

			$shortname = null;
			$eff_details = getPluginDetails(0);
			foreach($eff_details as $detail) {
				$allPostCode = $detail -> embedCode;
				$shortname = $detail -> shortname;
			}
			if (isset($allPostCode)) {
				/* allSetCode($allPostCode, $getPostTitle);
				echo '<h1>
						<center>OR</center>
					</h1>'; */
					$allPostCode = str_replace("var effectoPreview=''","var effectoPreview='true'", $allPostCode);
					$getPostID = get_the_ID();
					$getPostTitle = get_the_title();
					//$getPostTitle = substr($getPostTitle, 0, 10);
					if (!isset($getPostID) && !isset($getPostTitle)) {
						$getPostID = 0;
						$getPostTitle = "preview";
					}
					$allPostCode = str_replace("var effectoPostId=''","var effectoPostId='0'", $allPostCode);
					$allPostCode = str_replace("var effectoPagetitle = ''","var effectoPagetitle='".$getPostTitle."'", $allPostCode);
					$allPostCode = str_replace("var effectoPageurl = ''","var effectoPageurl='".$wpSite."?p=".$getPostID."'", $allPostCode);

					echo '<h2>
						<center>
							(PREVIEW-ONLY)<br />
							Your default emotion set is 
						</center>
					</h2> '.$allPostCode;
			} else {
				echo '<h1>
						<center>
							You don\'t have any emotion sets configured right now.<br />
						</center>
					</h1>

					<h2>
						<center>
							<a style="cursor:pointer;" href="'.get_site_url().'/wp-admin/admin.php?page=_FILE_&postName='.$getPostTitle.'&pluginType=defaultAdd&postURL='.$_SERVER['REQUEST_URI'].'?post_id='.$getPostID.'">Add a default emotion set </a> <br /> OR 
						</center>
					</h2>';
			}
			echo '<h2>
					<center>
						<a class="effectoConfig" style="cursor:pointer;" effectohref="'.get_site_url().'/wp-admin/admin.php?page=_FILE_&postID='.$getPostID.'&postName='.$getPostTitle.'&shortname='.$shortname.'&pluginType=postAdd&postURL='.$postUrl.'?post='.$getPostID.'">Add emotion set to this post</a>
					</center>
				</h2>';
		} else {
		//<strong> '.$getPostTitle.' </strong>
			$postCode = str_replace("var effectoPreview=''","var effectoPreview='true'", $postCode);
			$postCode = str_replace("var effectoPostId=''","var effectoPostId='".$getPostID."'", $postCode);
			$postCode = str_replace("var effectoPagetitle = ''","var effectoPagetitle='".$getPostTitle."'", $postCode);
			$postCode = str_replace("var effectoPageurl = ''","var effectoPageurl='".$wpSite."?p=".$getPostID."'", $postCode);

			$currentPost = "current";
			echo '<h2>
					<center>(PREVIEW-ONLY) <br>Your current emotion set for this post is </center>
				</h2> '.$postCode;
			echo '<h2>
					<center>
						<a class="effectoConfig" style="cursor:pointer;" effectohref="'.get_site_url().'/wp-admin/admin.php?page=_FILE_&postID='.$getPostID.'&postName='.$getPostTitle.'&pluginType=postEdit&postURL='.$_SERVER['REQUEST_URI'].'?post_id='.$getPostID.'&shortname='.$shortname.'">Change emotion set of this post</a>
					<center>
				</h2>';
		}
		showEffModal();
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

	function allSetCode($allPostCode, $getPostTitle) {
		$allPostCode = str_replace("var effectoPreview=''","var effectoPreview='true'", $allPostCode);
		
		$allPostCode = str_replace("var effectoPostId=''","var effectoPostId='0'", $allPostCode);
		$allPostCode = str_replace("var effectoPagetitle = ''","var effectoPagetitle='preview'", $allPostCode);
		$allPostCode = str_replace("var effectoPageurl = ''","var effectoPageurl=''", $allPostCode);

		$shortname = "";
		$eff_details = getPluginDetails(0);
		foreach($eff_details as $detail) {
			$shortname = $detail -> shortname;
		}
		echo '<h2>
				<center>
					<a href="http://www.myeffecto.com/dashboard-overview" target="_blank">Visit Dashboard</a> <br /><br />
					(PREVIEW-ONLY) <br>
					Your default emotion set is 
				</center>
			</h2> '.$allPostCode;
		echo '<h2>
				<center>
					<a class="effectoConfig" style="cursor:pointer;" effectohref="'.get_site_url().'/wp-admin/admin.php?page=_FILE_&postName='.$getPostTitle.'&pluginType=defaultEdit&postURL='.$_SERVER['REQUEST_URI'].'&shortname='.$shortname.'" title="Default emotion set appears on all posts.">Change your default emotion set </a>
				</center>
			</h2>';
			showEffModal();
	}
?>