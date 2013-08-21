<?php

// This cron job runs every hour and logs the clicks for each advertiser with their product in chargify. 

// Chargify bills the client once per week.

// This job also checks the status of the advertiser as 'used_up': budget filled until next time period or 'active': 
// budget remaining or 'cancelled': and saves as user_meta

// All product posts for advertisers who have moved to 'used_up' or 'cancelled' have their product posts set to draft status.

// All product posts for advertisers who have moved from 'used_up' to 'active' when their 
// next billing cycle commences have their posts moved from draft to published.

// The system must know when clicks reach max to turn posts off, then to turn on again.


//9.  Show plan, rate and amount billed on advertiser/billing tab: Invoices, billing history, account pause, upgrade-downgrade.
//10. Write cron (hourly) to add metered amount of clicks to chargify subscription.
//11. Each chargify subscription period (weekly) add the dollar amount and clicks on a table in billing tab
//12. Add option on billing for advertiser to upgrade, downgrade or pause their subscription
//13. Make weekly advertiser email that shows clicks and billing. Has prompt - reaching max cap? Increase your cap and 
//    get more customers for your business Not reaching cap? Create another editorial - let us know what makes your product 
//    interesting. Link to form. Add link to 'how to write a great post'.
//14. Advertiser post is shown around site as 'related posts'.
//15. When cap is reached, posts go to draft status
//16. When new billing cycle commences, posts turn on again

//-----------------------------------//

echo PHP_EOL; 
echo '_______________________________________________________';
echo PHP_EOL;
echo '_______________________________________________________';
echo PHP_EOL; 
echo 'Begins';
echo PHP_EOL; 
echo '_______________________________________________________';
echo PHP_EOL;
echo '_______________________________________________________';
echo PHP_EOL; 
echo PHP_EOL;

//1.Connect to MYSQL database
$db_connection = mysql_connect("127.0.0.1", "s2-wordpress", "7BXmxPmwy4LJZNhR") or die(mysql_error());
#mysql_connect("127.0.0.1", "root", "") or die(mysql_error());

echo PHP_EOL; 
echo '_______________________________________________________';
echo PHP_EOL;
echo 'Select databse';
echo PHP_EOL;
echo '_______________________________________________________';
echo PHP_EOL; 

mysql_select_db("s2-wordpress") or die(mysql_error());
#mysql_select_db("s1-wordpress") or die(mysql_error());

//2.Sign into Google Analytics
echo PHP_EOL; 
echo '_______________________________________________________';
echo PHP_EOL;
echo 'Sign in to Google Analytics';
echo PHP_EOL;
echo '_______________________________________________________';
echo PHP_EOL;

//require '../ga/analytics.class.php';
//require '../greenpag.es/gp-au-theme/ga/analytics.class.php';
//require '../gp-theme/gp-au-theme/ga/analytics.class.php';
require '/var/www/production/www.greenpag.es/wordpress/wp-content/themes/gp-au-theme/ga/analytics.class.php';
require '/var/www/production/www.greenpag.es/wordpress/wp-content/themes/gp-au-theme/functions.php';

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

echo PHP_EOL; 
echo '_______________________________________________________';
echo PHP_EOL;
echo 'Get user ids with click budget_status user meta set';
echo PHP_EOL; 
echo '_______________________________________________________';
echo PHP_EOL;

# Get user id's with active click budgets
$sql = 'SELECT DISTINCT m1.user_id,
            m1.meta_value as budget_status
		FROM wp_usermeta m1
		    JOIN wp_usermeta m2 on (m1.user_id = m2.user_id and m2.meta_key = "budget_status")
		WHERE m1.meta_key = "budget_status"';

$db_result = mysql_query($sql);

$data_set = mysql_num_rows($db_result);
echo 'Users with budget_status set: '. $data_set;
echo PHP_EOL;

// 4. Get all posts for each ID. Grab the their analytics for the hour for all posts and sum

echo PHP_EOL; 
echo '_______________________________________________________';
echo PHP_EOL;
echo 'Get posts, grab analytics and sum clicks';
echo PHP_EOL; 
echo '_______________________________________________________';
echo PHP_EOL;
echo PHP_EOL;

date_default_timezone_set('UTC');
	
$i = 0;
while ($i < $data_set) {
    
	mysql_data_seek($db_result, $i);
	$user_row = mysql_fetch_object($db_result);
	$user_id = $user_row->user_id;
	
    if ( ( $user_row->budget_status == 'active' ) || ( $user_row->budget_status == 'used_up' ) ) {

    	// Get chargify subscription id
    	$sql_subscription_id  = 'SELECT meta_value 
                                 FROM   wp_usermeta 
                                 WHERE  user_id = "'. $user_id .'"
                                     AND meta_key = "subscription_id";';
    
        $sql_subscription_id_results = mysql_query($sql_subscription_id);    
        mysql_data_seek($sql_subscription_id_results, 0);
    	$subscription_id_row = mysql_fetch_object($sql_subscription_id_results);	
    	$subscription_id = $subscription_id_row->meta_value;      
        
        // Get chargify product id
    	$sql_product_id  = 'SELECT meta_value 
                            FROM   wp_usermeta 
                            WHERE  user_id = "'. $user_id .'"
                                 AND meta_key = "product_id";';
    
        $sql_product_id_results = mysql_query($sql_product_id);
        mysql_data_seek($sql_product_id_results, 0);
    	$product_id_row = mysql_fetch_object($sql_product_id_results);	

    	$product_id =     $product_id_row->meta_value;
        $component_id =   get_component_id($product_id);
        $cap =            get_click_cap($product_id);

        if (!empty($component_id)) {
            
           	echo PHP_EOL; 
            echo '_______________________________________________________';
            echo PHP_EOL;
            echo 'User: '. $user_id;
            echo PHP_EOL; 
            echo '_______________________________________________________';
            echo PHP_EOL;
            
            echo '$subscription_id: ';
    	    var_dump($subscription_id);
            echo PHP_EOL;  

            # Get all product posts authored by user and store in $posts_results
        	$sql_posts = 'SELECT DISTINCT wp_posts.* 
        				  FROM wp_posts 
        				  WHERE ( post_status = "publish"
        				          or post_status = "pending" ) 
            			      AND wp_posts.post_type = "gp_advertorial" 
            			      AND wp_posts.post_author = "'. $user_id .'";';
        	
        	$posts_results = mysql_query($sql_posts);
        	$num_posts     = mysql_num_rows($posts_results);
        	
        	# Get all clicks for this users product posts
        	# this variable needs to hold the total number of click that user will be billed for 
        	$billable_clicks  =  0;
        	$clicks_this_week =  0;
        	$j =                 0;
            
        	while ($j < $num_posts) { 	
        	    
                mysql_data_seek($posts_results, $j);
        	    $post_row =                  mysql_fetch_object($posts_results);
        	    
        	    $now =                       time();
        	    $yesterday_date_stamp =      ( $now - (24 * 60 * 60) );
        		$yesterday_date       =      date('Y-m-d', $yesterday_date_stamp);
        
        		$today_date =                date('Y-m-d'); 		            //Todays Date
        	    
                $sumClick_past_day =         get_clicks_for_post($post_row, $user_id, $analytics, $yesterday_date, $today_date);
        	    
                // Get time advertiser signed up to chargify
        	    $sql_adv_time = 'SELECT meta_value 
                                 FROM wp_usermeta 
                                 WHERE user_id = "'. $user_id .'"
                                     AND meta_key = "adv_signup_time";';
        
        	    $signup_time_results =      mysql_query($sql_adv_time);
                mysql_data_seek($signup_time_results, 0);
    
                $signup_time_row =          mysql_fetch_object($signup_time_results);    	    
        	    $advertiser_signup_time =   $signup_time_row->meta_value;
        	    
        	    // Get difference between last week anniversary of sign up
        	    $one_week =                 (7 * 24 * 60 * 60);
        
        	    $now =                      time();
        	    $total_time_signedup =      $now - $advertiser_signup_time;
        	    $this_billing_week =        $total_time_signedup % $one_week;
        	    $start_this_billing_week =  $now - $this_billing_week;
        	    
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
                
        	    $sumClick_this_week =  get_clicks_for_post($post_row, $user_id, $analytics, $start_date_billing_week, $today_date);
                
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
        	
    	    echo '$cap: ';
            var_dump($cap);
            echo PHP_EOL;
            
            // Set quantity of clicks from last 24 hrs, this number is sent to chargify 
            // and added to their usage total for billing, hence the need to enforce their total never exceeds their cap
            $quantity = ( ( $billable_clicks + $clicks_this_week ) <= $cap ) ? $billable_clicks : ($cap - $clicks_this_week);
                
            echo '$quantity: ';			
            var_dump($quantity);
            echo PHP_EOL;

            echo '$clicks_this_week: ';			
            var_dump($clicks_this_week);
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
        								  WHERE ID ="'. $post_row->ID .'"
        	    						      AND post_status = "pending";';

                    echo '$post_status_sql: ';			
                    var_dump($post_status_sql);
                    echo PHP_EOL;
                    
                    mysql_query($post_status_sql);
                    
                    echo 'Set post '. $post_row->ID .' to publish';
                    echo PHP_EOL;
                    
                    $k++;
        	    
        	    }
        	        
        	    # set budget_status to 'active'
        	    $budget_status_sql = 'UPDATE wp_usermeta 
        							  SET meta_value = replace(meta_value, "used_up", "active") 
        							  WHERE meta_key = "budget_status" 
        	    					      AND user_id ="'. $user_id .'" ;';
        
        	    # run budget_status query on db    
                mysql_query($budget_status_sql);
                echo 'Set budget_status for user '. $user_id .' to active';      	    
        	    echo PHP_EOL;
        	    
        	} else {
        
        	    $k = 0;
            
        	    while ($k < $num_posts) { 	
        	    
                    mysql_data_seek($posts_results, $k);
        	        $post_row = mysql_fetch_object($posts_results);	  
        	        
        	        # set post_status to 'pending'
                    $post_status_sql =   'UPDATE wp_posts 
        								  SET post_status = replace(post_status, "publish", "pending") 
        								  WHERE ID ="'. $post_row->ID .'"
        	    						      AND post_status = "publish";';
        
                    echo 'Set post '. $post_row->ID .' to publish';
                    echo PHP_EOL;
                    
                    mysql_query($post_status_sql);
                    $k++;
                    
        	    }
        	    
                # set budget_status to 'used_up'
        	    $budget_status_sql = 'UPDATE wp_usermeta 
        							  SET meta_value = replace(meta_value, "active", "used_up") 
        							  WHERE meta_key = "budget_status" 
        	    					      AND user_id ="'. $user_id .'" ;';
        
        	    # run budget_status and post_status queries on db    
                mysql_query($budget_status_sql);
                echo 'Set budget_status for user '. $user_id .' to used_up';
                echo PHP_EOL;
        
        	}
    	    
        	echo PHP_EOL; 
            echo '_______________________________________________________';
            echo PHP_EOL;        
        	
            $chargify_key =       '3FAaEvUO_ksasbblajon';
            $chargify_auth =      $chargify_key .':x';
            $chargify_auth_url =  'https://'. $chargify_auth .'green-pages.chargify.com/subscriptions/';
            echo PHP_EOL;
            
    	    $chargify_url = 'https://green-pages.chargify.com/subscriptions/' . $subscription_id . '/components/' . $component_id . '/usages.json';
            echo '$chargify_url: '. $chargify_url;
            echo PHP_EOL;
            
            $usage = '
                     { 
                         "usage":{ 
                             "quantity": '. $quantity .' 
    	                 } 
    	             }
    	             ';
            
            echo '$usage: '. $usage;
            echo PHP_EOL;
            # send billing data to url above using curl 
    
            if ($quantity != 0) {
                
                echo 'Sending data to chargify ...';   
                echo PHP_EOL;
    
                // Chargify api key: 3FAaEvUO_ksasbblajon
                // http://docs.chargify.com/api-authentication
                
                $ch = curl_init($chargify_auth_url);
                
                $array = array();
                array_push($array, 'Content-Type: application/json;', 'Accept: application/json;', 'charset=utf-8;');

                curl_setopt($ch, CURLOPT_HTTPHEADER, $array);
                curl_setopt($ch, CURLOPT_URL, $chargify_url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $usage);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
                curl_setopt($ch, CURLOPT_VERBOSE, true);
                curl_setopt($ch, CURLOPT_USERPWD, $chargify_auth);
                
                $result = curl_exec($ch);
                echo $result;   
                echo PHP_EOL;

                curl_close($ch);    
                            
                echo 'Data successfully sent to chargify and usage recorded!';   
                echo PHP_EOL;
                
            } else {
                
                echo 'No clicks, do not send data to chargify!';
                echo PHP_EOL;            
            
            }
    
        	echo PHP_EOL; 
            echo '_______________________________________________________';
            echo PHP_EOL;
    
    	} else {

    	    echo 'No $component_id found, no data sent to chargify.';
    	    echo PHP_EOL;
    	
    	}       
    
    } else {

        echo 'User: '. $user_id .' $budget_status: '. $user_row->budget_status;
        echo PHP_EOL;
        
    }
    
    echo PHP_EOL;
    echo '_______________________________________________________';
    echo PHP_EOL; 
    echo '_______________________________________________________';
    echo PHP_EOL;      
    
	$i++;	
}	

echo PHP_EOL; 
echo '_______________________________________________________';
echo PHP_EOL;
echo 'Ends';
echo PHP_EOL; 
echo '_______________________________________________________';
echo PHP_EOL;

mysql_close($db_connection);
exit();

?>
