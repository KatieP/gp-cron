<?php

// This cron job runs every hour and logs the clicks for each advertiser with their product in chargify. 

// Chargify bills the client once per week.

// This job also checks the status of the advertiser as 'used_up': budget filled until next time period or 'active': 
// budget remaining or 'paused': and saves as user_meta

// All product posts for advertisers who have moved to 'used_up' or 'paused' have their product posts set to draft status.

// All product posts for advertisers who have moved from 'used_up' to 'active' when their 
// next billing cycle commences have their posts moved from draft to published.

// The system must know when clicks reach max to turn posts off, then to turn on again.


#8.  Activate 'billing' section on 'advertisers' tab on user profile.
#9.  Show plan, rate and amount billed on advertiser/billing tab: Invoices, billing history, account pause, upgrade-downgrade.
#10. Write cron (hourly) to add metered amount of clicks to chargify subscription.
#11. Each chargify subscription period (weekly) add the dollar amount and clicks on a table in billing tab
#12. Add option on billing for advertiser to upgrade, downgrade or pause their subscription
#13. Make weekly advertiser email that shows clicks and billing. Has prompt - reaching max cap? Increase your cap and 
//   get more customers for your business Not reaching cap? Create another editorial - let us know what makes your product 
//   interesting. Link to form. Add link to 'how to write a great post'.
#14. Advertiser post is shown around site as 'related posts'.
#15. When cap is reached, posts go to draft status
#16. When new billing cycle commences, posts turn on again


//-----------------------------------//

//1.Connect to MYSQL database
#mysql_connect("127.0.0.1", "s1-wordpress", "7BXmxPmwy4LJZNhR") or die(mysql_error());

mysql_connect("127.0.0.1", "root", "") or die(mysql_error());
mysql_select_db("s1-wordpress") or die(mysql_error());

//2.Sign into Google Analytics
#require '../ga/analytics.class.php';
require '../greenpag.es/gp-au-theme/ga/analytics.class.php';
$analytics = new analytics('greenpagesadserving@gmail.com', 'greenpages01'); //sign in and grab profile			
$analytics->setProfileById('ga:42443499'); 			//$analytics->setProfileByName('Stage 1 - Green Pages');

// meta_key = budget 			-> 	meta_value = 'used_up','active' or 'paused'
// meta_key = reg_advertiser 	-> 	meta_value = 1;
// meta_key = charigfyID 		-> 	meta_value = number;
// meta_key = productID 		-> 	meta_value = number;

// $12 / week CPC plan 	ID: 3313295   	Handle: 12-week-plan
// $39 / week CPC plan 	ID: 27029   	Handle: 39-week-plan
// $99 / week CPC plan 	ID: 27028   	Handle: 99-week-plan
// $249 / week plan 		ID: 3313296   	Handle: 249-week-plan
// $499 / week plan 		ID: 3313297   	Handle: 499-week-plan
// Click $1.90 component ID: 3207

// Directory Page $39 Monthly Fee ID: 27023   Handle: directory-page-39-monthly-fee

// Biome               wp=259	 ch=1733583
// New Leaf Corporate  wp=1505   ch=1787860
// 15 Trees            wp=1936	 ch=2045804
// Climate Friendly    wp=1551   ch=1828803

//3. Find active advertisers to bill: 
//   Get all authors (advertisers)IDs, chargifyIDs and productIDs where reg_advertiser=1, budget=active


# Get user id's with active click budgets
$sql = 'SELECT DISTINCT m1.user_id,
            m1.meta_value as budget
		FROM wp_usermeta m1
		    JOIN wp_usermeta m2 on (m1.user_id = m2.user_id and m2.meta_key = "budget")
		WHERE m1.meta_value = "active"';

$db_result = mysql_query($sql);

$data_set = mysql_num_rows($db_result);
echo $data_set;
echo PHP_EOL;
	
// 4. Get all posts for each ID. Grab the their analytics for the hour for all posts and sum
date_default_timezone_set('UTC');
	
$i = 0;
while ($i < $data_set) { 

	mysql_data_seek($db_result, $i);
	$row = mysql_fetch_object($db_result);
	
	# Get all product posts authored by user and store in $pageposts
	$sql_posts = 'SELECT DISTINCT wp_posts.* 
				  FROM wp_posts 
				  WHERE post_status = "publish" 
    			  	and wp_posts.post_type = "gp_advertorial" 
    			  	and wp_posts.post_author = "'. $row->user_id .'";';
	
	echo $sql_posts;
	echo PHP_EOL;
	
	$posts_results = mysql_query($sql_posts);
	$num_posts     = mysql_num_rows($posts_results);
	
	var_dump($num_posts);
	echo PHP_EOL;
	
	# Get all clicks for this users product posts
	# this variable needs to hold the total number of click that user will be billed for 
	$billable_clicks = 0;
	$j = 0;
    
	while ($j < $num_posts) { 	
	 	    
        mysql_data_seek($posts_results, $j);
	    $post_row = mysql_fetch_object($posts_results);
	    
	    #var_dump($post_row);
		
		$post_url_ext = $post_row->post_name; //Need to get post_name for URL. Gets ful URl, but we only need /url extention for Google API
        var_dump($post_url_ext);
	    echo PHP_EOL;
		
		$post_type_map = 'eco-friendly-products';
        var_dump($post_type_map);
	    echo PHP_EOL;		
				
		$post_url_end = '/' . $post_type_map . '/' . $post_url_ext . '/';
		var_dump($post_url_end);
	    echo PHP_EOL;

		#$post_date = get_the_time('Y-m-d'); 				            //Post Date
		#$post_date = $post_row->post_date;
		$one_hour_ago = time() - (60 * 60);
	    var_dump($one_hour_ago);
	    echo PHP_EOL;
		
		#$today_date = date('Y-m-d'); 						            //Todays Date
	    
  		#$analytics->setDateRange($post_date, $today_date); 	        //Set date in GA $analytics->setMonth(date('$post_date'), date('$new_date'));
				
  		#print_r($analytics->getVisitors()); 				            //get array of visitors by day
  	
  		#$pageViewURL = ($analytics->getPageviewsURL($post_url_end));	//Page views for specific URL
  		#echo $pageViewURL . ' $pageViewURL';
  		#var_dump ($pageViewURL);

  		$sumURL = 0;
  			
  		#foreach ($pageViewURL as $data) {
    	#	$sumURL = $sumURL + $data;
    	#	$total_sumURL = $total_sumURL + $data;
  		#}
  		#echo ' <br />*** ' . $sumURL . ' ***<br /> ';			
			
  		#$pageViewType = ($analytics->getPageviewsURL('/' . $post_type_map . '/'));	//Page views for the section landing page, e.g. the news page
  			
  		#$sumType = 0;
  		#foreach ($pageViewType as $data) {
    	#	$sumType = $sumType + $data;
  		#}
  				
  		#$keywords = $analytics->getData( array(
        #                                  	'dimensions' => 'ga:keyword',
        #                               	 	'metrics' => 'ga:visits',
        #                                   	'sort' => 'ga:keyword'
        #                                   	)
        #                               	);	
          	
       	#SET UP POST ID AND AUTHOR ID DATA, POST DATE, GET LINK CLICKS DATA FROM GA 
       	#$post_date_au = get_the_time('j-m-y');
		#$post_id = $post->ID;
		#$click_track_tag = '/yoast-ga/' . $post_id . '/' . $profile_author_id . '/outbound-article/';
		#$clickURL = ($analytics->getPageviewsURL($click_track_tag));
  		#$sumClick = 0;
		#foreach ($clickURL as $data) {
    	#	$sumClick = $sumClick + $data;
  		#}
			
		#switch (get_post_type()) {		# CHECK POST TYPE AND ASSIGN APPROPRIATE TITLE, URL, COST AND GET BUTTON CLICKS DATA
		   
		#	case 'gp_advertorial':
		#		$post_title = 'Products';
		#		$post_url = '/eco-friendly-products';
		#		$post_price = '$89.00';
		#  		$custom = get_post_custom($post->ID);
		#		$product_url = $custom["gp_advertorial_product_url"][0];	
		#		if ( !empty($product_url) ) {		# IF 'BUY IT' BUTTON ACTIVATED, GET CLICKS
		#			$click_track_tag_product_button = '/outbound/product-button/' . $post_id . '/' . $profile_author_id . '/' . $product_url . '/'; 
  		#			$clickURL_product_button = ($analytics->getPageviewsURL($click_track_tag_product_button));
  		#			foreach ($clickURL_product_button as $data) {
    	#				$sumClick = $sumClick + $data;
  		#			}
		#		}
				# GET PAGE IMPRESSIONS FOR OLD PRODUCT POSTS FROM BEFORE WE CHANGED URL AND ADD TO TOTAL
		#	 	$old_post_url_end = '/new-stuff/' . $post_url_ext . '/';
		#		$old_PageViewURL = ($analytics->getPageviewsURL($old_post_url_end));	//Page views for specific old URL
  		#		foreach ($old_PageViewURL as $data) {
    	#			$sumURL = $sumURL + $data;
    	#			$total_sumURL = $total_sumURL + $data;
  		#		}
	    #  		break;
		#	case 'gp_competitions':
		#		$post_title = 'Competitions';
		#		$post_url = '/competitions';
		#		$post_price = '$250.00';
	    #   		break;
	   	#	case 'gp_events':
	   	#		$post_title = 'Events';
	   	#		$post_url = '/events';
	   	#		$post_price = 'N/A';
	    #		break;
	    #	case 'gp_news':
		#	   	$post_title = 'News';
	   	#		$post_url = '/news';
	   	#		$post_price = 'N/A';		   			
	    # 		break;
	    # 	case 'gp_projects':
		#    	$post_title = 'Projects';
		#    	$post_url = '/projects';
		#    	$post_price = 'N/A';
		#        break;
		#}
			
	  	#if ($sumClick == 0) {			#IF NO CLICKS YET, DISPLAY 'Unavailable'
    	#	$sumClick = 'Unavailable';
    	#}

    	$j++;
	}	
		
		# Should have the following data available by now:
		# -> user_id (wp), 
		# -> subscription_id (chargify - unique subscription code for customer's product), 
		# -> component_id (chargify - code for click price)
		# -> billable_clicks (quantity - from google analytics work done above - int)
		
        $subscription_id = '';
        $component_id =    '';        					
		$quantity =        $billable_clicks;			
					
        //Check is under cap
    
        # get number of clicks for this week so far for this user from db
        $weekly_clicks = '';  

        # get max number of clicks for this user from db, determined by plan they are on
        $cap = '';
    
	    if ($weekly_clicks < $cap) {

	        # set post_status for all product posts for this user to 'publish'
	        ;
	
    	} else {
    
    	    # set post_status for all product posts for this user to 'draft'
    	    ;
    	    
    	}
    
        //Send to chargify metering	
    	//Send a post request with Json data to this URL
    
    	$chargify_url = 'https://greenpages.chargify.com/subscriptions/' . $subscription_id . '/components/' . $component_id . '/usages.json';
    
    	# send billing data to url above using curl 
    	
    	// curl -v -H "Content-Type: application/json" -X POST 
    	// -d ' "usage":{ "id": $subscription_id, "quantity":$quantity }' $chargify_url
	
	$i++;	
}	
?>
