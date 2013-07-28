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

#require '../ga/analytics.class.php';
#require '../greenpag.es/gp-au-theme/ga/analytics.class.php';
#require '../gp-theme/gp-au-theme/ga/analytics.class.php';



$db_connection = mysql_connect("127.0.0.1", "s2-wordpress", "7BXmxPmwy4LJZNhR") or die(mysql_error());	
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

	var_dump($budget_status_results);
	
	if (!$budget_status_results) {
    	echo('Database error: ' . mysql_error());
    	return '';
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

function get_component_id($product_id) {
    /**
	 * Return component id mapped to product id
	 * for Chargify metered billing components
	 **/

    $component_map = array( '3313295'  => '3207',
							'27029'    => '3207',
							'27028'    => '3207',
							'3313296'  => '20016',
							'3313297'  => '20017',
							'27023'    => '' );

    $component_id = $component_map[$product_id];

    return $component_id;
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
    
	$sql = 'SELECT DISTINCT user_ID
        	FROM wp_usermeta
        	WHERE meta_key = "budget_status"
        	    AND (meta_value = "active"
        	         OR meta_value = "used_up");';

	$users =     mysql_query($sql);

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

        if ( ( $budget_status == 'active' ) || ( $budget_status == 'used_up' ) ) {

            $product_id =       get_product_id($user_id);
            $component_id =     get_component_id($product_id);
            
            if ( !empty($component_id) ) {
            
                $adv_signup_time =      get_adv_signup_time($user_id);
    
                $sql = 'SELECT user_email, user_nicename, display_name
                        FROM wp_users
                        WHERE ID = "'. $user_id .'";';
    
                $reg_advertiser_results = mysql_query($sql);
                mysql_data_seek( $reg_advertiser_results, 0 );
    
                $reg_advertiser_row =   mysql_fetch_object($reg_advertiser_results);
                $member_display_name =  $reg_advertiser_row->display_name;
                $user_nicename =        $reg_advertiser_row->user_nicename;
                $user_email =           $reg_advertiser_row->user_email;
    
                $signup_day =           gmdate('l', $adv_signup_time);
                $today =                date('l'); //Day of week in lower case string
    
                //if ($signup_day == $today) {
                    $intro_sentence =   get_intro_sentence($user_id, $member_display_name);
                    $email_body =       get_email_body($user_nicename, $budget_status);
                    send_email_notification($user_email, $intro_sentence, $email_body);
                //}
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
	$intro_sentence =    '<p>Hi '. $member_display_name .'!</p>
	                      <p>This week '. $views_this_week .' people viewed your post 
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
            $email_body =     '<p>Hey, you\'ve still got come budget left :)</p>
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
                                                'to' => $user_email,
                                                'cc' => 'jb@greenpag.es', 
                                                            
                                                'subject' => 'How many clicks did you receive this week from greenpag.es?',
                                                'text' => 'Some text',
                                                'html' => '
                                                              
                                                              
                                                              
    <!--  / ---------------------------------------------------------------------------------------------- / -->
                                                              
                                                              
     <!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><head><title></title><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><meta name="viewport" content="width=320, target-densitydpi=device-dpi">
<style type="text/css">
/* Mobile-specific Styles */
@media only screen and (max-width: 660px) { 
table[class=w0], td[class=w0] { width: 0 !important; }
table[class=w10], td[class=w10], img[class=w10] { width:10px !important; }
table[class=w15], td[class=w15], img[class=w15] { width:5px !important; }
table[class=w30], td[class=w30], img[class=w30] { width:10px !important; }
table[class=w60], td[class=w60], img[class=w60] { width:10px !important; }
table[class=w125], td[class=w125], img[class=w125] { width:80px !important; }
table[class=w130], td[class=w130], img[class=w130] { width:55px !important; }
table[class=w140], td[class=w140], img[class=w140] { width:90px !important; }
table[class=w160], td[class=w160], img[class=w160] { width:180px !important; }
table[class=w170], td[class=w170], img[class=w170] { width:100px !important; }
table[class=w180], td[class=w180], img[class=w180] { width:80px !important; }
table[class=w195], td[class=w195], img[class=w195] { width:80px !important; }
table[class=w220], td[class=w220], img[class=w220] { width:80px !important; }
table[class=w240], td[class=w240], img[class=w240] { width:180px !important; }
table[class=w255], td[class=w255], img[class=w255] { width:185px !important; }
table[class=w275], td[class=w275], img[class=w275] { width:135px !important; }
table[class=w280], td[class=w280], img[class=w280] { width:135px !important; }
table[class=w300], td[class=w300], img[class=w300] { width:140px !important; }
table[class=w325], td[class=w325], img[class=w325] { width:95px !important; }
table[class=w360], td[class=w360], img[class=w360] { width:140px !important; }
table[class=w410], td[class=w410], img[class=w410] { width:180px !important; }
table[class=w470], td[class=w470], img[class=w470] { width:200px !important; }
table[class=w580], td[class=w580], img[class=w580] { width:280px !important; }
table[class=w640], td[class=w640], img[class=w640] { width:300px !important; }
table[class*=hide], td[class*=hide], img[class*=hide], p[class*=hide], span[class*=hide] { display:none !important; }
table[class=h0], td[class=h0] { height: 0 !important; }
p[class=footer-content-left] { text-align: center !important; }
#headline p { font-size: 30px !important; }
.article-content, #left-sidebar{ -webkit-text-size-adjust: 90% !important; -ms-text-size-adjust: 90% !important; }
.header-content, .footer-content-left {-webkit-text-size-adjust: 80% !important; -ms-text-size-adjust: 80% !important;}
img { height: auto; line-height: 100%;}
 } 
/* Client-specific Styles */
#outlook a { padding: 0; }	/* Force Outlook to provide a "view in browser" button. */
body { width: 100% !important; }
.ReadMsgBody { width: 100%; }
.ExternalClass { width: 100%; display:block !important; } /* Force Hotmail to display emails at full width */
/* Reset Styles */
/* Add 100px so mobile switch bar doesn\'t cover street address. */
body { background-color: #ececec; margin: 0; padding: 0; }
img { outline: none; text-decoration: none; display: block;}
br, strong br, b br, em br, i br { line-height:100%; }
h1, h2, h3, h4, h5, h6 { line-height: 100% !important; -webkit-font-smoothing: antialiased; }
h1 a, h2 a, h3 a, h4 a, h5 a, h6 a { color: blue !important; }
h1 a:active, h2 a:active,  h3 a:active, h4 a:active, h5 a:active, h6 a:active {	color: red !important; }
/* Preferably not the same color as the normal header link color.  There is limited support for psuedo classes in email clients, this was added just for good measure. */
h1 a:visited, h2 a:visited,  h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited { color: purple !important; }
/* Preferably not the same color as the normal header link color. There is limited support for psuedo classes in email clients, this was added just for good measure. */  
table td, table tr { border-collapse: collapse; }
.yshortcuts, .yshortcuts a, .yshortcuts a:link,.yshortcuts a:visited, .yshortcuts a:hover, .yshortcuts a span {
color: black; text-decoration: none !important; border-bottom: none !important; background: none !important;
}	/* Body text color for the New Yahoo.  This example sets the font of Yahoo\'s Shortcuts to black. */
/* This most probably won\'t work in all email clients. Don\'t include code blocks in email. */
code {
  white-space: normal;
  word-break: break-all;
}
#background-table { background-color: #ececec; }
/* Webkit Elements */
#top-bar { border-radius:6px 6px 0px 0px; -moz-border-radius: 6px 6px 0px 0px; -webkit-border-radius:6px 6px 0px 0px; -webkit-font-smoothing: antialiased; background-color: #61c201; color: #61c201; }
#top-bar a { font-weight: bold; color: #61c201; text-decoration: none;}
#footer { border-radius:0px 0px 6px 6px; -moz-border-radius: 0px 0px 6px 6px; -webkit-border-radius:0px 0px 6px 6px; -webkit-font-smoothing: antialiased; }
/* Fonts and Content */
body, td { font-family: Helvetica Neue, Arial, Helvetica, Geneva, sans-serif; }
.header-content, .footer-content-left, .footer-content-right { -webkit-text-size-adjust: none; -ms-text-size-adjust: none; }
/* Prevent Webkit and Windows Mobile platforms from changing default font sizes on header and footer. */
.header-content { font-size: 12px; font-weight: bold; color: white; }
.header-content a { font-weight: bold; color: white; text-decoration: none; }
#headline p { color: #01aed8; font-family: Helvetica Neue, Arial, Helvetica, Geneva, sans-serif; font-size: 36px; text-align: left; margin-top:0px; margin-bottom:30px; }
#headline p a { color: #01aed8; text-decoration: none; }
.article-title { font-size: 18px; line-height:24px; color: #01aed8; font-weight:bold; margin-top:0px; margin-bottom:18px; font-family: Helvetica Neue, Arial, Helvetica, Geneva, sans-serif; }
.article-title a { color: #01aed8; text-decoration: none; }
.article-title.with-meta {margin-bottom: 0;}
.article-meta { font-size: 13px; line-height: 20px; color: #ccc; font-weight: bold; margin-top: 0;}
.article-content { font-size: 18px; line-height: 24px; color: #444444; margin-top: 0px; margin-bottom: 18px; font-family: Helvetica Neue, Arial, Helvetica, Geneva, sans-serif; }
.article-content a { color: #01aed8; font-weight:bold; text-decoration:none; }
.article-content img { max-width: 100% }
.article-content ol, .article-content ul { margin-top:0px; margin-bottom:18px; margin-left:19px; padding:0; }
.article-content li { font-size: 13px; line-height: 18px; color: #444444; }
.article-content li a { color: #01aed8; text-decoration:underline; }
.article-content p {margin-bottom: 15px;}
.footer-content-left { font-size: 12px; line-height: 15px; color: #e2e2e2; margin-top: 0px; margin-bottom: 15px; }
.footer-content-left a { color: #E2E2E2; font-weight: bold; text-decoration: none; }
.footer-content-right { font-size: 11px; line-height: 16px; color: #e2e2e2; margin-top: 0px; margin-bottom: 15px; }
.footer-content-right a { color: #E2E2E2; font-weight: bold; text-decoration: none; }
#footer { background-color: #61c201; color: #e2e2e2; }
#footer a { color: #E2E2E2; text-decoration: none; font-weight: bold; }
#permission-reminder { white-space: normal; }
#street-address { color: #E2E2E2; white-space: normal; }
</style>
<!--[if gte mso 9]>
<style _tmplitem="40" >
.article-content ol, .article-content ul {
   margin: 0 0 0 24px;
   padding: 0;
   list-style-position: inside;
}
</style>
<![endif]--></head><body><table width="100%" cellpadding="0" cellspacing="0" border="0" id="background-table">
	<tbody><tr>
		<td align="center" bgcolor="#ececec">
        	<table class="w640" style="margin:0 10px;" width="640" cellpadding="0" cellspacing="0" border="0">
            	<tbody><tr><td class="w640" width="640" height="20"></td></tr>
                
            	<tr>
                	<td class="w640" width="640">
                        <table id="top-bar" class="w640" width="640" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff">
    <tbody><tr>
        <td class="w15" width="15"></td>
        <td class="w325" width="350" valign="middle" align="left">
            <table class="w325" width="350" cellpadding="0" cellspacing="0" border="0">
                <tbody><tr><td class="w325" width="350" height="8"></td></tr>
            </tbody></table>
            
            <div class="header-content"><webversion>&nbsp;&nbsp;<a href="http://www.greenpag.es">Weekly advertiser update from greenpag.es</a></webversion><span class="hide">&nbsp; </span><div>
            
            <table class="w325" width="350" cellpadding="0" cellspacing="0" border="0">
                <tbody><tr><td class="w325" width="350" height="8"></td></tr>
            </tbody></table>
        </td>
        <td class="w30" width="30"></td>
        <td class="w255" width="255" valign="middle" align="right">
            <table class="w255" width="255" cellpadding="0" cellspacing="0" border="0">
                <tbody><tr><td class="w255" width="255" height="8"></td></tr>
            </tbody></table>
            <table cellpadding="0" cellspacing="0" border="0">
    <tbody><tr>
        
        
        
    </tr>
</tbody></table>
            <table class="w255" width="255" cellpadding="0" cellspacing="0" border="0">
                <tbody><tr><td class="w255" width="255" height="8"></td></tr>
            </tbody></table>
        </td>
        <td class="w15" width="15"></td>
    </tr>
</tbody></table>
                        
                    </td>
                </tr>
                <tr>
                <td id="header" class="w640" width="640" align="center" bgcolor="#ffffff">
    
    <table class="w640" width="640" cellpadding="0" cellspacing="0" border="0">
    </table>
    
    
</td>
                </tr>
                
                <tr><td class="w640" width="640" height="30" bgcolor="#ffffff"></td></tr>
                <tr id="simple-content-row"><td class="w640" width="640" bgcolor="#ffffff">
    <table class="w640" width="640" cellpadding="0" cellspacing="0" border="0">
        <tbody><tr>
            <td class="w30" width="30"></td>
            <td class="w580" width="580">
                <repeater>
                    
                    <layout label="Text only">
                        <table class="w580" width="580" cellpadding="0" cellspacing="0" border="0">
                            <tbody><tr>
                                <td class="w580" width="580">
                                    
                                    <div align="left" class="article-content">
                                    
                                    	<multiline label="Description">'. $intro_sentence .'</multiline>
                                    
                                        <multiline label="Description">'. $email_body .'</multiline>
                                    </div>
                                </td>
                            </tr>
                            <tr><td class="w580" width="580" height="10"></td></tr>
                        </tbody></table>
                    </layout>
                 
                 
                 
         </repeater>
            </td>
            <td class="w30" width="30"></td>
        </tr>
    </tbody></table>
</td></tr>
                <tr><td class="w640" width="640" height="15" bgcolor="#ffffff"></td></tr>
                
                <tr>
                <td class="w640" width="640">
    <table id="footer" class="w640" width="640" cellpadding="0" cellspacing="0" border="0" bgcolor="#61c201">
        <tbody><tr><td class="w30" width="30"></td><td class="w580 h0" width="360" height="30"></td><td class="w0" width="60"></td><td class="w0" width="160"></td><td class="w30" width="30"></td></tr>
        <tr>
            <td class="w30" width="30"></td>
            <td class="w580" width="360" valign="top">
            <span class="hide"><p id="permission-reminder" align="left" class="footer-content-left"><span>You\'re receiving this because you are subscribed a pay per click advertising campaign on greenpag.es. </span></p></span>
            <p align="left" class="footer-content-left"><unsubscribe><a href="http://www.greenpag.es/profile/'. $user_nicename .'#tab:advertise">Go to My Account</a></unsubscribe></p>
            </td>
            <td class="hide w0" width="60"></td>
            <td class="hide w0" width="160" valign="top">
            <p id="street-address" align="right" class="footer-content-right"></p>
            </td>
            <td class="w30" width="30"></td>
        </tr>
        <tr><td class="w30" width="30"></td><td class="w580 h0" width="360" height="15"></td><td class="w0" width="60"></td><td class="w0" width="160"></td><td class="w30" width="30"></td></tr>
    </tbody></table>
</td>
                </tr>
                <tr><td class="w640" width="640" height="60"></td></tr>
            </tbody></table>
        </td>
	</tr>
</tbody></table></body>
                                                         
                                                                                                              
                                                              
     <!--  / ---------------------------------------------------------------------------------------------- / -->                                                         
                                                              
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

mysql_close($db_connection);
exit();

?>
