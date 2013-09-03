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
		$getPostTitle = get_the_title();

		$getPostTitle = substr($getPostTitle, 0, 11);

		$postCode = getEmbedCodeByPostID($getPostID);

		/* Check if there is plugin for current post. */
		if (!isset($postCode)) {

			/* If not found, check for AllPost code. */
			$allPostCode = getEmbedCodeByPostID(0);
			if (isset($allPostCode)) {
				/* allSetCode($allPostCode, $getPostTitle);
				echo '<h1>
						<center>OR</center>
					</h1>'; */
					$allPostCode = str_replace("var effectoPreview=''","var effectoPreview='true'", $allPostCode);
					$getPostID = get_the_ID();
					if (!isset($getPostID)) {
						$getPostID = 0;
					}
					$allPostCode = str_replace("var effectoPostId=''","var effectoPostId='".$getPostID."'", $allPostCode);
					echo '<h2>
						<center>
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
							<a href="'.get_site_url().'/wp-admin/admin.php?page=_FILE_&postName='.$getPostTitle.'&pluginType=defaultAdd&postURL='.$_SERVER['REQUEST_URI'].'">Add a default emotion set </a> <br /> OR 
						</center>
					</h2>';
			}
			echo '<h2>
					<center>
						<a href="'.get_site_url().'/wp-admin/admin.php?page=_FILE_&postID='.$getPostID.'&postName='.$getPostTitle.'&pluginType=postAdd&postURL='.$_SERVER['REQUEST_URI'].'">Add emotion set to this post</a>
					</center>
				</h2>';
		} else {
		//<strong> '.$getPostTitle.' </strong>
			$postCode = str_replace("var effectoPreview=''","var effectoPreview='true'", $postCode);
			$postCode = str_replace("var effectoPostId=''","var effectoPostId='".$getPostID."'", $postCode);
			
			$shortname = substr($postCode, stripos($postCode, 'effecto_uniquename'), strpos($postCode, ";") - stripos($postCode, 'effecto_uniquename'));
			echo '<h2>
					<center>Your current emotion set for this post is </center>
				</h2> '.$postCode;
			echo '<h2>
					<center>
						<a href="'.get_site_url().'/wp-admin/admin.php?page=_FILE_&postID='.$getPostID.'&postName='.$getPostTitle.'&pluginType=postEdit&postURL='.$_SERVER['REQUEST_URI'].'&shortname='.$shortname.'" onclick="return deleteItem()">Change emotion set of this post</a>
					<center>
				</h2>';
		}
	}

	function allSetCode($allPostCode, $getPostTitle) {
		$allPostCode = str_replace("var effectoPreview=''","var effectoPreview='true'", $allPostCode);
		$getPostID = get_the_ID();
		if (!isset($getPostID)) {
			$getPostID = 0;
		}
		$allPostCode = str_replace("var effectoPostId=''","var effectoPostId='".$getPostID."'", $allPostCode);
		$shortname = substr($allPostCode, stripos($allPostCode, 'effecto_uniquename'), strpos($allPostCode, ";") - stripos($allPostCode, 'effecto_uniquename'));
		echo '<h2>
				<center>
					Your default emotion set is 
				</center>
			</h2> '.$allPostCode;
		echo '<h2>
				<center>
					<a href="'.get_site_url().'/wp-admin/admin.php?page=_FILE_&postName='.$getPostTitle.'&pluginType=defaultEdit&postURL='.$_SERVER['REQUEST_URI'].'&shortname='.$shortname.'" onclick="return deleteItem()" title="Default emotion set appears on all posts.">Change your default emotion set </a>
				</center>
			</h2>';
	}

?>
<script type="text/javascript">
	function deleteItem() {
		if (confirm("Changing your default set will erase your current emotion set data. Do you want to continue?")) {
			<?php 
				$codeToChange = getEmbedCodeByPostID(0);
				$shortname = substr($codeToChange, stripos($codeToChange, 'effecto_uniquename'), strpos($codeToChange, ";") - stripos($codeToChange, 'effecto_uniquename'));
			?>
			return true;
		}
		return false;
	}
</script>