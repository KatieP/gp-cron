<?php

// This cron job runs every day/hour and logs the clicks for each advertiser with their product in chargify. 

// Chargify bills the client one per week.

// This job also checks the status of the advertiser as 'used_up': budget filled until next time period or 'active': budget remaining or 'paused': and saves as user_meta

// All posts for advertisers who have moved to 'used_up' or 'paused' have their posts set to draft status.

// All posts for advertisers who have moved from 'used_up' to 'active' when thier next billing cycle commences have their posts moved from draft to published.

//The system must know when clicks reach max to turn posts off, then to turn on again.


#8. Activate 'billing' section on 'advertisers' tab on user profile.
#9. Show plan, rate and amount billed on advertiser/billing tab.
#10. Write cron to daily add metered amount of clicks to chargify subscription.
#11. Each chargify subscription period (weekly) add the amount and clicks on a table in billing tab
#12. Add option on billing for advertiser to upgrade, downgrade or pause their subscription
#13. Make weekly advertiser email that shows clicks and billing. Has prompt - reaching max cap? Increase your cap and get more customers for your business Not reaching cap? Create another editorial - let us know what makes your product interesting. Link to form. Add link to 'how to write a great post 
#14. Advertiser post is shown around site as 'related posts'.
#15. When cap is reached, posts go to draft status
#16. When new billing cycle commences, posts turn on again


//-----------------------------------//

//1.Connect to MYSQL database
mysql_connect("127.0.0.1", "s1-wordpress", "7BXmxPmwy4LJZNhR") or die(mysql_error());
mysql_select_db("s1-wordpress") or die(mysql_error());

//2.Sign into Google Analytics
$analytics = new analytics('greenpagesadserving@gmail.com', 'greenpages01'); //sign in and grab profile			
$analytics->setProfileById('ga:42443499'); 			//$analytics->setProfileByName('Stage 1 - Green Pages');

// meta_key = budget 			-> 	meta_value = 'used_up','active' or 'paused'
// meta_key = reg_advertiser 	-> 	meta_value = 1;
// meta_key = charigfyID 		-> 	meta_value = number;
// Meta_key = productID 		-> 	meta_value = number;

//$12 / week CPC plan 	ID: 3313295   	Handle: 12-week-plan
//$39 / week CPC plan 	ID: 27029   	Handle: 39-week-plan
//$99 / week CPC plan 	ID: 27028   	Handle: 99-week-plan
//$249 / week plan 		ID: 3313296   	Handle: 249-week-plan
//$499 / week plan 		ID: 3313297   	Handle: 499-week-plan
//Click $1.90 component ID: 3207

//Directory Page $39 Monthly Fee ID: 27023   Handle: directory-page-39-monthly-fee

// meta_key = budget 			-> 	meta_value = 'used_up','active' or 'paused'
// meta_key = reg_advertiser 	-> 	meta_value = 1;
// meta_key = charigfy_ID 		-> 	meta_value = number;
// Meta_key = chargify_product_ID 	-> 	meta_value = number;

// Biome wp=259	 ch=1733583
// New Leaf Corporate wp=1505  ch=1787860
// 15 Trees wp=1936	ch=2045804
// Climate Friendly  wp=1551  ch=1828803


//3.Get all authors (advertisers)IDs, chargifyIDs and productIDs where reg_advertiser=1, budget=active

$sql = "SELECT user_ID
		FROM wp_meta 
		WHERE meta_value = 'reg_advertiser', meta_key = '1'
		AND meta_value = 'budget', meta_key = 'active'";


$db_result = mysql_query($sql);

$data_set = mysql_num_rows($db_result);
	echo $data_set;
	echo '<hr />';
	
//4. Get all posts for each ID. Grab the their analytics for the hour for all posts and sum
	
	date_default_timezone_set('UTC');
	
	$i = 0;
	while ($i < $data_set) { 

		mysql_data_seek($db_result, $i);
		$row = mysql_fetch_object($db_result);
		
	
				
	if ($pageposts) {		
	 	
	    $total_sumURL = 0;
	    
		foreach ($pageposts as $post) {
			setup_postdata($post);
		
			$post_url_ext = $post->post_name; //Need to get post_name for URL. Gets ful URl, but we only need /url extention for Google API			
			$type = get_post_type($post->ID);
				
			$post_type_map = getPostTypeSlug($type);
				
			$post_url_end = '/' . $post_type_map . '/' . $post_url_ext . '/';
			#echo $post_url_end . '<br />$post_url_end<br />';
				
			
			$post_date = get_the_time('Y-m-d'); 				//Post Date
			#echo $post_date . ' ';
			$today_date = date('Y-m-d'); 						//Todays Date
			#echo $today_date . ' ';
				
  			$analytics->setDateRange($post_date, $today_date); 	//Set date in GA $analytics->setMonth(date('$post_date'), date('$new_date'));
				
  			#print_r($analytics->getVisitors()); 				//get array of visitors by day
  	
  			$pageViewURL = ($analytics->getPageviewsURL($post_url_end));	//Page views for specific URL
  			#echo $pageViewURL . ' $pageViewURL';
  			#var_dump ($pageViewURL);
  			$sumURL = 0;
  			foreach ($pageViewURL as $data) {
    			$sumURL = $sumURL + $data;
    			$total_sumURL = $total_sumURL + $data;
  			}
  			#echo ' <br />*** ' . $sumURL . ' ***<br /> ';			
			
  			$pageViewType = ($analytics->getPageviewsURL('/' . $post_type_map . '/'));	//Page views for the section landing page, e.g. the news page
  			$sumType = 0;
  			foreach ($pageViewType as $data) {
      			$sumType = $sumType + $data;
  			}
  				
  			$keywords = $analytics->getData(array(
            	'dimensions' => 'ga:keyword',
           	 	'metrics' => 'ga:visits',
            	'sort' => 'ga:keyword'
            	)
          	);	
          	
          	#SET UP POST ID AND AUTHOR ID DATA, POST DATE, GET LINK CLICKS DATA FROM GA 
          	$post_date_au = get_the_time('j-m-y');
	 		$post_id = $post->ID;
	 		$click_track_tag = '/yoast-ga/' . $post_id . '/' . $profile_author_id . '/outbound-article/';
			$clickURL = ($analytics->getPageviewsURL($click_track_tag));
  			$sumClick = 0;
			foreach ($clickURL as $data) {
    			$sumClick = $sumClick + $data;
  			}
			
			switch (get_post_type()) {		# CHECK POST TYPE AND ASSIGN APPROPRIATE TITLE, URL, COST AND GET BUTTON CLICKS DATA
			   
				case 'gp_advertorial':
					$post_title = 'Products';
					$post_url = '/eco-friendly-products';
					$post_price = '$89.00';
			  		$custom = get_post_custom($post->ID);
	 				$product_url = $custom["gp_advertorial_product_url"][0];	
	 				if ( !empty($product_url) ) {		# IF 'BUY IT' BUTTON ACTIVATED, GET CLICKS
	 					$click_track_tag_product_button = '/outbound/product-button/' . $post_id . '/' . $profile_author_id . '/' . $product_url . '/'; 
  						$clickURL_product_button = ($analytics->getPageviewsURL($click_track_tag_product_button));
  						foreach ($clickURL_product_button as $data) {
    						$sumClick = $sumClick + $data;
  						}
	 				}
	 				# GET PAGE IMPRESSIONS FOR OLD PRODUCT POSTS FROM BEFORE WE CHANGED URL AND ADD TO TOTAL
				 	$old_post_url_end = '/new-stuff/' . $post_url_ext . '/';
	 				$old_PageViewURL = ($analytics->getPageviewsURL($old_post_url_end));	//Page views for specific old URL
  					foreach ($old_PageViewURL as $data) {
    					$sumURL = $sumURL + $data;
    					$total_sumURL = $total_sumURL + $data;
  					}
		       		break;
				case 'gp_competitions':
					$post_title = 'Competitions';
					$post_url = '/competitions';
					$post_price = '$250.00';
		       		break;
		   		case 'gp_events':
		   			$post_title = 'Events';
		   			$post_url = '/events';
		   			$post_price = 'N/A';
		     		break;
		     	case 'gp_news':
				   	$post_title = 'News';
		   			$post_url = '/news';
		   			$post_price = 'N/A';		   			
		     		break;
		     	case 'gp_projects':
			    	$post_title = 'Projects';
			    	$post_url = '/projects';
			    	$post_price = 'N/A';
			        break;
			}
			
		  	if ($sumClick == 0) {			#IF NO CLICKS YET, DISPLAY 'Unavailable'
    			$sumClick = 'Unavailable';
    		}
			
# DISPLAY ROW OF ANALYTICS DATA FOR EACH POST BY THIS AUTHOR (PAGE IMPRESSIONS ETC)
			echo '<tr>				
					<td class="author_analytics_title"><a href="' . get_permalink($post->ID) . '" title="' . 
					esc_attr(get_the_title($post->ID)) . '" rel="bookmark">' . get_the_title($post->ID) . '</a></td>				
					<td class="author_analytics_type"><a href="' . $post_url . '">' . $post_title . '</a></td>					
					<td class="author_analytics_cost">' . $post_price . '</td>				
					<td class="author_analytics_date">' . $post_date_au . '</td>
					<td class="author_analytics_category_impressions">' . $sumType . '</td>
					<td class="author_analytics_page_impressions">' . $sumURL . '</td>	
					<td class="author_analytics_clicks">' . $sumClick . '</td>								
				</tr>';
		}
	}	
	?>
		</table>			

		<p>Your posts have been viewed a total of</p> 
		<p><span class="big-number"><?php echo $total_sumURL;?></span> times!</p>	
		<p></p>

<?php		
//Send to chargify metering

//Check is under cap

	if $weekly_clicks < $cap {

	return $weekly_clicks as $quantity;
	} elseif $weekly_clicks > $cap {

	return $cap return $quantity;
	}

	
	//Send a post request with Json data to this URL

	$url = 'https://greenpages.chargify.com/subscriptions/' . $subscription_id . '/components/' . $component_id . '/usages.json';

	curl -v -H "Content-Type: application/json" -X POST 
	-d ' "usage":{ "id": $subscription_id, "quantity":$quantity }' $url


	
		$i++;		
	}



//Google API data https://developers.google.com/analytics/devguides/reporting/core/dimsmets

//Date
ga:date

The date of the visit. An integer in the form YYYYMMDD.

//Hour
ga:hour

A two-digit hour of the day ranging from 00-23 in the timezone configured for the account. This value is also corrected for daylight savings time, adhering to all local rules for daylight savings time. If your timezone follows daylight savings time, there will be an apparent bump in the number of visits during the change-over hour (e.g. between 1:00 and 2:00) for the day per year when that hour repeats. A corresponding hour with zero visits will occur at the opposite changeover. (Google Analytics does not track visitor time more precisely than hours.)


$date_now = date();

//get 24 hour time in variable for GA 00 - 23




//The advertiser to who creates a post, get all their posts in an array

$author = ;

for the $author

get all post name by $author

//Get the total clicks for the posts for the last 7 days

between $date2_now and $date_1weekago

return google analytics for the outbound clicks for all the posts for the author

add the clicks together, return a total

$post_click_sum = ;

//Link the author ID to the chargify client ID

$author_as_advertiser = ;

//Check what cap plan they are on

$cap = ;

//Work out sum to bill client as clicks x CPC

$weekly_clicks = $post_click_sum;

//Check is under cap

if $weekly_clicks < $cap {

return $weekly_clicks as $quantity;
} elseif $weekly_clicks > $cap {

return $cap return $quantity;
}

//We only send the amount of clicks to chargify. It does the billing.

//Variables for chargify

$subscription_id = ;

$component_id = ;

//Send a post request with Json data to this URL

$url = 'https://greenpages.chargify.com/subscriptions/' . $subscription_id . '/components/' . $component_id . '/usages.json';

curl -v -H "Content-Type: application/json" -X POST 
-d ' "usage":{ "id": $subscription_id, "quantity":$quantity }' $url

Feature: Chargify Metered Component Usage JSON API
In order integrate my app with Chargify
As a developer
I want to record metered usage for a subscription

Background:
Given I am a valid API user
And I send and accept json

Scenario: Record metered usage
Given I have 1 product
And the product family has a metered component
And I have an active subscription to the first product
And I have this json usage data
"""
{
"usage":{
"quantity":5,
"memo":"My memo"
}
}
"""
When I send a POST request with the json data to https://[@subdomain].chargify.com/subscriptions/[@subscription.id]/components/[@component.id]/usages.json
Then the response status should be "200 OK"
And the response should be the json:
"""
{
"usage":{
"id":@auto generated@,
"quantity":5,
"memo":"My memo"
}
}
"""
And a usage will have been recorded








?>