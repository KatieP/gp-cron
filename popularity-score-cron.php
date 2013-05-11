<?php

// UPVOTES - Add unixtime to popularity_score from upvote in cron

// SOCIAL MEDIA COUNT - Add cron job for adding social media count to db and adding to popularity_score

// Todo COMMENTS - Counter, add one (like fb comment) Add unixtime to popularity_score when comment is created

// SOCIAL MEDIA -> UNIX TIME


function update_likecount() {
	/**
	 * Adds points to popularity score in db from upvotes 
	 * 1. Get likecount
	 * 2. Get likecount_old
	 * 3. Compare and save diff
	 * 4. Add diff x 3600 to popularity_score
	 *
 	 * Authors: Katie Patrick & Jesse Browne
	 *			katie.patrick@greenpag.es
	 *          jb@greenpag.es
	 **/

	mysql_connect("127.0.0.1", "s1-wordpress", "7BXmxPmwy4LJZNhR") or die(mysql_error());
	mysql_select_db("s1-wordpress") or die(mysql_error());

	$sql = 'SELECT wp_postmeta.meta_value, wp_postmeta.post_ID, wp_posts.likecount_old
       		FROM wp_postmeta, wp_posts
       		WHERE wp_postmeta.post_ID=wp_posts.ID
       		AND
       		meta_key = "likecount"';

	$db_result = mysql_query($sql);
	$data_set = mysql_num_rows($db_result);

	$i = 0;
	while ($i < $data_set) {

		mysql_data_seek($db_result, $i);
		$row = mysql_fetch_object($db_result);

		$post_ID = $row->post_ID;
		$likecount = (int) $row->meta_value;
		$likecount_old = (int) $row->likecount_old;

		if ($likecount > $likecount_old) {

			$like_difference = $likecount - $likecount_old;
			$like_difference_unixtime = pow(($like_difference*3600), 1.2);
			$like_difference_unixtime = (int) $like_difference_unixtime;

			mysql_query('UPDATE wp_posts SET popularity_score = popularity_score + '. $like_difference_unixtime .' WHERE ID = "'. $post_ID .'"');
			mysql_query('UPDATE wp_posts SET likecount_old = '. $likecount .' WHERE ID = "'. $post_ID .'"');
		} 
		$i++;
	}
}


function get_facebook_likes($url) {

   	$json_string = file_get_contents('http://graph.facebook.com/?ids=' . $url);
    $json = json_decode($json_string, true);
	
   	return intval( $json[$url]['shares'] );

}


function get_tweets($url) {

   	$json_string = file_get_contents('http://urls.api.twitter.com/1/urls/count.json?url=' . $url);
	$json = json_decode($json_string, true);

	return intval( $json['count'] );
}


function update_social_media_count() {

	/**
	 * Adds points to popularity score in db from social media activity
	 * and updates cout of facebook likes and tweets in db combined as
	 * social_media_count
	 * 
	 * Authors: Katie Patrick & Jesse Browne
	 *			katie.patrick@greenpag.es
	 *          jb@greenpag.es
	 **/

	// Get social media count from db
	mysql_connect("127.0.0.1", "s1-wordpress", "7BXmxPmwy4LJZNhR") or die(mysql_error());
	mysql_select_db("s1-wordpress") or die(mysql_error());
	
	$sql = "SELECT post_name, post_type, social_media_count, ID 
       		FROM wp_posts 
	        WHERE (post_type = 'gp_news' OR post_type = 'gp_projects' OR post_type = 'gp_advertorial')
       		AND post_status = 'publish'";
      
    $db_result = mysql_query($sql);
	$data_set = mysql_num_rows($db_result);

	$i = 0;
	while ($i < $data_set) {
	
		mysql_data_seek($db_result, $i);
		$row = mysql_fetch_object($db_result);
		
		$post_ID = $row->ID;
		$social_media_count_old = (int) $row->social_media_count;
		
		// Create a url for each post so can give it to fb and twitter  
	    $post_type = $row->post_type;
	   	$post_type_map = array( "gp_news" => "news", 
    	                        "gp_advertorial" => "eco-friendly-products", 
        	                    "projects" => "projects");
	   	$post_name = $row->post_name;
	   	 					
    	$url = 'http://www.thegreenpages.com.au/' . $post_type_map[$post_type] . "/" . $post_name;
		
		
		// Get likes and tweets from facebook and twitter
		$fb_likes = get_facebook_likes($url);
		$tweets = get_tweets($url);

		$social_media_count_new = $fb_likes + $tweets;
		
		if ($social_media_count_new > $social_media_count_old) {

			$social_media_count_diff = $social_media_count_new - $social_media_count_old;
			
			if ($social_media_count_diff > 0) {	
			
				$social_media_count_unixtime = pow(($social_media_count_diff*2000), 1.1);
				$social_media_count_unixtime = (int) $social_media_count_unixtime;

				// Compare old and new social media count and update popularity_score
				$sql_update_pop_score = 'UPDATE wp_posts SET popularity_score = popularity_score + '. $social_media_count_unixtime .' WHERE ID = "'. $post_ID .'"';	
				mysql_query($sql_update_pop_score);
    							

				// Update wp_posts with new social media score
				$social_media_count_new = (string) $social_media_count_new;
				$sql_update_sm_count = 'UPDATE wp_posts SET social_media_count = '. $social_media_count_new .' WHERE ID = "'. $post_ID .'"';

				mysql_query($sql_update_sm_count);

			}
		} 
		
		$i++;
	}
}		

update_likecount();
update_social_media_count();

?>
