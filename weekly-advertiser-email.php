<?php
/**
 * Weekly mail to CPC advertisers
 * Run this cron job once per day and sent to clients at their time of billing.
 * Connect to database, get user details from wp_usermeta
 * 
 * Authors:	 Katie Patrick & Jesse Browne
 *           kp@greenpag.es
 *           jb@greenpag.es
 * 
 **/

echo PHP_EOL; 
echo '_______________________________________________________';
echo PHP_EOL;
echo '_______________________________________________________';
echo PHP_EOL; 
echo 'Weekly Advertiser Email Cron Begins';
echo PHP_EOL; 
echo '_______________________________________________________';
echo PHP_EOL;
echo '_______________________________________________________';
echo PHP_EOL; 
echo PHP_EOL;

require '/var/www/production/www.greenpag.es/wordpress/wp-content/themes/gp-au-theme/ga/analytics.class.php';

mysql_connect("127.0.0.1", "s2-wordpress", "7BXmxPmwy4LJZNhR") or die(mysql_error());	
mysql_select_db("s2-wordpress") or die(mysql_error()); 
date_default_timezone_set('UTC');

function get_adv_signup_time($user_id) {

    echo 'get_adv_signup_time($user_id)';
    echo PHP_EOL;
    
    // Get time advertiser signed up to chargify
    $sql_adv_time = 'SELECT meta_value 
                     FROM wp_usermeta 
                     WHERE user_id = "'. $user_id .'"
                         AND meta_key = "adv_signup_time";';
    
    $signup_time_results = mysql_query($sql_adv_time);
    
	if (!$signup_time_results) {
    	echo('Database error: ' . mysql_error());
    	return;
	}
    
    mysql_data_seek($signup_time_results, 0);
    $signup_time_row = mysql_fetch_object($signup_time_results);	    
    $advertiser_signup_time = $signup_time_row->meta_value;

	return $advertiser_signup_time;

}

function get_budget_status($user_id) {

    echo 'get_budget_status($user_id)';
    echo PHP_EOL;
        
	$sql = 'SELECT meta_value
        	FROM   wp_usermeta
        	WHERE  user_ID = "'. $user_id .'"
        	    AND meta_key = "budget_status";';

	$budget_status_results = mysql_query($sql);
    
	if (!$budget_status_results) {
    	echo('Database error: ' . mysql_error());
    	return;
	}

    mysql_data_seek($budget_status_results, 0);
    $budget_status_row =  mysql_fetch_object($budget_status_results);	    
    $budget_status =      $budget_status_row->meta_value;	
	
    return $budget_status;   

}

function get_product_id($user_id) {

    echo 'get_product_id($user_id)';
    echo PHP_EOL;    

    // Get chargify product id    
    $sql_product_id  = 'SELECT meta_value 
                        FROM   wp_usermeta 
                        WHERE  user_id = "'. $user_id .'"
                             AND meta_key = "product_id";';
    
    $sql_product_id_results = mysql_query($sql_product_id);

	if (!$sql_product_id_results) {
    	echo('Database error: ' . mysql_error());
    	return;
	}
    
    mysql_data_seek($sql_product_id_results, 0);
    $product_id_row =  mysql_fetch_object($sql_product_id_results);	
    $product_id =      $product_id_row->meta_value;
    
    return $product_id;
}

function get_cost_per_click($product_id) {

    echo 'get_cost_per_click($product_id)';
    echo PHP_EOL; 
        
    switch ($product_id)   {
        case '3313295':
            // $12 per week plan
            $cpc = 1.9;
            break;
        case '27029':
            // $39 per week plan
            $cpc = 1.9;
            break;
        case '27028':
            // $99 per week plan
            $cpc = 1.9;
            break; 
        case '3313296':
            // $249 per week plan
            $cpc = 1.8;
            break; 
        case '3313297':
            // $499 per week plan
            $cpc = 1.7;
            break;                                                
    }
    return $cpc;   
}

function get_views_for_post($post_row, $user_id, $analytics, $start_range, $end_range) {

    echo 'get_views_for_post($post_row, $user_id, $analytics, $start_range, $end_range)';
    echo PHP_EOL; 
    
	$post_url_ext =   $post_row->post_name; //Need to get post_name for URL. Gets ful URl, but we only need /url extention for Google API
	$post_type_map =  'eco-friendly-products';
	$post_url_end =   '/' . $post_type_map . '/' . $post_url_ext . '/';

	$analytics->setDateRange($start_range, $end_range);	        //Set date in GA $analytics->setMonth(date('$post_date'), date('$new_date'));

  	$pageViewURL = ($analytics->getPageviewsURL($post_url_end));	//Page views for specific URL

  	$sumURL = 0;
  	foreach ($pageViewURL as $data) {
    	$sumURL = $sumURL + $data;
  	}
  	        
    return $sumURL;
}

function get_clicks_for_post($post_row, $user_id, $analytics, $start_range, $end_range) {

    echo 'get_clicks_for_post($post_row, $user_id, $analytics, $start_range, $end_range)';
    echo PHP_EOL;     
    
	$analytics->setDateRange($start_range, $end_range);	        //Set date in GA $analytics->setMonth(date('$post_date'), date('$new_date'));

   	#SET UP POST ID AND AUTHOR ID DATA, POST DATE, GET LINK CLICKS DATA FROM GA 
	$profile_author_id =  $user_id;
	$post_id =            $post_row->ID;
	$click_track_tag =    '/yoast-ga/' . $post_id . '/' . $profile_author_id . '/outbound-article/';

	$clickURL = ($analytics->getPageviewsURL($click_track_tag));
	$sumClick = 0;

	foreach ($clickURL as $data) {
   		$sumClick = $sumClick + $data;
	}

    // Get url product button is linked to
    $sql_product_url = 'SELECT meta_value 
                        FROM wp_postmeta 
                        WHERE post_id = "'. $post_id .'"
                            AND meta_key = "gp_advertorial_product_url";';

    $product_url_results =  mysql_query($sql_product_url);
    mysql_data_seek($product_url_results, 0);
    $product_url_row =      mysql_fetch_object($product_url_results);	
	$product_url =          $product_url_row->meta_value;

	if ( !empty($product_url) ) {		# IF 'BUY IT' BUTTON ACTIVATED, GET CLICKS
	    $click_track_tag_product_button = '/outbound/product-button/' . $post_id . '/' . $profile_author_id . '/' . $product_url . '/'; 	         
		$clickURL_product_button = ($analytics->getPageviewsURL($click_track_tag_product_button));
            
		foreach ($clickURL_product_button as $data) {
   			$sumClick = $sumClick + $data;
		}
	}
        
    return $sumClick;
}

function email_current_advertisers() {

    echo 'email_current_advertisers()';
    echo PHP_EOL;
    
	$sql = "SELECT DISTINCT user_ID
        	FROM wp_usermeta
        	WHERE meta_key = 'reg_advertiser'
              and user_ID = '3861';";

	$users = mysql_query($sql);

	if (!$users) {
    	echo('Database error: ' . mysql_error());
	}
	
	$data_set =  mysql_num_rows($users);
	$i =         0;
	
    while ($i < $data_set ) {

        mysql_data_seek($users, $i);
        
        $row =              mysql_fetch_object($users);
        $user_id =          $row->user_ID;
        $budget_status =    get_budget_status($user_id);
         
        if ($budget_status != 'cancelled') {

            $adv_signup_time =      get_adv_signup_time($user_id);
            
            $sql = "SELECT user_email, user_nicename, display_name
                    FROM wp_users
                    WHERE ID = '. $user_id .';";
    
            $reg_advertiser_results = mysql_query($sql);
            mysql_data_seek( $reg_advertiser_results, 0 );
            
            $reg_advertiser_row =   mysql_fetch_object($reg_advertiser_results);
            $member_display_name =  $reg_advertiser_row->display_name;
            $user_nicename =        $reg_advertiser_row->user_nicename;
            $user_email =           $reg_advertiser_row->user_email;
            
            $signup_day =           gmdate('l', $adv_signup_time);
            $today =                date('l'); //Day of week in lower case string
    
            if ($signup_day == $today) {
                $intro_sentence =   get_intro_sentence($user_id, $member_display_name);
                $email_body =       get_email_body($user_nicename, $budget_status);
                send_email_notification($user_email, $intro_sentence, $email_body);
            }
        
        }
        $i++;
    }
}

function get_intro_sentence($user_id, $member_display_name) {

    echo 'get_intro_sentence($user_id, $member_display_name)';
    echo PHP_EOL; 
        
    $analytics = new analytics('greenpagesadserving@gmail.com', 'greenpages01'); //sign in and grab profile			
    $analytics->setProfileById('ga:42443499');    

    # Get all product posts authored by user and store in $posts_results
    $sql_posts = 'SELECT DISTINCT wp_posts.* 
    			  FROM wp_posts 
    			  WHERE ( post_status = "publish"
        		          or post_status = "pending" ) 
       			  	and wp_posts.post_type = "gp_advertorial" 
       			  	and wp_posts.post_author = "'. $user_id .'";';

    $posts_results = mysql_query($sql_posts);
    $num_posts     = mysql_num_rows($posts_results);

    # Get all clicks for this users product posts
    # this variable needs to hold the total number of clicks that user will be billed for 

    $clicks_this_week =  0;
    $views_this_week  =  0;
    $j =                 0;

    while ($j < $num_posts) { 	

        mysql_data_seek($posts_results, $j);

    	$post_row =                 mysql_fetch_object($posts_results);
    	$now =                      time();
    	$today_date =               date('Y-m-d'); 		            //Todays Date
        $advertiser_signup_time =   get_adv_signup_time($user_id);

    	// Get difference between last week anniversary of sign up
    	$one_week =                 (7 * 24 * 60 * 60);
    	$now =                      time();
    	$total_time_signedup =      $now - $advertiser_signup_time;
    	$this_billing_week =        $total_time_signedup % $one_week;
    	$start_this_billing_week =  $now - $this_billing_week;    
    	$start_date_billing_week =  date('Y-m-d', $start_this_billing_week);
    	$sumClick_this_week =       get_clicks_for_post($post_row, $user_id, $analytics, $start_date_billing_week, $today_date);
        $sumView_this_week =        get_views_for_post($post_row, $user_id, $analytics, $start_date_billing_week, $today_date);
    	
    	$clicks_this_week  =        $clicks_this_week + $sumClick_this_week;
        $views_this_week  =         $views_this_week + $sumView_this_week;

       	$j++;
    }    

    // Get cost per click and calculate bill
    $product_id =        get_product_id($user_id);
    $cpc =               (float) get_cost_per_click($product_id);

	// Set analaytics variables
	$week_bill =         ( (int) $clicks_this_week ) * $cpc;
	$pretty_week_bill =  number_format($week_bill, 2);

	// Construct useful string and return
	$intro_sentence =    '<p>Hi '. $member_display_name .'! This week '. $views_this_week .' people viewed your post 
                          and '. $clicks_this_week .' people clicked through to your website from greenpag.es.</p>
                          <p>That means your bill this week was $'. $pretty_week_bill . '</p>';

	return $intro_sentence;

}

function get_email_body($user_nicename, $budget_status) {

    echo 'get_email_body($user_nicename, $budget_status)';
    echo PHP_EOL;     
    
    switch ($budget_status) {
        case 'used_up' :
            $email_body =     '<p>Wow your posts are popular! You\'re budget was reached this week and your product posts were 
                               hidden until the next billing cycle.</p>
                	           <p>Want to get more clicks?</p> 
                	           <p><a href="http://www.greenpag.es/profile/ '. $user_nicename .'/#tab:advertise">Increase your weekly budget now.</a></p>';
            break;
        case 'active' :
            $email_body =     '<p>Hey, you\'ve still got come budget left :)</p> <br /><br />
                               <p>Want to get more clicks? <a href="http://www.greenpag.es/forms/create-product-post">Create another product post now!</a></p>
                               <p>There\'s no limit to how many product posts you can create,
                               so go ahead, let the greenpages members know how excellent your business is!</p>';
            break;
	}
	
	return $email_body;
}

function send_email_notification($user_email, $intro_sentence, $email_body) {
    /**
     * Send email via mailgun
     **/

    echo 'send_email_notification($user_email, $intro_sentence, $email_body)';
    echo PHP_EOL;      
    
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, 'api:key-2848zj9zqy6vzlec3qy1hwber1tsy1i2');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v2/greenpag.es/messages');
    curl_setopt($ch, CURLOPT_POSTFIELDS, array( 'from' => 'hello@greenpag.es',
                                                //'to' => $user_email,
                                                'to' => 'info@thegreenpages.com.au',
                                                //'cc' => 'info@thegreenpages.com.au',
                                                'subject' => 'How many clicks did you receive this week from greenpag.es?',
                                                'text' => 'Some text',
                                                'html' => '<html>
                                                              '. $intro_sentence .'
                                                              '. $email_body .'
                                                           </html>' ) );
    $result = curl_exec($ch); 
    curl_close($ch);

    return $result;

}

// echo 'Here are some examples of some successful posts that helped get our customers more clients';
// if today is the day chargify bills by day of the week. If day of signup == day of the week.

email_current_advertisers();

echo PHP_EOL;
echo '_______________________________________________________';
echo PHP_EOL;
echo '_______________________________________________________';
echo PHP_EOL;
echo 'Weekly Advertiser Email Cron Ends';
echo PHP_EOL;
echo '_______________________________________________________';
echo PHP_EOL;
echo '_______________________________________________________';
echo PHP_EOL;
echo PHP_EOL;

exit();

?>
