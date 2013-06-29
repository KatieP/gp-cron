<?php
// Insert chargifyID, productID and advertiser=active into user_meta table 
		// chargifyID -> 
		// productID ->
		// reg_advertiser -> 1
		// advertiser_status -> active, used_up, paused
		
	//	meta_key = 'reg_advertiser' meta_value = 1
	//	('reg_advertiser'->1)
		
		mysql_query('INSERT INTO wp_usermeta 
					SET meta_key="advertiser_status", meta_value="active", user_ID = "' . $user_ID . '"
					AND meta_key="chargify_id", meta_value="'. $chargify_ID .'"
					AND meta_key="productID", meta_value="27023"');
		
		// chargifyID -> number
		// productID -> number
		
		// $99 /week plan productID =                         27028  API handle = 99-week-plan
		// $39 /week plan product ID =                        27029  API handle = 39-week-plan
		// Click at $1.90 / clicks ID =                       3207   metered billing
		// Current plan advertisers are on product ID =       27023  
		
		// $12  / week     $1.90 /click       component_id =  3207
		// $39  / week     $1.90 /click       component_id =  3207
		// $99  / week     $1.90 /click       component_id =  3207
		// $249 / week     $1.80 /click       component_id =  20016
		// $499 / week     $1.70 /click       component_id =  20017
		
		// Directory page $39 / month product ID = 27023 API handle = directory-page-renewal-39-monthly-fee

// meta_key = budget 			-> 	meta_value = 'used_up','active' or 'paused'
// meta_key = reg_advertiser 	-> 	meta_value = 1;
// meta_key = charigfy_ID 		-> 	meta_value = number;
// Meta_key = chargify_product_ID 	-> 	meta_value = number;

// Biome 				wp=259	 pi=27028	ch=1733583 	sub=1758911 signup=05/03/2012 #unix=1336003200
// New Leaf Corporate 	wp=1505  pi=27029	ch=1787860 	sub=1815521 signup=05/18/2012 #unix=1337299200
// 15 Trees 			wp=1936	 pi=3207	ch=2045804 	sub=2081871 signup=07/28/2012 #unix=1343433600
// Climate Friendly  	wp=1551  pi=27023	ch=1828803 	sub=1859829 signup=05/27/2012 #unix=1338076800


		
'INSERT INTO wp_usermeta 
SET meta_key="budget_status", meta_value="active", user_id = "2"';

'INSERT INTO wp_usermeta					 
SET meta_key="productID", meta_value="27023", user_id = "2"';
					
'INSERT INTO wp_usermeta					 
SET meta_key="chargifyID", meta_value="27023", user_id = "2"';	

'INSERT INTO wp_usermeta					 
SET meta_key="subscriptionID", meta_value="27023", user_id = "2"';

'INSERT INTO wp_usermeta					 
SET meta_key="adv_signup_time", meta_value="27023", user_id = "2"';			

'INSERT INTO wp_usermeta 
SET meta_key="advertiser_status", meta_value="active"
WHERE meta_value = "reg_advertiser"';

/** Test data insert queries	
  
 ______________________________________________________________________________________________________________

INSERT INTO wp_usermeta 
SET meta_key="budget_status", meta_value="active", user_id = "259";

INSERT INTO wp_usermeta 
SET meta_key="budget_status", meta_value="active", user_id = "1505";

INSERT INTO wp_usermeta 
SET meta_key="budget_status", meta_value="active", user_id = "1936";

INSERT INTO wp_usermeta 
SET meta_key="budget_status", meta_value="active", user_id = "1551";

______________________________________________________________________________________________________________

INSERT INTO wp_usermeta 
SET meta_key="product_id", meta_value="27028", user_id = "259";

INSERT INTO wp_usermeta 
SET meta_key="product_id", meta_value="27029", user_id = "1505";

INSERT INTO wp_usermeta 
SET meta_key="product_id", meta_value="3207", user_id =  "1936";

INSERT INTO wp_usermeta 
SET meta_key="product_id", meta_value="27023", user_id = "1551";

______________________________________________________________________________________________________________

INSERT INTO wp_usermeta 
SET meta_key="customer_id", meta_value="1733583", user_id = "259";

INSERT INTO wp_usermeta 
SET meta_key="customer_id", meta_value="1787860", user_id = "1505";

INSERT INTO wp_usermeta 
SET meta_key="customer_id", meta_value="2045804", user_id =  "1936";

INSERT INTO wp_usermeta 
SET meta_key="customer_id", meta_value="1828803", user_id = "1551";

______________________________________________________________________________________________________________

INSERT INTO wp_usermeta 
SET meta_key="subscription_id", meta_value="1758911", user_id = "259";

INSERT INTO wp_usermeta 
SET meta_key="subscription_id", meta_value="1815521", user_id = "1505";

INSERT INTO wp_usermeta 
SET meta_key="subscription_id", meta_value="2081871", user_id =  "1936";

INSERT INTO wp_usermeta 
SET meta_key="subscription_id", meta_value="1859829", user_id = "1551";

______________________________________________________________________________________________________________

INSERT INTO wp_usermeta 
SET meta_key="adv_signup_time", meta_value="1336003200", user_id = "259";

INSERT INTO wp_usermeta 
SET meta_key="adv_signup_time", meta_value="1337299200", user_id = "1505";

INSERT INTO wp_usermeta 
SET meta_key="adv_signup_time", meta_value="1343433600", user_id =  "1936";

INSERT INTO wp_usermeta 
SET meta_key="adv_signup_time", meta_value="1338076800", user_id = "1551";

______________________________________________________________________________________________________________ 
  
 **/	
		
?>

