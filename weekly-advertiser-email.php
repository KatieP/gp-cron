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
echo 'Begins';
echo PHP_EOL; 
echo '_______________________________________________________';
echo PHP_EOL;
echo '_______________________________________________________';
echo PHP_EOL; 
echo PHP_EOL;

function get_advertiser_ids() {
    
	mysql_connect("127.0.0.1", "s2-wordpress", "7BXmxPmwy4LJZNhR") or die(mysql_error());	
	mysql_select_db("s2-wordpress") or die(mysql_error());

	$sql = "SELECT DISTINCT user_ID
        	FROM wp_usermeta
        	WHERE meta_key = 'reg_advertiser';";

	$db_result = mysql_query($sql);

	if (!$db_result) {
    	echo('Database error: ' . mysql_error());
	}

	return $db_result;   

}

function get_adv_signup_time($user_id) {

    mysql_connect("127.0.0.1", "s2-wordpress", "7BXmxPmwy4LJZNhR") or die(mysql_error());	
	mysql_select_db("s2-wordpress") or die(mysql_error());

	$sql = 'SELECT meta_value
        	FROM   wp_usermeta
        	WHERE  user_ID = "'. $user_id .'"
        	    AND meta_key = "adv_signup_time";';

	$adv_signup_time = mysql_query($sql);

	if (!$adv_sign_up_time) {
    	echo('Database error: ' . mysql_error());
	}

	return $adv_signup_time;

}

function get_budget_status($user_id) {

    mysql_connect("127.0.0.1", "s2-wordpress", "7BXmxPmwy4LJZNhR") or die(mysql_error());	
	mysql_select_db("s2-wordpress") or die(mysql_error());

	$sql = 'SELECT meta_value
        	FROM   wp_usermeta
        	WHERE  user_ID = "'. $user_id .'"
        	    AND meta_key = "budget_status";';

	$budget_status = mysql_query($sql);

	if (!$budget_status) {
    	echo('Database error: ' . mysql_error());
	}

	return $budget_status;   

}

function process_advertisers() {

	$users =     get_advertiser_ids();
	$data_set =  mysql_num_rows($users);
	$i =         0;
	
    while ($i < $data_set ) {

        mysql_data_seek($users, $i);
        
        $row =              mysql_fetch_object($users);
        $user_id =          $row->user_ID;
        $adv_signup_time =  get_adv_signup_time($user_id);
        $budget_status =    get_budget_status($user_id);

        if ($budget_status != 'cancelled') {

            $sql = "SELECT user_email, user_nicename, display_name
                    FROM wp_users
                    WHERE ID = '. $user_id .';";
    
            $reg_advertiser_results = mysql_query($sql);
            mysql_data_seek( $reg_advertiser_results, 0 );
            
            $reg_advertiser_row =   mysql_fetch_object($reg_advertiser_results);
            $member_display_name =  $reg_advertiser_row->display_name;
            $user_nicename =        $reg_advertiser_row->user_nicename;
            $user_email =           $reg_advertiser_row->user_email;
            
            $signup_day =           gmdate("l", $adv_signup_time);
            $today =                date('l'); //Day of week in lower case string
    
            if ($signup_day == $today) {
                send_email_notification($user_email, $intro_sentence);
            }
        
        }
        $i++;
    }
}

function get_user_analytics($member_display_name) {

	// Set analaytics variables
	$week_impressions =  '';
	$week_clicks =       '';
	$week_bill =         '';

	// Construct useful string and return
	$analytics_string =  'Hi '. $member_display_name .'! This week '. $week_impressions .' people viewed your post 
                          and '. $week_clicks .' people clicked through to your website. <br /><br />
                          That means your bill this week was '. $week_bill;
	
	return $analytics_string;

}

function get_email_message($user_nicename, $budget_status) {

    switch ($budget_status) {
        case 'used_up' :
            $email_message =  'Wow your posts are popular! You\'re budget was reached this week and your product posts were 
                               hidden until the next billing cycle. <br /><br />
                	           Want to get more clicks? 
                	           <a href="http://www.greenpag.es/profile/ '. $user_nicename .'/#tab:advertise">Increase your weekly budget now.</a>';
            break;
        case 'active' :
            $email_message =  'Hey, you\'ve still got come budget left :) <br /><br />
                               Want to get more clicks? <a href="http://www.greenpag.es/forms/create-product-post">Create another product post now!</a>
                               There\'s no limit to how many product posts you can create,
                               so go ahead, let the greenpages members know how excellent your business is!';
            break;
	}
	
	return $email_message;
}

function send_email_notification($user_email, $intro_sentence) {
    /**
     * Send email via mailgun
     **/
    
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
                                                'html' => '<htlm>
                                                              '. $intro_sentence .'
                                                           </html>' ) );
    $result = curl_exec($ch); 
    curl_close($ch);

    return $result;

}

// echo 'Here are some examples of some successful posts that helped get our customers more clients';
// if today is the day chargify bills by day of the week. If day of signup == day of the week.

echo PHP_EOL;
echo '_______________________________________________________';
echo PHP_EOL;
echo '_______________________________________________________';
echo PHP_EOL;
echo 'Ends';
echo PHP_EOL;
echo '_______________________________________________________';
echo PHP_EOL;
echo '_______________________________________________________';
echo PHP_EOL;
echo PHP_EOL;

exit();

?>
