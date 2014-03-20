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
			isDisabled BOOLEAN,
			embedCode TEXT,
			UNIQUE KEY id (id)
		);";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		add_option( "effecto_db_version", $effecto_db_version );
	}

	/* Save plugin data in db */
	function insertInMyEffectoDb($user_id, $apiKey, $code, $postID, $eff_shortname) {
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
				'isDisabled' => 0
			),
			array(
				'%d', 
				'%s', 
				'%s', 
				'%d',
				'%s', 
				'%s'
			)
		);
	}

	function effecto_insert_plugin_disable($isDisabled, $postId, $eff_shortname, $userId) {
		global $wpdb;
		global $table_name;

		$embedCode = "";
		$p_shortname = "";
		$apiPluginDetailsArray = getMyEffectoPluginDetails($postId);
		foreach($apiPluginDetailsArray as $detail) {
			$embedCode = $detail -> embedCode;
			$p_shortname = $detail -> shortname;
			$is_disabled = $detail -> isDisabled;
		}

		if ($isDisabled != $is_disabled && empty($embedCode)) {
			$wpdb->insert(
			$table_name,
				array(
					'userID' => $userId, 
					'apiKey' => "", 
					'embedCode' => "",
					'postID' => $postId,
					'shortname' => $eff_shortname,
					'isDisabled' => $isDisabled,
				),
				array(
					'%d', 
					'%s', 
					'%s', 
					'%d',
					'%s',
					'%s',
				)
			);
		} else {
			$wpdb->update(
				$table_name,
				array(
					'isDisabled' => $isDisabled,
				),
				array( 'postID' => $postId ),
				array(
					'%s',
				), 
				array( '%d' )
			);
		}
	}

	function effecto_delete_disabled($postId, $isDisabled) {
		global $wpdb;
		global $table_name;

		$embedCode = getMyeffectoEmbedCodeByPostID($postId);
		if (!isset($embedCode)) {
				$wpdb->delete(
				$table_name, 
				array( 'postID' => $postId ), 
				array( '%d' )
			);
		} else {
			$wpdb->update(
				$table_name,
				array(
					'isDisabled' => $isDisabled,
				),
				array( 'postID' => $postId ),
				array(
					'%s',
				), 
				array( '%d' )
			);
		}
	}

	/* Update effecto table */
	function updateMyeffectoEmbedCode($data, $postID, $eff_shortname) {
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

	function updateNewMyeffectoEmbedCode($data, $eff_shortname) {
		$data = stripcslashes($data);

		global $wpdb;
		global $table_name;

		$wpdb->update(
			$table_name,
			array(
				'embedCode' => $data,
			),
			array( 'shortname' => $eff_shortname ),
			array( 
				'%s',	// value1
			), 
			array( '%s' )
		);
	}

	/* Select pluginCode by userID */
	function getMyeffectoEmbedCodeByPostID($postID) {
		global $wpdb;
		global $table_name;

		return $wpdb->get_var("SELECT embedCode FROM $table_name WHERE postID=".$postID);
	}

	function getMyEffectoPluginDetails($postID) {
		if (isset($postID)) {
			global $wpdb;
			global $table_name;
			return $wpdb->get_results( "SELECT embedCode, shortname, isDisabled FROM $table_name WHERE postID=".$postID);
		}
	}

	function effecto_is_disabled($postID) {
		global $wpdb;
		global $table_name;

		return $wpdb->get_var("SELECT isDisabled FROM $table_name WHERE postID=".$postID);
	}
?>