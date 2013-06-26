<?php
//Takes postdate and turns it into unixtime and adds to popularity score. Use as one-off to commennce homepage ranks. 

	mysql_connect("127.0.0.1", "s1-wordpress", "7BXmxPmwy4LJZNhR") or die(mysql_error());
	mysql_select_db("s1-wordpress") or die(mysql_error());
	
	$sql = "SELECT ID, post_date
       		FROM wp_posts
       		WHERE post_status = 'publish'";
       		
       		
	$db_result = mysql_query($sql);
	
	#var_dump($db_result);
	$data_set = mysql_num_rows($db_result);
	echo $data_set;
	echo '<hr />';
	
	date_default_timezone_set('UTC');
	
	$i = 0;
	while ($i < $data_set) { 

		mysql_data_seek($db_result, $i);
		$row = mysql_fetch_object($db_result);
		$ID = $row->ID;
		echo $ID;
		echo '<hr />';
		$post_date = $row->post_date;
		echo $post_date;
		echo '<hr />';
		$popularity_score = strtotime($post_date);
		echo $popularity_score;
		var_dump($popularity_score);
	
		$update_query = 'UPDATE wp_posts SET popularity_score = popularity_score + ' . $popularity_score . ' WHERE ID = "' . $ID . '"';
		
		var_dump($update_query);
		mysql_query($update_query);
		echo '<hr />';
		echo '<hr />';
		echo '<hr />';
		$i++;		
	}

?>

