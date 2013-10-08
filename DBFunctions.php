<?php

	/* Table Name to access with different functions */
	$table_name = $wpdb->prefix . "effecto";

	/* Create Effecto Table */
	function createEffectoTable($effecto_db_version) {
		global $table_name;
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			userID mediumint(9) NOT NULL,
			apiKey VARCHAR(60) NOT NULL,
			shortname VARCHAR(60) NOT NULL,
			postID mediumint(9) NOT NULL,
			embedCode TEXT,
			PRIMARY KEY id (id)
		);";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		add_option( "effecto_db_version", $effecto_db_version );
	}

	/* Save plugin data in db */
	function insertInDb($user_id, $apiKey, $code, $postID, $eff_shortname) {
		$code = stripcslashes($code);

		global $wpdb;
		global $table_name;
		$wpdb->insert( 
						$table_name, 
						array(
							'userID' => $user_id, 
							'apiKey' => $apiKey, 
							'embedCode' => $code, 
							'postID' => $postID,
							'shortname' => $eff_shortname, 
						),
						array(
							'%d', 
							'%s', 
							'%s', 
							'%d',
							'%s', 
						)
					);

	}

	/* Update effecto table */
	function updateEmbedCode($data, $postID, $eff_shortname) {
		$data = stripcslashes($data);

		global $wpdb;
		global $table_name;

		$wpdb->update(
			$table_name,
			array(
				'embedCode' => $data,
				'shortname' => $eff_shortname,
			),
			array( 'postID' => $postID ),
			array( 
				'%s',	// value1
				'%s',	// value2
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
	
	function getPluginDetails($postID) {
		global $wpdb;
		global $table_name;
		return $wpdb->get_results( "SELECT embedCode, shortname FROM $table_name WHERE postID=".$postID);
	}
?>
