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

// 3a. Define functions to get clicks

function get_clicks_for_post($post_row, $user_row, $analytics, $start_range, $end_range) {
	    
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
		
  		$analytics->setDateRange($start_range, $end_range);	        //Set date in GA $analytics->setMonth(date('$post_date'), date('$new_date'));
          	
       	#SET UP POST ID AND AUTHOR ID DATA, POST DATE, GET LINK CLICKS DATA FROM GA 
		$profile_author_id = $user_row->user_id;
		$post_id =           $post_row->ID;
		$click_track_tag =   '/yoast-ga/' . $post_id . '/' . $profile_author_id . '/outbound-article/';
		
		$clickURL = ($analytics->getPageviewsURL($click_track_tag));
  		$sumClick = 0;
		foreach ($clickURL as $data) {
    		$sumClick = $sumClick + $data;
  		}
		var_dump($sumClick);
        echo PHP_EOL;   
        
		$post_url =   '/eco-friendly-products';
		
		#$custom = get_post_custom($post->ID);
		#$product_url = $custom["gp_advertorial_product_url"][0];	

	    // Get url product button is linked to
	    $sql_product_url = 'SELECT meta_value 
                            FROM wp_postmeta 
                            WHERE post_id = "'. $post_id .'"
                                AND meta_key = "gp_advertorial_product_url";';
	
	    echo $sql_product_url;
	    echo PHP_EOL;    

	    $product_url_results = mysql_query($sql_product_url);
        mysql_data_seek($product_url_results, 0);
	    $product_url_row = mysql_fetch_object($product_url_results);	
		$product_url = $product_url_row->meta_value;
		
		var_dump($product_url);
	    echo PHP_EOL; 
		
		if ( !empty($product_url) ) {		# IF 'BUY IT' BUTTON ACTIVATED, GET CLICKS
			
		    $click_track_tag_product_button = '/outbound/product-button/' . $post_id . '/' . $profile_author_id . '/' . $product_url . '/'; 
        	var_dump($click_track_tag_product_button);
            echo PHP_EOL; 
	         
			$clickURL_product_button = ($analytics->getPageviewsURL($click_track_tag_product_button));
  			var_dump($clickURL_product_button);
            echo PHP_EOL; 
            
  			foreach ($clickURL_product_button as $data) {
    			$sumClick = $sumClick + $data;
  			}
		}
		var_dump ($sumClick);
        echo PHP_EOL;   
			
	  	#if ($sumClick == 0) {			#IF NO CLICKS YET, DISPLAY 'Unavailable'
    	#	$sumClick = 'Unavailable';
    	#}
        
        return $sumClick;
}

// 4. Get all posts for each ID. Grab the their analytics for the hour for all posts and sum
date_default_timezone_set('UTC');
	
$i = 0;
while ($i < $data_set) { 

	mysql_data_seek($db_result, $i);
	$user_row = mysql_fetch_object($db_result);
	
	# Get all product posts authored by user and store in $pageposts
	$sql_posts = 'SELECT DISTINCT wp_posts.* 
				  FROM wp_posts 
				  WHERE post_status = "publish" 
    			  	and wp_posts.post_type = "gp_advertorial" 
    			  	and wp_posts.post_author = "'. $user_row->user_id .'";';
	
	echo $sql_posts;
	echo PHP_EOL;
	
	$posts_results = mysql_query($sql_posts);
	$num_posts     = mysql_num_rows($posts_results);
	
	var_dump($num_posts);
	echo PHP_EOL;
	
	# Get all clicks for this users product posts
	# this variable needs to hold the total number of click that user will be billed for 
	$billable_clicks  =  0;
	$clicks_this_week =  0;
	$j =                 0;
    
	while ($j < $num_posts) { 	
	    
        mysql_data_seek($posts_results, $j);
	    $post_row = mysql_fetch_object($posts_results);
	    
	    $now =                       time();
	    $yesterday_date_stamp =      ( $now - (24 * 60 * 60) );
		$yesterday_date       =      date('Y-m-d', $yesterday_date_stamp);
		echo '$yesterday_date: ';
	    var_dump($yesterday_date);
	    echo PHP_EOL;

		$today_date =                date('Y-m-d'); 		            //Todays Date
	    echo '$today_date: ';
		var_dump($today_date);
	    echo PHP_EOL;
	    
        $sumClick_past_day =   get_clicks_for_post($post_row, $user_row, $analytics, $yesterday_date, $today_date);
	    
        	    // Get time advertiser signed up to chargify
	    $sql_adv_time = 'SELECT meta_value 
                         FROM wp_usermeta 
                         WHERE user_id = "'. $user_row->user_id .'"
                             AND meta_key = "adv_signup_time";';
	
	    echo $sql_adv_time;
	    echo PHP_EOL;    

	    $signup_time_results = mysql_query($sql_adv_time);
        mysql_data_seek($signup_time_results, 0);
	    $signup_time_row = mysql_fetch_object($signup_time_results);	    
	    
	    $advertiser_signup_time = $signup_time_row->meta_value;
	    
	    var_dump($advertiser_signup_time);
	    echo PHP_EOL;
	    
	    // Get difference between last week anniversary of sign up
	    $one_week = (7 * 24 * 60 * 60);
	    var_dump($one_week);
	    echo PHP_EOL;

	    $now =                      time();
	    $total_time_signedup =      $now - $advertiser_signup_time;
	    $this_billing_week =        $total_time_signedup % $one_week;
	    $start_this_billing_week =  $now - $this_billing_week;

	    echo '$start_this_billing_week: ';
	    var_dump($start_this_billing_week);
	    echo PHP_EOL;
	    
	    $start_date_billing_week =  date('Y-m-d', $start_this_billing_week);
	    
		#$one_hour_ago_stamp =       ( $now - (60 * 60) );
	    #var_dump($one_hour_ago_stamp);
	    #echo PHP_EOL;
		
	    #$one_hour_ago =             date('H', $one_hour_ago_stamp);
	    #var_dump($one_hour_ago);
	    #echo PHP_EOL;	    
	    
	    #$this_hour =                date('H', $now);
	    #var_dump($this_hour);
	    #echo PHP_EOL;
        
	    $sumClick_this_week =  get_clicks_for_post($post_row, $user_row, $analytics, $start_date_billing_week, $today_date);
        
        $billable_clicks   = $billable_clicks  + $sumClick_past_day;
		$clicks_this_week  = $clicks_this_week + $sumClick_this_week;
		 
		echo '$billable_clicks: ';
        var_dump ($billable_clicks);
        echo PHP_EOL;
        
        echo '$clicks_this_week: ';
        var_dump ($clicks_this_week);
        echo PHP_EOL;
                
    	$j++;
	}	
		
	# Should have the following data available by now:
	# -> user_id (wp), 
	# -> subscription_id (chargify - unique subscription code for customer's product), 
	# -> component_id (chargify - code for click price)
	# -> billable_clicks (quantity - from google analytics work done above - int)

	
	// Get chargify subscription id
	$sql_subscription_id  = 'SELECT meta_value 
                             FROM   wp_usermeta 
                             WHERE  user_id = "'. $user_row->user_id .'"
                                 AND meta_key = "subscription_id";';
	
	echo $sql_subscription_id;
	echo PHP_EOL; 

    $sql_subscription_id_results = mysql_query($sql_subscription_id);
    mysql_data_seek($sql_subscription_id_results, 0);
	$subscription_id_row = mysql_fetch_object($sql_subscription_id_results);	
	$subscription_id = $subscription_id_row->meta_value;
    
	var_dump($subscription_id);
    echo PHP_EOL;

    // Get chargify product id
	$sql_product_id  = 'SELECT meta_value 
                        FROM   wp_usermeta 
                        WHERE  user_id = "'. $user_row->user_id .'"
                             AND meta_key = "product_id";';
	
	echo $sql_product_id;
	echo PHP_EOL; 

    $sql_product_id_results = mysql_query($sql_product_id);
    mysql_data_seek($sql_product_id_results, 0);
	$product_id_row = mysql_fetch_object($sql_product_id_results);	
	$product_id = $product_id_row->meta_value;
    
	var_dump($product_id);
    echo PHP_EOL;
    
    $component_id = '';
    
    switch ($product_id)   {
        case '3313295':
            // $12 per week plan
            $component_id = '3207';
            $cap = (int) (12.00 / 1.9);
            break;
        case '27029':
            // $39 per week plan
            $component_id = '3207';
            $cap = (int) (39.00 / 1.9);
            break;
        case '27028':
            // $99 per week plan
            $component_id = '3207';
            $cap = (int) (99.00 / 1.9);
            break; 
        case '3313296':
            // $249 per week plan
            $component_id = '20016';
            $cap = (int) (249.00 / 1.8);
            break; 
        case '3313297':
            // $499 per week plan
            $component_id = '20017';
            $cap = (int) (449.00 / 1.7);
            break;                                                
    }    					
    
    var_dump($component_id);
    echo PHP_EOL;

    // Send to chargify metering	
    // Send a post request with Json data to this URL
    
	if (!empty($component_id)) {

	        echo '$cap: ';
            var_dump($cap);
            echo PHP_EOL;
            
            $quantity = $billable_clicks;
            
            echo '$quantity: ';			
            var_dump($quantity);
            echo PHP_EOL;
	    
        //Check is under cap
    	if ($clicks_this_week < $cap) {
    
    	    $k = 0;
        
    	    while ($k < $num_posts) { 	
    	    
                mysql_data_seek($posts_results, $k);
    	        $post_row = mysql_fetch_object($posts_results);	    
    
    	        # set post_status to 'publish'
                $post_status_sql =   'UPDATE wp_posts 
    								  SET post_status = replace(post_status, "pending", "publish") 
    								  WHERE post_id ="'. $post_row->ID .'"
    	    						      AND post_status = "publish";';
    
                mysql_query($post_status_sql);
                
                echo 'Set post '. $post_row->ID .' to publish';
                echo PHP_EOL;
                
                $k++;
    	    
    	    }
    	        
    	    # set budget_status to 'active'
    	    $budget_status_sql = 'UPDATE wp_usermeta 
    							  SET meta_value = replace(meta_value, "paused", "active") 
    							  WHERE meta_key = "budget_status" 
    	    					      AND user_id ="'. $user_row->user_id .'" ;';
    
    	    # run budget_status query on db    
            mysql_query($budget_status_sql);
            echo 'Set budget_status for user '. $user_row->user_id .' to active';      	    
    	    echo PHP_EOL;
    	    
    	} else {
    
    	    $k = 0;
        
    	    while ($k < $num_posts) { 	
    	    
                mysql_data_seek($posts_results, $k);
    	        $post_row = mysql_fetch_object($posts_results);	  
    	        
    	        # set post_status to 'publish'
                $post_status_sql =   'UPDATE wp_posts 
    								  SET post_status = replace(post_status, "pending", "publish") 
    								  WHERE post_id ="'. $post_row->ID .'"
    	    						      AND post_status = "pending";';
    
                echo 'Set post '. $post_row->ID .' to pending';
                echo PHP_EOL;
                
                mysql_query($post_status_sql);
                $k++;
                
    	    }
    	    
            # set budget_status to 'paused'
    	    $budget_status_sql = 'UPDATE wp_usermeta 
    							  SET meta_value = replace(meta_value, "active", "paused") 
    							  WHERE meta_key = "budget_status" 
    	    					      AND user_id ="'. $user_row->user_id .'" ;';  
    
    	    # run budget_status and post_status queries on db    
            mysql_query($budget_status_sql);
            echo 'Set budget_status for user '. $user_row->user_id .' to paused';
            echo PHP_EOL;
    
    	}
	    
    	echo PHP_EOL; 
        echo '_______________________________________________________';
        echo PHP_EOL;
        echo '_______________________________________________________';
        echo PHP_EOL; 
        echo PHP_EOL;        
    	
	    $chargify_url = 'https://greenpages.chargify.com/subscriptions/' . $subscription_id . '/components/' . $component_id . '/usages.json';
        echo '$chargify_url: '. $chargify_url;
        echo PHP_EOL;
        
        $usage = ' "usage":{ "id": '. $subscription_id .', "quantity": '. $quantity .' }';
        echo '$usage: '. $usage;
        echo PHP_EOL;
        # send billing data to url above using curl 

        if ($quantity != 0) {
            echo 'Send data to chargify!';   
            echo PHP_EOL;

            // curl -v -H "Content-Type: application/json" -X POST 
            // -d ' "usage":{ "id": $subscription_id, "quantity":$quantity }' $chargify_url     
                        
        } else {
            echo 'No clicks, do not send data to chargify!';
            echo PHP_EOL;            
        }

    	echo PHP_EOL; 
        echo '_______________________________________________________';
        echo PHP_EOL;
        echo '_______________________________________________________';
        echo PHP_EOL; 
        echo PHP_EOL;

	} else {
	    echo 'No $component_id found, no data sent to chargify.';
	    echo PHP_EOL;
	}
    	
	$i++;	
}	

exit();

?>
