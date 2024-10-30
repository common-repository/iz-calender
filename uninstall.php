<?php 
	    global $wpdb;
	    $table_name = $wpdb->prefix . "iz_calender";
		$sql = "DROP TABLE `".$table_name."`";
		$wpdb->query($sql);
		delete_option('izc_db_version');
?>