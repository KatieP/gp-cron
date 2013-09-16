<?php 

// Account Info:
//   Green Pages
//   Katie <katie.patrick@thegreenpages.com.au>
//   Standard plan

// API URL : https://api.mailgun.net/v2
// API Key : key-2848zj9zqy6vzlec3qy1hwber1tsy1i2

// Hostname : smtp.mailgun.org
// Login    : postmaster@greenpag.es
// Password : 2cm7dvnrkqw8
// Green Middle #67B832
// Green Lower #5EB723

//GET VARIABLES FROM DATABASE

//Connect to database s1-wordpress

$db_connection = mysql_connect("127.0.0.1", "s2-wordpress", "7BXmxPmwy4LJZNhR") or die(mysql_error());
//$db_connection = mysql_connect("127.0.0.1", "root", "") or die(mysql_error());

mysql_select_db("s2-wordpress") or die(mysql_error());
//mysql_select_db("s1-wordpress") or die(mysql_error());

require '/var/www/production/www.greenpag.es/wordpress/wp-content/plugins/gp-theme/core/gp-functions.php';

date_default_timezone_set('UTC');

echo '_______________________________________________________';
echo PHP_EOL; 
echo 'Begins';
echo PHP_EOL; 
echo '_______________________________________________________';
echo PHP_EOL;
	
function get_post_url($row) {
    /**
	 * Assigns words that are used in url and makes a full url for the post 
	 */
   	$post_type =     $row->post_type;
   	$post_name =     $row->post_name;
   	$post_type_map = array( "gp_news" => "news", 
                            "gp_advertorial" => "eco-friendly-products", 
                            "projects" => "projects");
     	 					
    $post_url = "http://www.greenpag.es/" . $post_type_map[$post_type] . "/" . $post_name;
	return $post_url;

}

function get_post_image($row) {

	$post_content = $row->post_content;
	$pattern =      'src';
	$image_url_img = '';

	// Extract all 'words' beggining with 'src=' and end with .jpg, .png or .gif from $post_content and store as image url variable
	if (preg_match("/(src.*)(jpg)/", $post_content, $matches)){
	    $image_url = $matches[0];
	} else {
	    $image_url = '';
	}
	
	if ($row->_thumbnail_id != NULL && $row->post_type == 'gp_news' && !empty($image_url) ) {
	    $upload_url =    'http://www.greenpag.es/wp-content/uploads';
	    $upload_year =   substr($row->post_date, 0, 4);
	    $upload_month =  substr($row->post_date, 5, 2);
	    
	    if ( !empty($image_url) ) {
	        $f_name =     strrchr($image_url, '/');
	        $dot_pos =    strpos($f_name, '.');
	        $s_f_name =   substr($f_name, 1, $dot_pos - 1);
	        $url =        $upload_url . '/' . $upload_year . '/' . $upload_month . '/' . $s_f_name . '-110x110.jpg';
	        $image_url =  'src="' . $url . '"';
	    }
	} elseif ( empty($image_url) && ($row->_thumbnail_id != NULL) ) {
	    // Get url for featured image thumbnail from db
	    echo PHP_EOL;
	    echo 'Get url for featured image thumbnail from db';
	    echo PHP_EOL;
	    
	    $db_img_result =  get_featured_image_urls_from_db($row->post_author);
            $data_set =       mysql_num_rows($db_img_result);
            $i =              0;
            $post_date_tr =   substr($row->post_date, 0, 14);
            
	    echo PHP_EOL;
	    echo 'Post_date: '. $post_date_tr;
	    echo PHP_EOL;
            
	    if ($data_set != FALSE) {
                while ($i <= $data_set) {
            	    mysql_data_seek($db_img_result, $i);
        	    $new_row =           mysql_fetch_object($db_img_result);
        	    $new_post_date_tr =  substr($new_row->post_date, 0, 14);       		
        	    if ($post_date_tr == $new_post_date_tr) {
			
		        echo PHP_EOL;
	                echo 'Image post_date: '. $new_post_date_tr;
	                echo PHP_EOL;
			
            	        $file_type =      substr($new_row->guid, -4);
            		$len =            strlen($new_row->guid);
            		$f_i_name =       substr($new_row->guid, 0, $len - 4);
            		$s_f_i_name =     $f_i_name . '-110x110' . $file_type;
            		$image_url_img =  'img src="' . $s_f_i_name .'"';
			break;
        	    }
        	    $i++;
                }
            }
	} elseif ( empty($image_url) ) {
		// If image src is not found, then randomly show a cool image
		$random_images = array();
		$random_images = get_random_images();
		$rand_keys =     array_rand($random_images, 2);
		$image_url_img = 'img src='. $random_images[$rand_keys[0]];		
        } else {
            $image_url_img = 'img '. $image_url;
        }
    
        
	    echo PHP_EOL;
	    echo 'Post type: '. $row->post_type;
	    echo PHP_EOL;
	    echo $row->post_title . ' Final else $image_url_img:';
	    echo PHP_EOL;
	    var_dump($image_url_img);
	    echo PHP_EOL;
        

        return $image_url_img;
}

function strip_non_utf_chars($string) {
    /**
     * Remove non utf-8 characters from a string
     * returns clean string, thanks stackoverflow!
     */
    
    $clean_string = preg_replace('/[^(\x20-\x7F)]*/', '', $string);

    return $clean_string;
}

function get_heading($heading) {
    
    $title        = '<!-- HEADING -->
                     <table class="w580" width="580" cellpadding="0" cellspacing="0" border="0">
                         <tbody>
                             <tr style="border-collapse:collapse;">
                                 <td class="w580" width="580" style="border-collapse:collapse;">
                                     <p align="left" class="article-title" style="font-size:18px;line-height:24px;color:#787878;font-weight:bold;margin-top:0px;margin-bottom:8px;font-family:Arial, Helvetica, sans-serif;">
                                         ' . $heading . '
                                     </p>
                                 </td>
                             </tr>
                             <tr style="border-collapse:collapse;">
                                 <td class="w580" width="580" height="10" style="border-collapse:collapse;"></td>
                             </tr>
                         </tbody>
                     </table>';

    return $title;    
}

function get_single_event($row, $show_country = false) {
	// Variables used in content of email	
	$post_title =    strip_non_utf_chars($row->post_title);
	$post_name =     $row->post_name;
	$post_ID =       $row->ID;
	$post_url =      get_post_url($row);
	
	$post_locality = $row->gp_google_geo_locality;
	$post_country  = $row->gp_google_geo_country;
	
    $country_map =          get_country_map();
	$country_pretty_name =  $country_map[$post_country];	
	$display_location =     ($show_country == false) ? $post_locality : $post_locality . ', ' . $country_pretty_name;
	
	$displayday =           date('j', $row->gp_events_startdate) . date('S', $row->gp_events_startdate);
	$displaymonth =         date('F', $row->gp_events_startdate);
	$displayyear =          date('y', $row->gp_events_startdate);
	$displaydate =          $displayday . ' ' . $displaymonth;
	
	if (!empty($post_title)) {
	    $single_event = '<!-- EVENT STARTS -->
                         <table class="w580" width="580" cellpadding="0" cellspacing="0" border="0">
                             <tbody>
                                 <tr style="border-collapse:collapse;">
                                     <td class="w580" width="580" style="border-collapse:collapse;">
                                         <p align="left" class="article-title" style="font-size:18px;line-height:24px;color:#787878;font-weight:bold;margin-top:0px;margin-bottom:8px;font-family:Arial, Helvetica, sans-serif;">
                                             <!--HEADER LINK TO ARTICLE -->
                                             <a href="'. $post_url .'" style="color:#01aed8;text-decoration:none;">
                                                 <!--ARTICLE TITLE-->
                                                 ' . $post_title . '
                                             </a> 
                                             <br /> ' . $display_location . ' - ' . $displaydate . '
                                         </p>
                                     </td>
                                 </tr>
                                 <tr style="border-collapse:collapse;">
                                     <td class="w580" width="580" height="10" style="border-collapse:collapse;"></td>
                                 </tr>
                             </tbody>
                         </table>
                         <!-- EVENT ENDS -->';	
    } else {
	    $single_event = '';
	}

	mb_convert_encoding($single_event, 'UTF-8');
	
	return $single_event;	
}

function get_single_post($row) {
	// Variables used in content of email	
	$post_title =    strip_non_utf_chars($row->post_title);
	$post_name =     $row->post_name;
	$raw_content =   strip_tags($row->post_content);
	$content =       strip_non_utf_chars($raw_content);
	$post_content =  substr($content, 0, 160);
	$post_ID =       $row->ID;
	$post_url =      get_post_url($row);
	$post_image =    get_post_image($row);

	if (!empty($post_title)) {
	    $single_post = '<!-- STORY STARTS -->
                        <table class="w580" width="580" cellpadding="0" cellspacing="0" border="0">
                            <tbody>
                                <tr style="border-collapse:collapse;">
                                    <td class="w580" width="580" style="border-collapse:collapse;">
                                        <p align="left" class="article-title" style="font-size:18px;line-height:24px;color:#787878;font-weight:bold;margin-top:0px;margin-bottom:8px;font-family:Arial, Helvetica, sans-serif;"><!--HEADER LINK TO ARTICLE --><a href="'. $post_url .'" style="color:#01aed8;text-decoration:none;"><!--ARTICLE TITLE-->' . $post_title . '</a></p><hr style="padding-top:0px;padding-bottom:0px;padding-right:0px;padding-left:0px;margin-top:0px;margin-bottom:10px;margin-right:0;margin-left:0;">
                                        <table cellpadding="0" cellspacing="0" border="0" align="left">
                                            <tbody>
                                                <tr style="border-collapse:collapse;">
                                                    <td style="border-collapse:collapse;">
                                                        <a href="'. $post_url .'">
                                                            <!--IMAGE LINK-->
                                                            <img label="Image" class="w110" width="110" border="0" '. $post_image .' height="110" style="height:auto;line-height:100%;outline-style:none;text-decoration:none;display:block;">
                                                            <!--IMAGE HTTP://-->
                                                        </a>
                                                    </td>
                                                    <td class="w30" width="15" style="border-collapse:collapse;"></td>
                                                </tr>
                                                <tr style="border-collapse:collapse;"><td style="border-collapse:collapse;"></td><td class="w30" width="15" height="5" style="border-collapse:collapse;"></td></tr>
                                            </tbody>
                                        </table>
                                        <div align="left" class="article-content" style="font-size:13px;line-height:18px;color:#444444;margin-top:0px;margin-bottom:18px;font-family: Arial, Helvetica, sans-serif;">   
                                        <p style="margin-bottom:15px;">
                                            <!--BODY TEXT--> '. $post_content .'...
                                            <!--LEARN MORE LINK TO ARTICLE --><a href="'. $post_url .'" style="color:#01aed8;font-weight:bold;text-decoration:none;">  Learn more</a>
                                        </p>
                                        <p style="margin-bottom:15px;">
	                                        <a href="/t/r-fb-ojylyjt-eidkjkly-xh/?act=wv" 
	                                           likeurl="'. $post_url .'" 
	                                           rel="cs_facebox" 
	                                           style="color:#01aed8;font-weight:bold;text-decoration:none;" 
	                                           cs_likeurl="/t/r-fb-ojylyjt-eidkjkly-xh/?act=like">
	                                            <img src="https://img.createsend1.com/img/social/fblike.png" border="0" 
	                                                 title="Like this on Facebook" alt="Facebook Like Button" width="51" height="20" 
	                                                 style="height:auto;line-height:100%;outline-style:none;text-decoration:none;display:block;max-width:100%;">
	                                        </a>
	                                    </p>
                                    </div>
                                </td>
                            </tr>
                            <tr style="border-collapse:collapse;"><td class="w580" width="580" height="10" style="border-collapse:collapse;"></td></tr>
                        </tbody></table>
                        <!-- STORY ENDS -->';	
    } else {
	    $single_post = '';
	}

	mb_convert_encoding($single_post, 'UTF-8');
	
	return $single_post;	
}

function get_featured_image_urls_from_db($author_id) {

	$sql = "SELECT post_date, post_author, post_title, guid
       		FROM wp_posts
	        WHERE post_modified > DATE_SUB(CURDATE(), INTERVAL 3 WEEK)
		    AND post_author = '" . $author_id . "'
	            AND post_type = 'attachment'
       		    AND post_status = 'inherit'";

	$db_result = mysql_query($sql);

	if (! $db_result){
	   echo('Database error: ' . mysql_error());
	}

	return $db_result;

}

function get_posts_from_db($post_type) {

	$sql = "SELECT wp_posts.*,
	            m0.meta_value AS _thumbnail_id
       		FROM wp_posts 
	            LEFT JOIN wp_postmeta AS m0 on m0.post_id=wp_posts.ID and m0.meta_key='_thumbnail_id'
	        WHERE post_modified > DATE_SUB(CURDATE(), INTERVAL 3 WEEK) 
	            AND post_type = '". $post_type ."'
       		    AND post_status = 'publish'";

	$db_result = mysql_query($sql);

	if (! $db_result){
	   echo('Database error: ' . mysql_error());
	}
		
	return $db_result;
}

function get_events_from_db($filterby_country = '', $filterby_state = '', $filterby_city = '', $max_results = '', $offset_num = '') {

    $epochtime =  strtotime('now');
    $limit_by =   (!empty($max_results)) ? 'LIMIT '. $max_results : '';
    $offset =     (!empty($offset_num))  ? 'OFFSET '. $offset_num : '';
    
	$sql = "SELECT DISTINCT wp_posts.*,
	            m0.meta_value AS _thumbnail_id,
	            m1.meta_value AS gp_events_enddate,
	            m2.meta_value AS gp_events_startdate,
	            m3.meta_value AS gp_google_geo_country,
	            m4.meta_value AS gp_google_geo_administrative_area_level_1,
	            m5.meta_value AS gp_google_geo_locality_slug,
	            m6.meta_value AS gp_google_geo_locality
       		FROM wp_posts 
	            LEFT JOIN wp_postmeta AS m0 on m0.post_id=wp_posts.ID and m0.meta_key='_thumbnail_id'
                LEFT JOIN wp_postmeta AS m1 on m1.post_id=wp_posts.ID and m1.meta_key='gp_events_enddate'
                LEFT JOIN wp_postmeta AS m2 on m2.post_id=wp_posts.ID and m2.meta_key='gp_events_startdate'
                LEFT JOIN wp_postmeta AS m3 on m3.post_id=wp_posts.ID and m3.meta_key='gp_google_geo_country'
                LEFT JOIN wp_postmeta AS m4 on m4.post_id=wp_posts.ID and m4.meta_key='gp_google_geo_administrative_area_level_1'
                LEFT JOIN wp_postmeta AS m5 on m5.post_id=wp_posts.ID and m5.meta_key='gp_google_geo_locality_slug'
                LEFT JOIN wp_postmeta AS m6 on m6.post_id=wp_posts.ID and m6.meta_key='gp_google_geo_locality'
	        WHERE post_type = 'gp_events'
       		    AND post_status = 'publish'
                " . $filterby_country . "
                " . $filterby_state . "
                " . $filterby_city . "       		    
	            AND CAST(CAST(m1.meta_value AS UNSIGNED) AS SIGNED) >= ". $epochtime ."
	            ORDER BY gp_events_startdate ASC ".
    	        $limit_by .
    	        $max_results . 
    	        $offset .";";

	$db_result = mysql_query($sql);

	if (! $db_result){
	   echo('Database error: ' . mysql_error());
	}

	return $db_result;
}

function get_users() {

	//Get user emails and their location
	$sql_user = 'SELECT DISTINCT user_email, display_name, ID
                     FROM   wp_users
                     WHERE  ID = "3" OR
		            ID = "7"';

	$db_result = mysql_query($sql_user);

	if (! $db_result){
	   echo('Database error: ' . mysql_error());
	}
		
	return $db_result;
}

function get_user_lat_long($user_id) {

	$sql = 'SELECT  meta_key, meta_value
            FROM    wp_usermeta
            WHERE   user_id = "'. $user_id .'"
                AND ( meta_key = "gp_google_geo_latitude" 
                      OR meta_key = "gp_google_geo_longitude" )';

    $db_result = mysql_query($sql);

    if (!$db_result){
       echo('Database error: ' . mysql_error());
    }
                
    return $db_result;
}

function get_all_user_location_data($user_id) {
    
    $sql = 'SELECT meta_key, meta_value
            FROM  wp_usermeta 
            WHERE user_id = '. $user_id .'
            AND (
                meta_key =     "gp_google_geo_location"
                OR meta_key =  "gp_google_geo_latitude"
                OR meta_key =  "gp_google_geo_longitude"
                OR meta_key =  "gp_google_geo_country"
                OR meta_key =  "gp_google_geo_administrative_area_level_1"
                OR meta_key =  "gp_google_geo_administrative_area_level_2"
                OR meta_key =  "gp_google_geo_administrative_area_level_3"
                OR meta_key =  "gp_google_geo_locality"
                OR meta_key =  "gp_google_geo_locality_slug"
            )';

    $db_result = mysql_query($sql);

    if (!$db_result){
       echo('Database error: ' . mysql_error());
    }

    return $db_result;
    
}

function get_user_notification_setting($user_id) {

	$sql = 'SELECT  meta_value
            FROM    wp_usermeta
            WHERE   user_id = "'. $user_id .'"
                AND meta_key = "notification_setting";';

    $db_result = mysql_query($sql);
    
    if (!$db_result){
       echo('Database error: ' . mysql_error());
    }

    mysql_data_seek($db_result, 0);
    $notification_setting_row = mysql_fetch_object($db_result);
    $notification_setting =     $notification_setting_row->meta_value;
    
    return $notification_setting;
}

function get_events($user_id) {
    
    $user_location_data = get_all_user_location_data($user_id);
    // need lat, long, country, city for event queries
    
    $data_set =  mysql_num_rows($user_location_data);
    
    $i = 0;
    
	while ($i < $data_set) {   

	    mysql_data_seek($user_location_data, $i);
        $user_location_row = mysql_fetch_object($user_location_data);
        $meta_key =          $user_location_row->meta_key;
        $meta_value =        $user_location_row->meta_value;    

	    switch ($meta_key) {
            case 'gp_google_geo_location':
                $user_location = $meta_value;
                break;	            
            case 'gp_google_geo_latitude':
                $user_lat = $meta_value;
                break;
            case 'gp_google_geo_longitude':
                $user_long = $meta_value;
                break;
            case 'gp_google_geo_country':
                $user_country = $meta_value;
                break;
            case 'gp_google_geo_administrative_area_level_1':
                $user_location_state = $meta_value;
                break;
            case 'gp_google_geo_administrative_area_level_2':
                $user_admin_2 = $meta_value;
                break;
            case 'gp_google_geo_administrative_area_level_3':
                $user_admin_3 = $meta_value;
                break;
            case 'gp_google_geo_locality':
                $user_location_city = $meta_value;
                break;
            case 'gp_google_geo_locality_slug':
                $user_location_country_slug = $meta_value;
                break;                  
        }
        
        $i++;
        
	}
	
	// set location query strings based on user location
	
	$querystring_country =    ( !empty( $user_country ) )               ? $user_country                             : 'AU';
        $querystring_state =      ( !empty( $user_location_state ) )        ? strtoupper( $user_location_state )        : 'NSW';
	$querystring_city =       ( !empty( $user_location_city ) )         ? $user_location_city                       : 'Sydney';
	
	$filterby_country =       ( !empty($querystring_country) ) ? ' AND m3.meta_value ="'. $querystring_country .'"' : '';
    $filterby_state =         ( !empty($querystring_state) )   ? ' AND m4.meta_value ="'. $querystring_state .'"'  : '';
    $filterby_city =          ( !empty($querystring_city) )    ? ' AND m6.meta_value ="'. $querystring_city .'"'   : '';
	
   	$db_result = get_events_from_db($filterby_country, $filterby_state, $filterby_city);
   	
    if (!$db_result){
       echo PHP_EOL;
       echo('Database error: ' . mysql_error());
       echo PHP_EOL;
    }
   	
    $data_set =  mysql_num_rows($db_result);

    $i = 0;
    $events_set = '';
    $hr = '<hr style="padding-top:0px;padding-bottom:0px;padding-right:0px;padding-left:0px;margin-top:0px;margin-bottom:10px;margin-right:0;margin-left:0;"> ';

	while ($i < $data_set) {

        mysql_data_seek($db_result, $i);
        $row =         mysql_fetch_object($db_result);
        $event =       get_single_event($row);
        
        if ( ($i == 0) && (!empty($event)) ) {
            $event_set .=    '<br />';
	    $event_set .=    $hr;
            $events_title =  get_heading('Events in ' . $querystring_city);
            $event_set .=    $events_title . '<br />';
            $event_set .=    $hr;
            $event_set .=    $event . '<br />';
        } elseif (!empty($event)) {
            $event_set .=    $event . '<br />';
        }

        $i++;

	}
	
	$filterby_country =       ( !empty($querystring_country) ) ? ' AND m3.meta_value ="'.  $querystring_country .'"' : '';
        $filterby_state =         ( !empty($querystring_state) )   ? ' AND m4.meta_value ="'.  $querystring_state .'"'  : '';
        $filterby_city =          ( !empty($querystring_city) )    ? ' AND m6.meta_value !="'. $querystring_city .'"'   : '';
	
   	$db_result = get_events_from_db($filterby_country, $filterby_state, $filterby_city);
   	
    if (!$db_result){
       echo PHP_EOL;
       echo('Database error: ' . mysql_error());
       echo PHP_EOL;
    }
   	
    $data_set =  mysql_num_rows($db_result);

    $i = 0;

	while ($i < $data_set) {

        mysql_data_seek($db_result, $i);
        $row =         mysql_fetch_object($db_result);
        $event =       get_single_event($row);
        
        if ( ($i == 0) && (!empty($event)) ) {
            $event_set .=    '<br />';
	        $event_set .=    $hr;       
            $events_title =  get_heading('Events in ' . $querystring_state);
            $event_set .=    $events_title;
            $event_set .=    $hr;
            $event_set .=    $event . '<br />';
        } elseif (!empty($event)) {
            $event_set .=  $event . '<br />';
        }

        $i++;

	}	

    $filterby_country =       ( !empty($querystring_country) ) ? ' AND m3.meta_value ="'.  $querystring_country .'"' : '';
    $filterby_state =         ( !empty($querystring_state) )   ? ' AND m4.meta_value !="'. $querystring_state .'"'  : '';
    $filterby_city =          ( !empty($querystring_city) )    ? ' AND m6.meta_value !="'. $querystring_city .'"'   : '';

    $db_result = get_events_from_db($filterby_country, $filterby_state, $filterby_city);
   	
    if (!$db_result){
       echo PHP_EOL;
       echo('Database error: ' . mysql_error());
       echo PHP_EOL;
    }
   	
    $data_set =  mysql_num_rows($db_result);

    $i = 0;

    $country_map =           get_country_map();
	$country_pretty_name =   $country_map[$querystring_country];
    
	while ($i < $data_set) {

        mysql_data_seek($db_result, $i);
        $row =         mysql_fetch_object($db_result);
        $event =       get_single_event($row);
        
        if ( ($i == 0) && (!empty($event)) ) {
            $event_set .=    '<br />';
	        $event_set .=    $hr;
            $events_title =  get_heading('Events in '. $country_pretty_name);
            $event_set .=    $events_title;
            $event_set .=    $hr;
            $event_set .=    $event . '<br />';
        } elseif (!empty($event)) {
            $event_set .=    $event . '<br />';
        }

        $i++;

	}
	
	$filterby_country =      ( !empty($querystring_country) ) ? ' AND m3.meta_value !="'. $querystring_country .'"' : '';
        $filterby_state =        '';
        $filterby_city =         '';	

   	$db_result = get_events_from_db($filterby_country, $filterby_state, $filterby_city);
   	
    if (!$db_result){
       echo PHP_EOL;
       echo('Database error: ' . mysql_error());
       echo PHP_EOL;
    }
   	
    $data_set =  mysql_num_rows($db_result);

    $i = 0;

	while ($i < $data_set) {

        mysql_data_seek($db_result, $i);
        $row =         mysql_fetch_object($db_result);
        $event =       get_single_event($row, true);
        
        if ( ($i == 0) && (!empty($event)) ) { 
            $event_set .=    '<br />';
	    $event_set .=    $hr;           
            $events_title = get_heading('Events from around the world');
            $event_set .=   $events_title;
            $event_set .=   $hr;
            $event_set .=   $event . '<br />';
        } elseif (!empty($event)) {
            $event_set .=   $event . '<br />';
        }

        $i++;

	}	

	return $event_set;

}

function get_sorted_posts($post_type, $user_lat, $user_long) {
   	
    $db_result =  get_posts_from_db($post_type);
    $data_set =   mysql_num_rows($db_result);
   	
    $unsorted_posts =  array();	    
    $sorted_posts =    array();
	
   	$i = 0;
	$posts_set = '';
	$hr = '<hr style="padding-top:0px;padding-bottom:0px;padding-right:0px;padding-left:0px;margin-top:0px;margin-bottom:10px;margin-right:0;margin-left:0;"> ';

	if ($data_set > 0) {
	    switch ($post_type) {
	        case 'gp_advertorial':
	            $posts_set =  '<br />';
	            $posts_set .=  $hr;
	            $posts_set .= get_heading('Awesome Eco Friendly Products &amp; Services');
	            $posts_set .=  $hr;
	            break;
	        case 'gp_projects':
	            $posts_set =  '<br />';
	            $posts_set .=  $hr;
	            $posts_set .= get_heading('Green Projects');
	            $posts_set .=  $hr;
	            break;	            
	    }
	}
	
	while ($i < $data_set) {

	    mysql_data_seek($db_result, $i);
		$row = mysql_fetch_object($db_result);

		$c = user_post_distance($row, $user_lat, $user_long);
		$popularity_score_thisuser = page_rank($c, $row);

		$post = get_single_post($row);

		$unsorted_posts[$popularity_score_thisuser] = $post;
		$i++;

	}

	krsort($unsorted_posts);
	
	$sorted_posts = ($post_type == 'gp_news') ? array_slice($unsorted_posts, 0, 15, true) : $unsorted_posts;

	foreach ($sorted_posts as $post) {
    	    $posts_set .= $post . '<br />';
	}
	
	return $posts_set;
}

function get_posts($user_lat, $user_long) {
    
    $post_set =    '';
    
    // Get news
    $post_type =   'gp_news';
    $posts_set .=  get_sorted_posts($post_type, $user_lat, $user_long);
    
    // Get products
    $post_type =   'gp_advertorial';
   	$posts_set .=  get_sorted_posts($post_type, $user_lat, $user_long);
	
    // Get projects
    $post_type =   'gp_projects';
    $posts_set .=  get_sorted_posts($post_type, $user_lat, $user_long);
	
    return $posts_set;
}

//STEP 3: Work out distance of user to post by hypotenuse  
function user_post_distance($row, $user_lat, $user_long) {

	$post_title =        $row->post_title;	
	$post_ID =           $row->ID;
	$popularity_score =  $row->popularity_score;
	$post_latitude =     $row->post_latitude;
	$post_longitude =    $row->post_longitude;

	$a = $post_latitude -  $user_lat;
	$b = $post_longitude - $user_long;

	$c = sqrt(pow($a,2) + pow($b,2));

	return $c;

}

//STEP 4: Add or subtract hypotenuse as converted to unixtime to popularity_score for previous array

function page_rank($c, $row) {

	$popularity_score = $row->popularity_score;

	if ($c > 2) {
 
    	$location_as_unix = pow(($c*2000), 1.2);
    	$location_as_unix = (int) $location_as_unix;
    	$popularity_score_thisuser = $popularity_score - $location_as_unix;
    	
		
	} elseif ($c < 1) {
	
		$popularity_score_thisuser = $popularity_score + pow(((1/$c)*3600), 1.2);
		$popularity_score_thisuser = (int) $popularity_score_thisuser;
		
	}
	
	return $popularity_score_thisuser;

}

// Send email using mailgun API

function send_email_notification($user_email, $posts_set, $events_set) {
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($ch, CURLOPT_USERPWD, 'api:key-2848zj9zqy6vzlec3qy1hwber1tsy1i2');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v2/greenpag.es/messages');
  
  curl_setopt($ch, CURLOPT_POSTFIELDS, array('from' => 'hello@greenpag.es',
                                             'to' => $user_email,
					     'cc' => 'jessebrowne78@gmail.com',
                                             'subject' => 'Green Razor: Look who\'s changing the world around you this week!',
                                             'text' => 'Some text',
                                             'html' => '
                                             
                                             
<html style="background-color:#eeeeee;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;"><head><title></title><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><style type="text/css">                                             
              
@media only screen and (max-device-width: 480px) { 
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
table[class=hide], td[class=hide], img[class=hide], p[class=hide], span[class=hide], .hide { display:none !important; }
table[class=h0], td[class=h0] { height: 0 !important; }
p[class=footer-content-left] { text-align: center !important; }
#headline p { font-size: 30px !important; }
 } 
#outlook a { padding: 0; }	
body { width: 100% !important; }
.ReadMsgBody { width: 100%; }
.ExternalClass { width: 100%; display:block !important; } 
html, body { background-color: #eeeeee; margin: 0; padding: 0; }
img { height: auto; line-height: 100%; outline: none; text-decoration: none; display: block;}
br, strong br, b br, em br, i br { line-height:100%; }
h1, h2, h3, h4, h5, h6 { line-height: 100% !important; -webkit-font-smoothing: antialiased; }
h1 a, h2 a, h3 a, h4 a, h5 a, h6 a { color: blue !important; }
h1 a:active, h2 a:active,  h3 a:active, h4 a:active, h5 a:active, h6 a:active {	color: red !important; }
h1 a:visited, h2 a:visited,  h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited { color: purple !important; }
  
table td, table tr { border-collapse: collapse; }
.yshortcuts, .yshortcuts a, .yshortcuts a:link,.yshortcuts a:visited, .yshortcuts a:hover, .yshortcuts a span {
color: black; text-decoration: none !important; border-bottom: none !important; background: none !important;
}	
code {
  white-space: normal;
  word-break: break-all;
}
#background-table { background-color: #eeeeee; }
#top-bar { border-radius:6px 6px 0px 0px; -moz-border-radius: 6px 6px 0px 0px; -webkit-border-radius:6px 6px 0px 0px; -webkit-font-smoothing: antialiased; background-color: #01aed8; color: #ffffff; }
#top-bar a { font-weight: bold; color: #ffffff; text-decoration: none;}
#footer { border-radius:0px 0px 6px 6px; -moz-border-radius: 0px 0px 6px 6px; -webkit-border-radius:0px 0px 6px 6px; -webkit-font-smoothing: antialiased; }
body { font-family: "Helvetica Neue", Arial, Helvetica, sans-serif; }
.header-content, .footer-content-left, .footer-content-right { -webkit-text-size-adjust: none; -ms-text-size-adjust: none; }
.header-content { font-size: 12px; color: #ffffff; }
.header-content a { font-weight: bold; color: #ffffff; text-decoration: none; }
#headline p { color: #01aed8; font-family: Helvetica Neue, Arial, Helvetica, Geneva, sans-serif; font-size: 24px; text-align: left; margin-top:0px; margin-bottom:30px; }
#headline p a { color: #01aed8; text-decoration: none; }
.article-title { font-size: 18px; line-height:24px; color: #787878; font-weight:bold; margin-top:0px; margin-bottom:8px; font-family: "Helvetica Neue", Arial, Helvetica, sans-serif; }
.article-title a { color: #01aed8; text-decoration: none; }
.article-title.with-meta {margin-bottom: 0;}
.article-meta { font-size: 13px; line-height: 20px; color: #ccc; font-weight: bold; margin-top: 0;}
.article-content { font-size: 13px; line-height: 18px; color: #444444; margin-top: 0px; margin-bottom: 18px; font-family: "Helvetica Neue", Arial, Helvetica, sans-serif; }
.article-content a { color: #01aed8; font-weight:bold; text-decoration:none; }
.article-content img { max-width: 100% }
.article-content ol, .article-content ul { margin-top:0px; margin-bottom:18px; margin-left:19px; padding:0; }
.article-content li { font-size: 13px; line-height: 18px; color: #444444; }
.article-content li a { color: #01aed8; text-decoration:underline; }
.article-content p {margin-bottom: 15px;}
.footer-content-left { font-size: 12px; line-height: 15px; color: #e2e2e2; margin-top: 0px; margin-bottom: 15px; }
.footer-content-left a { color: #eeeeee; font-weight: bold; text-decoration: none; }
.footer-content-right { font-size: 11px; line-height: 16px; color: #e2e2e2; margin-top: 0px; margin-bottom: 15px; }
.footer-content-right a { color: #eeeeee; font-weight: bold; text-decoration: none; }
#footer { background-color: #61C201; color: #e2e2e2; }
#footer a { color: #eeeeee; text-decoration: none; font-weight: bold; }
#permission-reminder { white-space: pre-wrap; }
#street-address { color: #61c201; white-space: pre-wrap; }
</style>

              
<meta name="robots" content="noindex,nofollow">

<link href="http://css.createsend1.com/css/social.min.css?h=BCE84E1Aoad" media="screen,projection" rel="stylesheet" type="text/css">
<style type="text/css">.fb_hidden{position:absolute;top:-10000px;z-index:10001}
.fb_invisible{display:none}
.fb_reset{background:none;border-spacing:0;border:0;color:#000;cursor:auto;direction:ltr;font-family:"lucida grande", tahoma, verdana, arial, sans-serif;font-size:11px;font-style:normal;font-variant:normal;font-weight:normal;letter-spacing:normal;line-height:1;margin:0;overflow:visible;padding:0;text-align:left;text-decoration:none;text-indent:0;text-shadow:none;text-transform:none;visibility:visible;white-space:normal;word-spacing:normal}
.fb_link img{border:none}
.fb_dialog{background:rgba(82, 82, 82, .7);position:absolute;top:-10000px;z-index:10001}
.fb_dialog_advanced{padding:10px;-moz-border-radius:8px;-webkit-border-radius:8px;border-radius:8px}
.fb_dialog_content{background:#fff;color:#333}
.fb_dialog_close_icon{background:url(http://static.ak.fbcdn.net/rsrc.php/v2/yq/r/IE9JII6Z1Ys.png) no-repeat scroll 0 0 transparent;_background-image:url(http://static.ak.fbcdn.net/rsrc.php/v2/yL/r/s816eWC-2sl.gif);cursor:pointer;display:block;height:15px;position:absolute;right:18px;top:17px;width:15px;top:8px\9;right:7px\9}
.fb_dialog_mobile .fb_dialog_close_icon{top:5px;left:5px;right:auto}
.fb_dialog_padding{background-color:transparent;position:absolute;width:1px;z-index:-1}
.fb_dialog_close_icon:hover{background:url(http://static.ak.fbcdn.net/rsrc.php/v2/yq/r/IE9JII6Z1Ys.png) no-repeat scroll 0 -15px transparent;_background-image:url(http://static.ak.fbcdn.net/rsrc.php/v2/yL/r/s816eWC-2sl.gif)}
.fb_dialog_close_icon:active{background:url(http://static.ak.fbcdn.net/rsrc.php/v2/yq/r/IE9JII6Z1Ys.png) no-repeat scroll 0 -30px transparent;_background-image:url(http://static.ak.fbcdn.net/rsrc.php/v2/yL/r/s816eWC-2sl.gif)}
.fb_dialog_loader{background-color:#f2f2f2;border:1px solid #606060;font-size:24px;padding:20px}
.fb_dialog_top_left,
.fb_dialog_top_right,
.fb_dialog_bottom_left,
.fb_dialog_bottom_right{height:10px;width:10px;overflow:hidden;position:absolute}
/* @noflip */
.fb_dialog_top_left{background:url(http://static.ak.fbcdn.net/rsrc.php/v2/ye/r/8YeTNIlTZjm.png) no-repeat 0 0;left:-10px;top:-10px}
/* @noflip */
.fb_dialog_top_right{background:url(http://static.ak.fbcdn.net/rsrc.php/v2/ye/r/8YeTNIlTZjm.png) no-repeat 0 -10px;right:-10px;top:-10px}
/* @noflip */
.fb_dialog_bottom_left{background:url(http://static.ak.fbcdn.net/rsrc.php/v2/ye/r/8YeTNIlTZjm.png) no-repeat 0 -20px;bottom:-10px;left:-10px}
/* @noflip */
.fb_dialog_bottom_right{background:url(http://static.ak.fbcdn.net/rsrc.php/v2/ye/r/8YeTNIlTZjm.png) no-repeat 0 -30px;right:-10px;bottom:-10px}
.fb_dialog_vert_left,
.fb_dialog_vert_right,
.fb_dialog_horiz_top,
.fb_dialog_horiz_bottom{position:absolute;background:#525252;filter:alpha(opacity=70);opacity:.7}
.fb_dialog_vert_left,
.fb_dialog_vert_right{width:10px;height:100%}
.fb_dialog_vert_left{margin-left:-10px}
.fb_dialog_vert_right{right:0;margin-right:-10px}
.fb_dialog_horiz_top,
.fb_dialog_horiz_bottom{width:100%;height:10px}
.fb_dialog_horiz_top{margin-top:-10px}
.fb_dialog_horiz_bottom{bottom:0;margin-bottom:-10px}
.fb_dialog_iframe{line-height:0}
.fb_dialog_content .dialog_title{background:#6d84b4;border:1px solid #3b5998;color:#fff;font-size:14px;font-weight:bold;margin:0}
.fb_dialog_content .dialog_title > span{background:url(http://static.ak.fbcdn.net/rsrc.php/v2/yd/r/Cou7n-nqK52.gif)
no-repeat 5px 50%;float:left;padding:5px 0 7px 26px}
body.fb_hidden{-webkit-transform:none;height:100%;margin:0;left:-10000px;overflow:visible;position:absolute;top:-10000px;width:100%
}
.fb_dialog.fb_dialog_mobile.loading{background:url(http://static.ak.fbcdn.net/rsrc.php/v2/ya/r/3rhSv5V8j3o.gif)
white no-repeat 50% 50%;min-height:100%;min-width:100%;overflow:hidden;position:absolute;top:0;z-index:10001}
.fb_dialog.fb_dialog_mobile.loading.centered{max-height:590px;min-height:590px;max-width:500px;min-width:500px}
#fb-root #fb_dialog_ipad_overlay{background:rgba(0, 0, 0, .45);position:absolute;left:0;top:0;width:100%;min-height:100%;z-index:10000}
#fb-root #fb_dialog_ipad_overlay.hidden{display:none}
.fb_dialog.fb_dialog_mobile.loading iframe{visibility:hidden}
.fb_dialog_content .dialog_header{-webkit-box-shadow:white 0 1px 1px -1px inset;background:-webkit-gradient(linear, 0 0, 0 100%, from(#738ABA), to(#2C4987));border-bottom:1px solid;border-color:#1d4088;color:#fff;font:14px Helvetica, sans-serif;font-weight:bold;text-overflow:ellipsis;text-shadow:rgba(0, 30, 84, .296875) 0 -1px 0;vertical-align:middle;white-space:nowrap}
.fb_dialog_content .dialog_header table{-webkit-font-smoothing:subpixel-antialiased;height:43px;width:100%
}
.fb_dialog_content .dialog_header td.header_left{font-size:12px;padding-left:5px;vertical-align:middle;width:60px
}
.fb_dialog_content .dialog_header td.header_right{font-size:12px;padding-right:5px;vertical-align:middle;width:60px
}
.fb_dialog_content .touchable_button{background:-webkit-gradient(linear, 0 0, 0 100%, from(#4966A6),
color-stop(0.5, #355492), to(#2A4887));border:1px solid #29447e;-webkit-background-clip:padding-box;-webkit-border-radius:3px;-webkit-box-shadow:rgba(0, 0, 0, .117188) 0 1px 1px inset,
rgba(255, 255, 255, .167969) 0 1px 0;display:inline-block;margin-top:3px;max-width:85px;line-height:18px;padding:4px 12px;position:relative}
.fb_dialog_content .dialog_header .touchable_button input{border:none;background:none;color:#fff;font:12px Helvetica, sans-serif;font-weight:bold;margin:2px -12px;padding:2px 6px 3px 6px;text-shadow:rgba(0, 30, 84, .296875) 0 -1px 0}
.fb_dialog_content .dialog_header .header_center{color:#fff;font-size:16px;font-weight:bold;line-height:18px;text-align:center;vertical-align:middle}
.fb_dialog_content .dialog_content{background:url(http://static.ak.fbcdn.net/rsrc.php/v2/y9/r/jKEcVPZFk-2.gif) no-repeat 50% 50%;border:1px solid #555;border-bottom:0;border-top:0;height:150px}
.fb_dialog_content .dialog_footer{background:#f2f2f2;border:1px solid #555;border-top-color:#ccc;height:40px}
#fb_dialog_loader_close{float:left}
.fb_dialog.fb_dialog_mobile .fb_dialog_close_button{text-shadow:rgba(0, 30, 84, .296875) 0 -1px 0}
.fb_dialog.fb_dialog_mobile .fb_dialog_close_icon{visibility:hidden}
.fb_iframe_widget{position:relative;display:-moz-inline-block;display:inline-block}
.fb_iframe_widget iframe{position:absolute}
.fb_iframe_widget_lift{z-index:1}
.fb_iframe_widget span{display:inline-block;position:relative;text-align:justify;vertical-align:text-bottom}
.fb_hide_iframes iframe{position:relative;left:-10000px}
.fb_iframe_widget_loader{position:relative;display:inline-block}
.fb_iframe_widget_fluid{display:inline}
.fb_iframe_widget_fluid span{width:100%}
.fb_iframe_widget_loader iframe{min-height:32px;z-index:2;zoom:1}
.fb_iframe_widget_loader .FB_Loader{background:url(http://static.ak.fbcdn.net/rsrc.php/v2/y9/r/jKEcVPZFk-2.gif) no-repeat;height:32px;width:32px;margin-left:-16px;position:absolute;left:50%;z-index:4}
.fb_button_simple,
.fb_button_simple_rtl{background-image:url(http://static.ak.fbcdn.net/rsrc.php/v2/yH/r/eIpbnVKI9lR.png);background-repeat:no-repeat;cursor:pointer;outline:none;text-decoration:none}
.fb_button_simple_rtl{background-position:right 0}
.fb_button_simple .fb_button_text{margin:0 0 0 20px;padding-bottom:1px}
.fb_button_simple_rtl .fb_button_text{margin:0 10px 0 0}
a.fb_button_simple:hover .fb_button_text,
a.fb_button_simple_rtl:hover .fb_button_text,
.fb_button_simple:hover .fb_button_text,
.fb_button_simple_rtl:hover .fb_button_text{text-decoration:underline}
.fb_button,
.fb_button_rtl{background:#29447e url(http://static.ak.fbcdn.net/rsrc.php/v2/yL/r/FGFbc80dUKj.png);background-repeat:no-repeat;cursor:pointer;display:inline-block;padding:0 0 0 1px;text-decoration:none;outline:none}
.fb_button .fb_button_text,
.fb_button_rtl .fb_button_text{background:#5f78ab url(http://static.ak.fbcdn.net/rsrc.php/v2/yL/r/FGFbc80dUKj.png);border-top:solid 1px #879ac0;border-bottom:solid 1px #1a356e;color:#fff;display:block;font-family:"lucida grande",tahoma,verdana,arial,sans-serif;font-weight:bold;padding:2px 6px 3px 6px;margin:1px 1px 0 21px;text-shadow:none}
a.fb_button,
a.fb_button_rtl,
.fb_button,
.fb_button_rtl{text-decoration:none}
a.fb_button:active .fb_button_text,
a.fb_button_rtl:active .fb_button_text,
.fb_button:active .fb_button_text,
.fb_button_rtl:active .fb_button_text{border-bottom:solid 1px #29447e;border-top:solid 1px #45619d;background:#4f6aa3;text-shadow:none}
.fb_button_xlarge,
.fb_button_xlarge_rtl{background-position:left -60px;font-size:24px;line-height:30px}
.fb_button_xlarge .fb_button_text{padding:3px 8px 3px 12px;margin-left:38px}
a.fb_button_xlarge:active{background-position:left -99px}
.fb_button_xlarge_rtl{background-position:right -268px}
.fb_button_xlarge_rtl .fb_button_text{padding:3px 8px 3px 12px;margin-right:39px}
a.fb_button_xlarge_rtl:active{background-position:right -307px}
.fb_button_large,
.fb_button_large_rtl{background-position:left -138px;font-size:13px;line-height:16px}
.fb_button_large .fb_button_text{margin-left:24px;padding:2px 6px 4px 6px}
a.fb_button_large:active{background-position:left -163px}
.fb_button_large_rtl{background-position:right -346px}
.fb_button_large_rtl .fb_button_text{margin-right:25px}
a.fb_button_large_rtl:active{background-position:right -371px}
.fb_button_medium,
.fb_button_medium_rtl{background-position:left -188px;font-size:11px;line-height:14px}
a.fb_button_medium:active{background-position:left -210px}
.fb_button_medium_rtl{background-position:right -396px}
.fb_button_text_rtl,
.fb_button_medium_rtl .fb_button_text{padding:2px 6px 3px 6px;margin-right:22px}
a.fb_button_medium_rtl:active{background-position:right -418px}
.fb_button_small,
.fb_button_small_rtl{background-position:left -232px;font-size:10px;line-height:10px}
.fb_button_small .fb_button_text{padding:2px 6px 3px;margin-left:17px}
a.fb_button_small:active,
.fb_button_small:active{background-position:left -250px}
.fb_button_small_rtl{background-position:right -440px}
.fb_button_small_rtl .fb_button_text{padding:2px 6px;margin-right:18px}
a.fb_button_small_rtl:active{background-position:right -458px}
.fb_share_count_wrapper{position:relative;float:left}
.fb_share_count{background:#b0b9ec none repeat scroll 0 0;color:#333;font-family:"lucida grande", tahoma, verdana, arial, sans-serif;text-align:center}
.fb_share_count_inner{background:#e8ebf2;display:block}
.fb_share_count_right{margin-left:-1px;display:inline-block}
.fb_share_count_right .fb_share_count_inner{border-top:solid 1px #e8ebf2;border-bottom:solid 1px #b0b9ec;margin:1px 1px 0 1px;font-size:10px;line-height:10px;padding:2px 6px 3px;font-weight:bold}
.fb_share_count_top{display:block;letter-spacing:-1px;line-height:34px;margin-bottom:7px;font-size:22px;border:solid 1px #b0b9ec}
.fb_share_count_nub_top{border:none;display:block;position:absolute;left:7px;top:35px;margin:0;padding:0;width:6px;height:7px;background-repeat:no-repeat;background-image:url(http://static.ak.fbcdn.net/rsrc.php/v2/yU/r/bSOHtKbCGYI.png)}
.fb_share_count_nub_right{border:none;display:inline-block;padding:0;width:5px;height:10px;background-repeat:no-repeat;background-image:url(http://static.ak.fbcdn.net/rsrc.php/v2/yX/r/i_oIVTKMYsL.png);vertical-align:top;background-position:right 5px;z-index:10;left:2px;margin:0 2px 0 0;position:relative}
.fb_share_no_count{display:none}
.fb_share_size_Small .fb_share_count_right .fb_share_count_inner{font-size:10px}
.fb_share_size_Medium .fb_share_count_right .fb_share_count_inner{font-size:11px;padding:2px 6px 3px;letter-spacing:-1px;line-height:14px}
.fb_share_size_Large .fb_share_count_right .fb_share_count_inner{font-size:13px;line-height:16px;padding:2px 6px 4px;font-weight:normal;letter-spacing:-1px}
.fb_share_count_hidden .fb_share_count_nub_top,
.fb_share_count_hidden .fb_share_count_top,
.fb_share_count_hidden .fb_share_count_nub_right,
.fb_share_count_hidden .fb_share_count_right{visibility:hidden}
.fb_connect_bar_container div,
.fb_connect_bar_container span,
.fb_connect_bar_container a,
.fb_connect_bar_container img,
.fb_connect_bar_container strong{background:none;border-spacing:0;border:0;direction:ltr;font-style:normal;font-variant:normal;letter-spacing:normal;line-height:1;margin:0;overflow:visible;padding:0;text-align:left;text-decoration:none;text-indent:0;text-shadow:none;text-transform:none;visibility:visible;white-space:normal;word-spacing:normal;vertical-align:baseline}
.fb_connect_bar_container{position:fixed;left:0 !important;right:0 !important;height:42px !important;padding:0 25px !important;margin:0 !important;vertical-align:middle !important;border-bottom:1px solid #333 !important;background:#3b5998 !important;z-index:99999999 !important;overflow:hidden !important}
.fb_connect_bar_container_ie6{position:absolute;top:expression(document.compatMode=="CSS1Compat"? document.documentElement.scrollTop+"px":body.scrollTop+"px")}
.fb_connect_bar{position:relative;margin:auto;height:100%;width:100%;padding:6px 0 0 0 !important;background:none;color:#fff !important;font-family:"lucida grande", tahoma, verdana, arial, sans-serif !important;font-size:13px !important;font-style:normal !important;font-variant:normal !important;font-weight:normal !important;letter-spacing:normal !important;line-height:1 !important;text-decoration:none !important;text-indent:0 !important;text-shadow:none !important;text-transform:none !important;white-space:normal !important;word-spacing:normal !important}
.fb_connect_bar a:hover{color:#fff}
.fb_connect_bar .fb_profile img{height:30px;width:30px;vertical-align:middle;margin:0 6px 5px 0}
.fb_connect_bar div a,
.fb_connect_bar span,
.fb_connect_bar span a{color:#bac6da;font-size:11px;text-decoration:none}
.fb_connect_bar .fb_buttons{float:right;margin-top:7px}
.fb_edge_widget_with_comment{position:relative;*z-index:1000}
.fb_edge_widget_with_comment span.fb_edge_comment_widget{position:absolute}
.fb_edge_widget_with_comment span.fb_send_button_form_widget{z-index:1}
.fb_edge_widget_with_comment span.fb_send_button_form_widget .FB_Loader{left:0;top:1px;margin-top:6px;margin-left:0;background-position:50% 50%;background-color:#fff;height:150px;width:394px;border:1px #666 solid;border-bottom:2px solid #283e6c;z-index:1}
.fb_edge_widget_with_comment span.fb_send_button_form_widget.dark .FB_Loader{background-color:#000;border-bottom:2px solid #ccc}
.fb_edge_widget_with_comment span.fb_send_button_form_widget.siderender
.FB_Loader{margin-top:0}
.fbpluginrecommendationsbarleft,
.fbpluginrecommendationsbarright{position:fixed !important;bottom:0;z-index:999}
/* @noflip */
.fbpluginrecommendationsbarleft{left:10px}
/* @noflip */
.fbpluginrecommendationsbarright{right:10px}</style><style type="text/css">@media print { #feedlyMiniIcon { display: none; } }</style></head><body style="width:100% !important;background-color:#eeeeee;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family: Arial, Helvetica, sans-serif;" cz-shortcut-listen="true"><table width="100%" cellpadding="0" cellspacing="0" border="0" id="background-table" style="background-color:#eeeeee;">
	<tbody><tr style="border-collapse:collapse;">
		<td align="center" bgcolor="#eeeeee" style="border-collapse:collapse;">
        	<table class="w640" width="640" cellpadding="0" cellspacing="0" border="0">
            	<tbody><tr style="border-collapse:collapse;"><td class="w640" width="640" height="20" style="border-collapse:collapse;"></td></tr>
                
            	<tr style="border-collapse:collapse;">
                	<td class="w640" width="640" style="border-collapse:collapse;">
                        <table id="top-bar" class="w640" width="640" cellpadding="0" cellspacing="0" border="0" bgcolor="#eeeeee" style="border-radius:6px 6px 0px 0px;-moz-border-radius:6px 6px 0px 0px;-webkit-border-radius:6px 6px 0px 0px;-webkit-font-smoothing:antialiased;background-color:#67B832;color:#ffffff;">
    <tbody><tr style="border-collapse:collapse;">
        <td class="w15" width="150" style="border-collapse:collapse;"></td>
        <td class="w325" width="350" valign="middle" align="left" style="border-collapse:collapse;">
            <table class="w325" width="350" cellpadding="0" cellspacing="0" border="0">
                <tbody><tr style="border-collapse:collapse;"><td class="w325" width="350" height="8" style="border-collapse:collapse;"></td></tr>
            </tbody></table>
            <div class="header-content" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;font-size:12px;color:#ffffff;"><a href="http://greenpages.createsend1.com/t/r-e-ojylyjt-eidkjkly-u/" style="font-weight:bold;color:#ffffff;text-decoration:none;">greenpag.es</a><span class="hide">&nbsp;&nbsp;<a href="http://greenpages.createsend1.com/t/r-l-ojylyjt-eidkjkly-a/" style="font-weight:bold;color:#ffffff;text-decoration:none;">this week</a>&nbsp;&nbsp;&nbsp;</span></div>
            <table class="w325" width="350" cellpadding="0" cellspacing="0" border="0">
                <tbody><tr style="border-collapse:collapse;"><td class="w325" width="350" height="8" style="border-collapse:collapse;"></td></tr>
            </tbody></table>
        </td>
        <td class="w30" width="30" style="border-collapse:collapse;"></td>
        <td class="w255" width="255" valign="middle" align="right" style="border-collapse:collapse;">
            <table class="w255" width="255" cellpadding="0" cellspacing="0" border="0">
                <tbody><tr style="border-collapse:collapse;"><td class="w255" width="255" height="8" style="border-collapse:collapse;"></td></tr>
            </tbody></table>
            <table cellpadding="0" cellspacing="0" border="0">
    <tbody><tr style="border-collapse:collapse;">
        
        <td valign="middle" style="border-collapse:collapse;"><a href="/t/r-fb-ojylyjt-eidkjkly-o/?act=wv" rel="cs_facebox" style="font-weight:bold;color:#ffffff;text-decoration:none;" cs_likeurl="/t/r-fb-ojylyjt-eidkjkly-o/?act=like"><img src="http://i5.createsend1.com/ti/r/F0/850/BDD/134918//csimport/like-glyph_0.png" border="0" width="8" height="14" alt="Facebook icon" =""="" style="height:auto;line-height:100%;outline-style:none;text-decoration:none;display:block;"></a></td>
        <td width="3" style="border-collapse:collapse;"></td>
        <td valign="middle" style="border-collapse:collapse;"><div class="header-content" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;font-size:12px;color:#ffffff;"><a href="/t/r-fb-ojylyjt-eidkjkly-b/?act=wv" rel="cs_facebox" style="font-weight:bold;color:#ffffff;text-decoration:none;" cs_likeurl="/t/r-fb-ojylyjt-eidkjkly-b/?act=like">Like</a></div></td>
        
        
        <td class="w10" width="10" style="border-collapse:collapse;"></td>
        <td valign="middle" style="border-collapse:collapse;"><a href="http://greenpages.createsend1.com/t/r-tw-ojylyjt-eidkjkly-n/" style="font-weight:bold;color:#ffffff;text-decoration:none;"><img src="http://i6.createsend1.com/ti/r/F0/850/BDD/134918//csimport/tweet-glyph_1.png" border="0" width="17" height="13" alt="Twitter icon" =""="" style="height:auto;line-height:100%;outline-style:none;text-decoration:none;display:block;"></a></td>
        <td width="3" style="border-collapse:collapse;"></td>
        <td valign="middle" style="border-collapse:collapse;"><div class="header-content" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;font-size:12px;color:#ffffff;"><a href="http://greenpages.createsend1.com/t/r-tw-ojylyjt-eidkjkly-p/" style="font-weight:bold;color:#ffffff;text-decoration:none;">Tweet</a></div></td>
        
        
        <td class="w10" width="10" style="border-collapse:collapse;"></td>
        <td valign="middle" style="border-collapse:collapse;"><a href="http://greenpages.forwardtomyfriend.com/r-eidkjkly-226DE05E-ojylyjt-l-x" style="font-weight:bold;color:#ffffff;text-decoration:none;"><img src="http://i7.createsend1.com/ti/r/F0/850/BDD/134918//csimport/forward-glyph_2.png" border="0" width="19" height="14" alt="Forward icon" =""="" style="height:auto;line-height:100%;outline-style:none;text-decoration:none;display:block;"></a></td>
        <td width="3" style="border-collapse:collapse;"></td>
        <td valign="middle" style="border-collapse:collapse;"><div class="header-content" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;font-size:12px;color:#ffffff;"><a href="http://greenpages.forwardtomyfriend.com/r-eidkjkly-226DE05E-ojylyjt-l-m" style="font-weight:bold;color:#ffffff;text-decoration:none;">Forward</a></div></td>
        
    </tr>
</tbody></table>
            <table class="w255" width="255" cellpadding="0" cellspacing="0" border="0">
                <tbody><tr style="border-collapse:collapse;"><td class="w255" width="255" height="8" style="border-collapse:collapse;"></td></tr>
            </tbody></table>
        </td>
        <td class="w15" width="15" style="border-collapse:collapse;"></td>
    </tr>
</tbody></table>
                        
                    </td>
                </tr>
                <tr style="border-collapse:collapse;">
                <td id="header" class="w640" width="640" align="center" bgcolor="#ffffff" style="border-collapse:collapse;">
    <!--
    <div align="left" style="text-align:left;"><img id="customHeaderImage" src="http://i8.createsend1.com/ti/r/F0/850/BDD/134918/header1.png" class="w640" border="0" align="top" style="display:inline;height:auto;line-height:100%;outline-style:none;text-decoration:none;"></div>-->
    
    <table class="w640" width="640" cellpadding="0" cellspacing="0" border="0">
        <tbody><tr style="border-collapse:collapse;"><td class="w30" width="30" style="border-collapse:collapse;"></td><td class="w580" width="580" height="30" style="border-collapse:collapse;"></td><td class="w30" width="30" style="border-collapse:collapse;"></td></tr>
        <tr style="border-collapse:collapse;">
            <td class="w30" width="30" style="border-collapse:collapse;"></td>
            
            
            <td class="w30" width="30" style="border-collapse:collapse;"></td>
        </tr>
    </tbody></table>
    
    
</td>
                </tr>
                
                <tr style="border-collapse:collapse;"><td class="w640" width="640" height="30" bgcolor="#ffffff" style="border-collapse:collapse;"></td></tr>
                <tr id="simple-content-row" style="border-collapse:collapse;"><td class="w640" width="640" bgcolor="#ffffff" style="border-collapse:collapse;">
    <table class="w640" width="640" cellpadding="0" cellspacing="0" border="0">
        <tbody><tr style="border-collapse:collapse;">
            <td class="w30" width="30" style="border-collapse:collapse;"></td>
            <td class="w580" width="580" style="border-collapse:collapse;">

			'. $posts_set .
			   $events_set .'
     
               <!--FOOTER -->
                  
          </td>
            <td class="w30" width="30" style="border-collapse:collapse;"></td>
        </tr>
    </tbody></table>
</td></tr>
                <tr style="border-collapse:collapse;"><td class="w640" width="640" height="15" bgcolor="#ffffff" style="border-collapse:collapse;"></td></tr>
                
                <tr style="border-collapse:collapse;">
                <td class="w640" width="640" style="border-collapse:collapse;">
    <table id="footer" class="w640" width="640" cellpadding="0" cellspacing="0" border="0" bgcolor="#67B832" style="border-radius:0px 0px 6px 6px;-moz-border-radius:0px 0px 6px 6px;-webkit-border-radius:0px 0px 6px 6px;-webkit-font-smoothing:antialiased;background-color:#67B832;color:#e2e2e2;">
    <tbody><tr style="border-collapse:collapse;"><td class="w30" width="30" style="border-collapse:collapse;"></td><td class="w580 h0" width="360" height="30" style="border-collapse:collapse;"></td><td class="w0" width="60" style="border-collapse:collapse;"></td><td class="w0" width="160" style="border-collapse:collapse;"></td><td class="w30" width="30" style="border-collapse:collapse;"></td></tr>
        <tr style="border-collapse:collapse;">
            <td class="w30" width="30" style="border-collapse:collapse;"></td>
            <td class="w580" width="360" valign="top" style="border-collapse:collapse;">
            <span class="hide"><p id="permission-reminder" align="left" class="footer-content-left" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;font-size:12px;line-height:15px;color:#e2e2e2;margin-top:0px;margin-bottom:15px;white-space:pre-wrap;">You\'re receiving weekly notifications of who\'s changing the world around you from www.greenpag.es</p></span>
            <p align="left" class="footer-content-left" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;font-size:12px;line-height:15px;color:#e2e2e2;margin-top:0px;margin-bottom:15px;"><a href="http://greenpages.updatemyprofile.com/r-ojylyjt-226DE05E-eidkjkly-c" style="color:#eeeeee;text-decoration:none;font-weight:bold;">Change your email settings</a> | <a href="http://greenpages.createsend1.com/t/r-u-ojylyjt-eidkjkly-q/" style="color:#eeeeee;text-decoration:none;font-weight:bold;"></a></p>
            </td>
            <td class="hide w0" width="60" style="border-collapse:collapse;"></td>
            <td class="hide w0" width="160" valign="top" style="border-collapse:collapse;">
            <p id="street-address" align="right" class="footer-content-right" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;font-size:11px;line-height:16px;margin-top:0px;margin-bottom:15px;color:#61c201;white-space:pre-wrap;">&nbsp;</p>
            </td>
            <td class="w30" width="30" style="border-collapse:collapse;"></td>
        </tr>
        <tr style="border-collapse:collapse;"><td class="w30" width="30" style="border-collapse:collapse;"></td><td class="w580 h0" width="360" height="15" style="border-collapse:collapse;"></td><td class="w0" width="60" style="border-collapse:collapse;"></td><td class="w0" width="160" style="border-collapse:collapse;"></td><td class="w30" width="30" style="border-collapse:collapse;"></td></tr>
    </tbody></table>
</td>
                </tr>
                <tr style="border-collapse:collapse;"><td class="w640" width="640" height="60" style="border-collapse:collapse;"></td></tr>
            </tbody></table>
        </td>
	</tr>
</tbody></table>

<div id="fb-root" class=" fb_reset"><div style="position: absolute; top: -10000px; height: 0px; width: 0px;"><div></div></div><div style="position: absolute; top: -10000px; height: 0px; width: 0px;"><div><iframe name="fb_xdm_frame_http" frameborder="0" allowtransparency="true" scrolling="no" id="fb_xdm_frame_http" aria-hidden="true" title="Facebook Cross Domain Communication Frame" tab-index="-1" style="border: none;" src="http://static.ak.facebook.com/connect/xd_arbiter.php?version=23#channel=f1dba83e08&amp;origin=http%3A%2F%2Fgreenpages.createsend1.com&amp;channel_path=%2Ft%2FViewEmail%2Fr%2F643813F9E5B60E732540EF23F30FEDED%2F273C6DC1D5E74A2EF6A1C87C670A6B9F%3Ffb_xd_fragment%23xd_sig%3Dfb087e7e%26"></iframe><iframe name="fb_xdm_frame_https" frameborder="0" allowtransparency="true" scrolling="no" id="fb_xdm_frame_https" aria-hidden="true" title="Facebook Cross Domain Communication Frame" tab-index="-1" style="border: none;" src="https://s-static.ak.facebook.com/connect/xd_arbiter.php?version=23#channel=f1dba83e08&amp;origin=http%3A%2F%2Fgreenpages.createsend1.com&amp;channel_path=%2Ft%2FViewEmail%2Fr%2F643813F9E5B60E732540EF23F30FEDED%2F273C6DC1D5E74A2EF6A1C87C670A6B9F%3Ffb_xd_fragment%23xd_sig%3Dfb087e7e%26"></iframe></div></div></div>
<script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>
<script type="text/javascript" src="http://js.createsend1.com/js/jquery-1.7.2.min.js?h=C99A4659oad"></script>
<script type="text/javascript" src="http://js.createsend1.com/js/track.min.js?h=A95B7562oad"></script>
<script type="text/javascript">
$(document).ready(function () {
    CS.WebVersion.setup({"LikeActionBase":"/t/r-fb-ojylyjt-eidkjkly-","IsSubscriber":true});
});
</script>

    <div id="facebox" style="display:none;">       <div class="popup">         <div class="content">         </div>         <div id="closeBox">           <a href="#" class="close">Close</a>         </div>       </div>     </div><img id="feedlyMiniIcon" title="feedly mini" style="position: fixed; bottom: 20px; right: 20px; cursor: pointer; border: 0px; -webkit-transition: opacity 0.5s ease; transition: opacity 0.5s ease; visibility: visible; width: 36px; height: 36px; max-height: 36px; max-width: 36px; overflow: hidden; display: block; padding: 0px; opacity: 0.15;" width="36" height="36" src="data:image/png;base64,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"></body></html>'));
                                            

  $result = curl_exec($ch); 
 
  curl_close($ch);

  return $result;
 
}

function send_notifcations() {

    $users = get_users();
    $i = 0;
    $data_set = mysql_num_rows($users);    

    while ($i < $data_set) {
		
        mysql_data_seek($users, $i);
	    $row = mysql_fetch_object($users);
        $user_id = $row->ID;
	    $user_email = $row->user_email;
	    $user_lat = '';
	    $user_long = '';
	    
	    $user_notification_setting = get_user_notification_setting($user_id);
	    
	    if ($user_notification_setting == 'weekly_email') {
            
    	    $user_lat_long = get_user_lat_long($user_id);
    
            $j = 0;
            $lat_long_set = mysql_num_rows($user_lat_long);    
    
            while ($j < $lat_long_set) {
                    
                    mysql_data_seek($user_lat_long, $j);
                    $row = mysql_fetch_object($user_lat_long);
                    $meta_key = $row->meta_key;
                    $meta_value = $row->meta_value;
                    switch ($meta_key) {    
                        case 'gp_google_geo_latitude':
                            $user_lat = $meta_value;
                            break;
                        case 'gp_google_geo_longitude':
                            $user_long = $meta_value;
                            break;
                    }
                    $j++;
            }
	    
	    echo '$user_lat: ';
	    echo PHP_EOL;
	    var_dump($user_lat);
	    echo PHP_EOL;
	    echo '$user_long: ';
	    echo PHP_EOL;
	    var_dump($user_long);
	    echo PHP_EOL;
	    
	    if (empty($user_lat) && empty($user_long)) {
		echo PHP_EOL;
		echo 'No location data set for user ' .$user_id;
		echo PHP_EOL;
		echo PHP_EOL;
		$user_lat = '-33.8674869';
		$user_long = '151.2069902';
		echo 'Location data now set for user ' .$user_id;
		echo PHP_EOL;
		echo '$user_lat: ';
		echo PHP_EOL;
		var_dump($user_lat);
		echo PHP_EOL;
		echo '$user_long: ';
		echo PHP_EOL;
		var_dump($user_long);
		echo PHP_EOL;
	    }
	    
            $posts_set = get_posts($user_lat, $user_long);
            mb_convert_encoding($posts_set, 'UTF-8');

            $events_set = get_events($user_id);
            mb_convert_encoding($events_set, 'UTF-8');
            
            send_email_notification($user_email, $posts_set, $events_set);
            echo 'Email sent to user '. $user_id;
	        echo PHP_EOL;
	    }
        
	    $i++;
    }
    echo PHP_EOL;
    echo PHP_EOL;
    echo $i .' emails sent.';
    echo PHP_EOL;
}

send_notifcations();

echo '_______________________________________________________';
echo PHP_EOL; 
echo 'Ends';
echo PHP_EOL; 
echo '_______________________________________________________';
echo PHP_EOL;

mysql_close($db_connection);
exit();

?>
