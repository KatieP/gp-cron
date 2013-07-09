<?php

	mysql_connect("127.0.0.1", "s2-wordpress", "7BXmxPmwy4LJZNhR") or die(mysql_error());
	mysql_select_db("s2-wordpress") or die(mysql_error());
	
	$sql = "SELECT DISTINCT post_ID
       		FROM wp_postmeta";
       		
       		
	$db_result = mysql_query($sql);

	$data_set = mysql_num_rows($db_result);
	
	$i = 0;
	while ($i < $data_set) { 

		mysql_data_seek($db_result, $i);
		$row = mysql_fetch_object($db_result);
		$post_ID = $row->post_ID;
	
		mysql_query('INSERT INTO wp_postmeta SET meta_key="likecount_old", meta_value="1", post_ID = "' . $post_ID . '"');
		$i++;		
	}
//. $post_ID .

	exit();
?>
