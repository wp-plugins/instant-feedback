<?php

	/* Table Name to access with different functions */
	$table_name = $wpdb->prefix . "effecto";

	/* Create Effecto Table */
	function createEffectoTable() {
		global $table_name;
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			userID mediumint(9) NOT NULL,
			apiKey VARCHAR(60) NOT NULL,
			postID mediumint(9) NOT NULL,
			embedCode TEXT,
			UNIQUE KEY id (id)
		);";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/* Save plugin data in db */
	function insertInDb($user_id, $apiKey, $code, $postID) {
		$code = stripcslashes($code);

		global $wpdb;
		global $table_name;
		$wpdb->insert( 
						$table_name, 
						array(
							'userID' => $user_id, 
							'apiKey' => $apiKey, 
							'embedCode' => $code, 
							'postID' => $postID
						),
						array(
							'%d', 
							'%s', 
							'%s', 
							'%d'
						)
					);

	}

	/* Update effecto table */
	function updateEmbedCode($data, $postID) {
		$data = stripcslashes($data);

		global $wpdb;
		global $table_name;

		$wpdb->update(
			$table_name,
			array(
				'embedCode' => $data,
			),
			array( 'postID' => $postID ),
			array( 
				'%s',	// value1
			), 
			array( '%d' )
		);
	}

	/* Select pluginCode by userID */
	function getEmbedCodeByPostID($postID) {
		global $wpdb;
		global $table_name;

		return $wpdb->get_var("SELECT embedCode FROM $table_name WHERE postID=".$postID);
	}
?>
