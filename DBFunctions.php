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
        
//        function alterTable($col_name)
//        {
//            global $wpdb;
//            global $table_name;
//            $wpdb->query("ALTER TABLE $table_name ADD settings AFTER embedCode");
//        }
        
        function addTrendingUrl($trending_url)// is added to wp_options table
        {
            update_option('trending_url', $trending_url);
        }
        
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
//                 $settings="{url:'http://abc.com'}";
//                 update_option('settings', $settings);
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
			return $wpdb->get_results( "SELECT embedCode, shortname FROM $table_name WHERE postID=".$postID);
		}
		return null;
	}
	
	function getMyEffectoShortnames() {
		global $wpdb;
		global $table_name;
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			return null;
		} else {
			return $wpdb->get_var("SELECT shortname FROM $table_name");
		}
	}
?>
