<?php 

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

function get_users() {

	//Get user emails and their location
	$sql_user = 'SELECT DISTINCT user_email, display_name, ID
                 FROM   wp_users';

	$db_result = mysql_query($sql_user);

	if (! $db_result){
	   echo('Database error: ' . mysql_error());
	}
		
	return $db_result;
}

function send_notifcations() {

    $users = get_users();
    $i = 0;
    $data_set = mysql_num_rows($users);    

    while ($i < $data_set) {
		
        mysql_data_seek($users, $i);
	    $row = mysql_fetch_object($users);
        $user_id = (int) $row->ID;
	    $user_email = $row->user_email;
        
	    if ( ($user_id >= 18790) && ($user_id <= 19191) ) {
            send_email_notification($user_email);
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

function send_email_notification($user_email) {
  $ch = curl_init();

  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($ch, CURLOPT_USERPWD, 'api:key-2848zj9zqy6vzlec3qy1hwber1tsy1i2');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v2/greenpag.es/messages');
  
  curl_setopt($ch, CURLOPT_POSTFIELDS, array('from' => 'noreply@greenpag.es',
                                             'to' => $user_email,
                                             
                                             'subject' => 'Set your location for The Green Razor, here\'s your log in details!',
                                             'text' => 'Some text',
                                             'html' => '
                                                 <html><head><title></title><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><meta name="viewport" content="width=320, target-densitydpi=device-dpi">
<style type="text/css">
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
#outlook a { padding: 0; }	
body { width: 100% !important; }
.ReadMsgBody { width: 100%; }
.ExternalClass { width: 100%; display:block !important; } 
body { background-color: #ececec; margin: 0; padding: 0; }
img { outline: none; text-decoration: none; display: block;}
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
#background-table { background-color: #ececec; }
#top-bar { border-radius:6px 6px 0px 0px; -moz-border-radius: 6px 6px 0px 0px; -webkit-border-radius:6px 6px 0px 0px; -webkit-font-smoothing: antialiased; background-color: #61c201; color: #61c201; }
#top-bar a { font-weight: bold; color: #61c201; text-decoration: none;}
#footer { border-radius:0px 0px 6px 6px; -moz-border-radius: 0px 0px 6px 6px; -webkit-border-radius:0px 0px 6px 6px; -webkit-font-smoothing: antialiased; }
body, td { font-family: "Helvetica Neue", Arial, Helvetica, Geneva, sans-serif; }
.header-content, .footer-content-left, .footer-content-right { -webkit-text-size-adjust: none; -ms-text-size-adjust: none; }
.header-content { font-size: 12px; color: #61c201; }
.header-content a { font-weight: bold; color: #61c201; text-decoration: none; }
#headline p { color: #01aed8; font-family: "Helvetica Neue", Arial, Helvetica, Geneva, sans-serif; font-size: 36px; text-align: left; margin-top:0px; margin-bottom:30px; }
#headline p a { color: #01aed8; text-decoration: none; }
.article-title { font-size: 18px; line-height:24px; color: #01aed8; font-weight:bold; margin-top:0px; margin-bottom:18px; font-family: "Helvetica Neue", Arial, Helvetica, Geneva, sans-serif; }
.article-title a { color: #01aed8; text-decoration: none; }
.article-title.with-meta {margin-bottom: 0;}
.article-meta { font-size: 13px; line-height: 20px; color: #ccc; font-weight: bold; margin-top: 0;}
.article-content { font-size: 13px; line-height: 18px; color: #444444; margin-top: 0px; margin-bottom: 18px; font-family: "Helvetica Neue", Arial, Helvetica, Geneva, sans-serif; }
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
<style _tmplitem="75" >
.article-content ol, .article-content ul {
   margin: 0 0 0 24px !important;
   padding: 0 !important;
   list-style-position: inside !important;
}
</style>
<![endif]--><meta name="robots" content="noindex,nofollow">
<meta property="og:title" content="Set your location for The Green Razor, here\'s your log in details!">
</head><body style="width:100% !important;background-color:#ececec;margin-top:0;margin-bottom:0;margin-right:0;margin-left:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;" cz-shortcut-listen="true"><table width="100%" cellpadding="0" cellspacing="0" border="0" id="background-table" style="background-color:#ececec;">
	<tbody><tr style="border-collapse:collapse;">
		<td align="center" bgcolor="#ececec" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
        	<table class="w640" width="640" cellpadding="0" cellspacing="0" border="0" style="margin-top:0;margin-bottom:0;margin-right:10px;margin-left:10px;">
            	<tbody><tr style="border-collapse:collapse;"><td class="w640" width="640" height="20" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                
            	<tr style="border-collapse:collapse;">
                	<td class="w640" width="640" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                        <table id="top-bar" class="w640" width="640" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="border-radius:6px 6px 0px 0px;-moz-border-radius:6px 6px 0px 0px;-webkit-border-radius:6px 6px 0px 0px;-webkit-font-smoothing:antialiased;background-color:#61c201;color:#61c201;">
    <tbody><tr style="border-collapse:collapse;">
        <td class="w15" width="15" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td>
        <td class="w325" width="350" valign="middle" align="left" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
            <table class="w325" width="350" cellpadding="0" cellspacing="0" border="0">
                <tbody><tr style="border-collapse:collapse;"><td class="w325" width="350" height="8" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
            </tbody></table>
            <div class="header-content" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;font-size:12px;color:#61c201;"><a href="http://greenpages2.createsend1.com/t/r-e-buhhhik-tryhldtiju-r/" style="font-weight:bold;color:#61c201;text-decoration:none;">Web Version</a><span class="hide">&nbsp;&nbsp;|&nbsp; <a href="http://greenpages2.updatemyprofile.com/r-buhhhik-97F827F4-tryhldtiju-y" lang="en" style="font-weight:bold;color:#61c201;text-decoration:none;">Update preferences</a>&nbsp;&nbsp;|&nbsp; <a href="http://greenpages2.createsend1.com/t/r-u-buhhhik-tryhldtiju-j/" style="font-weight:bold;color:#61c201;text-decoration:none;">Unsubscribe</a></span></div>
            <table class="w325" width="350" cellpadding="0" cellspacing="0" border="0">
                <tbody><tr style="border-collapse:collapse;"><td class="w325" width="350" height="8" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
            </tbody></table>
        </td>
        <td class="w30" width="30" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td>
        <td class="w255" width="255" valign="middle" align="right" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
            <table class="w255" width="255" cellpadding="0" cellspacing="0" border="0">
                <tbody><tr style="border-collapse:collapse;"><td class="w255" width="255" height="8" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
            </tbody></table>
            <table cellpadding="0" cellspacing="0" border="0">
    <tbody><tr style="border-collapse:collapse;">
        
        
        
    </tr>
</tbody></table>
            <table class="w255" width="255" cellpadding="0" cellspacing="0" border="0">
                <tbody><tr style="border-collapse:collapse;"><td class="w255" width="255" height="8" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
            </tbody></table>
        </td>
        <td class="w15" width="15" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td>
    </tr>
</tbody></table>
                        
                    </td>
                </tr>
                <tr style="border-collapse:collapse;">
                <td id="header" class="w640" width="640" align="center" bgcolor="#ffffff" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
    
    <table class="w640" width="640" cellpadding="0" cellspacing="0" border="0">
        <tbody><tr style="border-collapse:collapse;"><td class="w30" width="30" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td><td class="w580" width="580" height="30" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td><td class="w30" width="30" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
        <tr style="border-collapse:collapse;">
            <td class="w30" width="30" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td>
            <td class="w580" width="580" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                <div align="center" id="headline">
                    <p style="color:#01aed8;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;font-size:36px;text-align:left;margin-top:0px;margin-bottom:30px;">
                        <strong>Here\'s your greenpag.es username &amp; password!</strong>
                    </p>
                </div>
            </td>
            <td class="w30" width="30" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td>
        </tr>
    </tbody></table>
    
    
</td>
                </tr>
                
                <tr style="border-collapse:collapse;"><td class="w640" width="640" height="30" bgcolor="#ffffff" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                <tr id="simple-content-row" style="border-collapse:collapse;"><td class="w640" width="640" bgcolor="#ffffff" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
    <table class="w640" width="640" cellpadding="0" cellspacing="0" border="0">
        <tbody><tr style="border-collapse:collapse;">
            <td class="w30" width="30" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td>
            <td class="w580" width="580" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                
                        <table class="w580" width="580" cellpadding="0" cellspacing="0" border="0">
                            <tbody><tr style="border-collapse:collapse;">
                                <td class="w580" width="580" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                    <p align="left" class="article-title" style="font-size:18px;line-height:24px;color:#01aed8;font-weight:bold;margin-top:0px;margin-bottom:18px;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;">Set your location for the Green Razor</p>
                                    <div align="left" class="article-content" style="font-size:13px;line-height:18px;color:#444444;margin-top:0px;margin-bottom:18px;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;">
                                        <p style="margin-bottom:15px;">
	Green Pages is now a global platform! That means that every member receives the Green Razor tailored by location. Your Green Razor settings can now be maintained via your greenpag.es member account.</p>
<p style="margin-bottom:15px;">
	You current location is set as Sydney, Australia. If this is not correct, please do <a href="http://greenpages2.createsend1.com/t/r-l-buhhhik-tryhldtiju-d/" style="color:#01aed8;font-weight:bold;text-decoration:none;">login and change your location</a>. To log in you can use your email address as both your username and password.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr style="border-collapse:collapse;"><td class="w580" width="580" height="10" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                        </tbody></table>
                    
                        <table class="w580" width="580" cellpadding="0" cellspacing="0" border="0">
                            <tbody><tr style="border-collapse:collapse;">
                                <td class="w580" width="580" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                    <p align="left" class="article-title" style="font-size:18px;line-height:24px;color:#01aed8;font-weight:bold;margin-top:0px;margin-bottom:18px;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;">Username: </p>
                                    <div align="left" class="article-content" style="font-size:13px;line-height:18px;color:#444444;margin-top:0px;margin-bottom:18px;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;">
                                        <p style="margin-bottom:15px;">
                                            '. $user_email .'
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <tr style="border-collapse:collapse;"><td class="w580" width="580" height="10" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                        </tbody></table>
                    
                        <table class="w580" width="580" cellpadding="0" cellspacing="0" border="0">
                            <tbody><tr style="border-collapse:collapse;">
                                <td class="w580" width="580" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                    <p align="left" class="article-title" style="font-size:18px;line-height:24px;color:#01aed8;font-weight:bold;margin-top:0px;margin-bottom:18px;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;">Password:</p>
                                    <div align="left" class="article-content" style="font-size:13px;line-height:18px;color:#444444;margin-top:0px;margin-bottom:18px;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;">
                                        <p style="margin-bottom:15px;">
                                            '. $user_email .'
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <tr style="border-collapse:collapse;"><td class="w580" width="580" height="10" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                        </tbody></table>
                    
                        <table class="w580" width="580" cellpadding="0" cellspacing="0" border="0">
                            <tbody><tr style="border-collapse:collapse;">
                                <td class="w580" width="580" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                    <p align="left" class="article-title" style="font-size:18px;line-height:24px;color:#01aed8;font-weight:bold;margin-top:0px;margin-bottom:18px;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;"></p>
                                </td>
                            </tr>
                            <tr style="border-collapse:collapse;">
                                <td class="w580" width="580" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"><a href="http://greenpages2.createsend1.com/t/r-l-buhhhik-tryhldtiju-n/"><img label="Image" class="w580" width="215" border="0" src="http://i1.createsend1.com/ei/r/BE/282/8F1/csimport/ScreenShot2013-09-13at6.10.37AM.061142.png" height="43" style="outline-style:none;text-decoration:none;display:block;"></a></td>
                            </tr>
                            <tr style="border-collapse:collapse;"><td class="w580" width="580" height="15" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                            <tr style="border-collapse:collapse;">
                                <td class="w580" width="580" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                    <div align="left" class="article-content" style="font-size:13px;line-height:18px;color:#444444;margin-top:0px;margin-bottom:18px;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;">
                                        
                                    </div>
                                </td>
                            </tr>
                            <tr style="border-collapse:collapse;"><td class="w580" width="580" height="10" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                        </tbody></table>
                    
                        <table class="w580" width="580" cellpadding="0" cellspacing="0" border="0">
                            <tbody><tr style="border-collapse:collapse;">
                                <td class="w580" width="580" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                    <p align="left" class="article-title" style="font-size:18px;line-height:24px;color:#01aed8;font-weight:bold;margin-top:0px;margin-bottom:18px;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;">News directly from environmental groups</p>
                                    <div align="left" class="article-content" style="font-size:13px;line-height:18px;color:#444444;margin-top:0px;margin-bottom:18px;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;">
                                        <p style="margin-bottom:15px;">
	Greenpag.es only publishes news articles directly from environmental groups and universities. That means no journalists, bloggers or opinions. All of the greenpag.es news is real information direct from the primary source. <a href="http://www.greenpag.es/about/partners/">View content partners here.</a> &nbsp;</p>
<p style="margin-bottom:15px;">
	&nbsp;</p>
                                    </div>
                                </td>
                            </tr>
                            <tr style="border-collapse:collapse;"><td class="w580" width="580" height="10" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                        </tbody></table>
                    
                        <table class="w580" width="580" cellpadding="0" cellspacing="0" border="0">
                            <tbody><tr style="border-collapse:collapse;">
                                <td class="w580" width="580" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                    <p align="left" class="article-title" style="font-size:18px;line-height:24px;color:#01aed8;font-weight:bold;margin-top:0px;margin-bottom:18px;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;">What you can do with greenpag.es</p>
                                </td>
                            </tr>
                            <tr style="border-collapse:collapse;">
                                <td class="w580" width="580" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td>
                            </tr>
                            <tr style="border-collapse:collapse;"><td class="w580" width="580" height="15" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                            <tr style="border-collapse:collapse;">
                                <td class="w580" width="580" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                    <div align="left" class="article-content" style="font-size:13px;line-height:18px;color:#444444;margin-top:0px;margin-bottom:18px;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;">
                                        
                                    </div>
                                </td>
                            </tr>
                            <tr style="border-collapse:collapse;"><td class="w580" width="580" height="10" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                        </tbody></table>
                    
                        <table class="w580" width="580" cellpadding="0" cellspacing="0" border="0">
                            <tbody><tr style="border-collapse:collapse;">
                                <td class="w180" width="180" valign="top" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                    <table class="w180" width="180" cellpadding="0" cellspacing="0" border="0">
                                        <tbody><tr style="border-collapse:collapse;">
                                            <td class="w180" width="180" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"><a href="http://greenpages2.createsend1.com/t/r-l-buhhhik-tryhldtiju-p/"><img label="Image" class="w180" width="180" border="0" src="http://i2.createsend1.com/ei/r/BE/282/8F1/csimport/Ceiling_Merri.171327.jpg" height="150" style="outline-style:none;text-decoration:none;display:block;"></a></td>
                                        </tr>
                                        <tr style="border-collapse:collapse;"><td class="w180" width="180" height="10" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                                        <tr style="border-collapse:collapse;">
                                            <td class="w180" width="180" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                                <div align="left" class="article-content" style="font-size:13px;line-height:18px;color:#444444;margin-top:0px;margin-bottom:18px;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;">
                                                    <p style="margin-bottom:15px;">
	<a href="http://greenpages2.createsend1.com/t/r-l-buhhhik-tryhldtiju-x/" style="color:#01aed8;font-weight:bold;text-decoration:none;">Add your event to the calendar</a></p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr style="border-collapse:collapse;"><td class="w180" width="180" height="10" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                                    </tbody></table>
                                </td>
                                <td width="20" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td>
                                <td class="w180" width="180" valign="top" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                    <table class="w180" width="180" cellpadding="0" cellspacing="0" border="0">
                                        <tbody><tr style="border-collapse:collapse;">
                                            <td class="w180" width="180" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"><a href="http://greenpages2.createsend1.com/t/r-l-buhhhik-tryhldtiju-m/"><img label="Image" class="w180" width="180" border="0" src="http://i3.createsend1.com/ei/r/BE/282/8F1/csimport/press-release-icc.175744.jpg" height="149" style="outline-style:none;text-decoration:none;display:block;"></a></td>
                                        </tr>
                                        <tr style="border-collapse:collapse;"><td class="w180" width="180" height="10" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                                        <tr style="border-collapse:collapse;">
                                            <td class="w180" width="180" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                                <div align="left" class="article-content" style="font-size:13px;line-height:18px;color:#444444;margin-top:0px;margin-bottom:18px;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;">
                                                    <p style="margin-bottom:15px;">
	<a href="http://greenpages2.createsend1.com/t/r-l-buhhhik-tryhldtiju-c/" style="color:#01aed8;font-weight:bold;text-decoration:none;">Promote Community Projects</a></p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr style="border-collapse:collapse;"><td class="w180" width="180" height="10" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                                    </tbody></table>
                                </td>
                                <td width="20" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td>
                                <td class="w180" width="180" valign="top" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                    <table class="w180" width="180" cellpadding="0" cellspacing="0" border="0">
                                        <tbody><tr style="border-collapse:collapse;">
                                            <td class="w180" width="180" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"><a href="http://greenpages2.createsend1.com/t/r-l-buhhhik-tryhldtiju-q/"><img label="Image" class="w180" width="180" border="0" src="http://i4.createsend1.com/ei/r/BE/282/8F1/csimport/slide2.185728.png" height="151" style="outline-style:none;text-decoration:none;display:block;"></a></td>
                                        </tr>
                                        <tr style="border-collapse:collapse;"><td class="w180" width="180" height="10" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                                        <tr style="border-collapse:collapse;">
                                            <td class="w180" width="180" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                                <div align="left" class="article-content" style="font-size:13px;line-height:18px;color:#444444;margin-top:0px;margin-bottom:18px;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;">
                                                    <p style="margin-bottom:15px;">
	<a href="http://greenpages2.createsend1.com/t/r-l-buhhhik-tryhldtiju-a/" style="color:#01aed8;font-weight:bold;text-decoration:none;">Find eco friendly products</a> or <a href="http://greenpages2.createsend1.com/t/r-l-buhhhik-tryhldtiju-f/" style="color:#01aed8;font-weight:bold;text-decoration:none;">advertise your business</a></p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr style="border-collapse:collapse;"><td class="w180" width="180" height="10" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                                    </tbody></table>
                                </td>
                            </tr>
                        </tbody></table>
                    
                        <table class="w580" width="580" cellpadding="0" cellspacing="0" border="0">
                            <tbody><tr style="border-collapse:collapse;">
                                <td class="w180" width="180" valign="top" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                    <table class="w180" width="180" cellpadding="0" cellspacing="0" border="0">
                                        <tbody><tr style="border-collapse:collapse;">
                                            <td class="w180" width="180" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"><a href="http://greenpages2.createsend1.com/t/r-l-buhhhik-tryhldtiju-z/"><img label="Image" class="w180" width="180" border="0" src="http://i5.createsend1.com/ei/r/BE/282/8F1/csimport/ScreenShot2013-09-10at7.08.53PM.193354.png" height="150" style="outline-style:none;text-decoration:none;display:block;"></a></td>
                                        </tr>
                                        <tr style="border-collapse:collapse;"><td class="w180" width="180" height="10" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                                        <tr style="border-collapse:collapse;">
                                            <td class="w180" width="180" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                                <div align="left" class="article-content" style="font-size:13px;line-height:18px;color:#444444;margin-top:0px;margin-bottom:18px;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;">
                                                    <p style="margin-bottom:15px;">
	<a href="http://greenpages2.createsend1.com/t/r-l-buhhhik-tryhldtiju-v/" style="color:#01aed8;font-weight:bold;text-decoration:none;">World map: See the world\'s environmental issues</a></p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr style="border-collapse:collapse;"><td class="w180" width="180" height="10" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                                    </tbody></table>
                                </td>
                                <td width="20" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td>
                                <td class="w180" width="180" valign="top" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                    <table class="w180" width="180" cellpadding="0" cellspacing="0" border="0">
                                        <tbody><tr style="border-collapse:collapse;">
                                            <td class="w180" width="180" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"><a href="http://greenpages2.createsend1.com/t/r-l-buhhhik-tryhldtiju-e/"><img label="Image" class="w180" width="180" border="0" src="http://i6.createsend1.com/ei/r/BE/282/8F1/csimport/ScreenShot2013-09-10at7.36.48PM.195924.png" height="148" style="outline-style:none;text-decoration:none;display:block;"></a></td>
                                        </tr>
                                        <tr style="border-collapse:collapse;"><td class="w180" width="180" height="10" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                                        <tr style="border-collapse:collapse;">
                                            <td class="w180" width="180" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                                <div align="left" class="article-content" style="font-size:13px;line-height:18px;color:#444444;margin-top:0px;margin-bottom:18px;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;">
                                                    <p style="margin-bottom:15px;">
	<a href="http://greenpages2.createsend1.com/t/r-l-buhhhik-tryhldtiju-s/" style="color:#01aed8;font-weight:bold;text-decoration:none;">Upvote your favourite stories so more people see them</a></p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr style="border-collapse:collapse;"><td class="w180" width="180" height="10" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                                    </tbody></table>
                                </td>
                                <td width="20" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td>
                                <td class="w180" width="180" valign="top" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                    <table class="w180" width="180" cellpadding="0" cellspacing="0" border="0">
                                        <tbody><tr style="border-collapse:collapse;">
                                            <td class="w180" width="180" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"><a href="http://greenpages2.createsend1.com/t/r-l-buhhhik-tryhldtiju-g/"><img label="Image" class="w180" width="180" border="0" src="http://i7.createsend1.com/ei/r/BE/282/8F1/csimport/ScreenShot2013-09-10at8.17.07PM.202900.png" height="150" style="outline-style:none;text-decoration:none;display:block;"></a></td>
                                        </tr>
                                        <tr style="border-collapse:collapse;"><td class="w180" width="180" height="10" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                                        <tr style="border-collapse:collapse;">
                                            <td class="w180" width="180" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
                                                <div align="left" class="article-content" style="font-size:13px;line-height:18px;color:#444444;margin-top:0px;margin-bottom:18px;font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;">
                                                    <p style="margin-bottom:15px;">
	<a href="http://greenpages2.createsend1.com/t/r-l-buhhhik-tryhldtiju-w/" style="color:#01aed8;font-weight:bold;text-decoration:none;">Geolocation: The site will show content near your local area</a></p>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr style="border-collapse:collapse;"><td class="w180" width="180" height="10" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                                    </tbody></table>
                                </td>
                            </tr>
                        </tbody></table>
                    
            </td>
            <td class="w30" width="30" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td>
        </tr>
    </tbody></table>
</td></tr>
                <tr style="border-collapse:collapse;"><td class="w640" width="640" height="15" bgcolor="#ffffff" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
                
                <tr style="border-collapse:collapse;">
                <td class="w640" width="640" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
    <table id="footer" class="w640" width="640" cellpadding="0" cellspacing="0" border="0" bgcolor="#61c201" style="border-radius:0px 0px 6px 6px;-moz-border-radius:0px 0px 6px 6px;-webkit-border-radius:0px 0px 6px 6px;-webkit-font-smoothing:antialiased;background-color:#61c201;color:#e2e2e2;">
        <tbody><tr style="border-collapse:collapse;"><td class="w30" width="30" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td><td class="w580 h0" width="360" height="30" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td><td class="w0" width="60" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td><td class="w0" width="160" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td><td class="w30" width="30" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
        <tr style="border-collapse:collapse;">
            <td class="w30" width="30" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td>
            <td class="w580" width="360" valign="top" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
            <span class="hide"><p id="permission-reminder" align="left" class="footer-content-left" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;font-size:12px;line-height:15px;color:#e2e2e2;margin-top:0px;margin-bottom:15px;white-space:normal;"><span>You\'re receiving this because you are subscribed to Green Pages email notifications. </span></p></span>
            <p align="left" class="footer-content-left" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;font-size:12px;line-height:15px;color:#e2e2e2;margin-top:0px;margin-bottom:15px;"><a href="http://www.greenpag.es/forms/profile-notifications/" lang="en" style="color:#E2E2E2;text-decoration:none;font-weight:bold;">Edit your email preferences</a></p>
            </td>
            <td class="hide w0" width="60" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td>
            <td class="hide w0" width="160" valign="top" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;">
            <p id="street-address" align="right" class="footer-content-right" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;font-size:11px;line-height:16px;margin-top:0px;margin-bottom:15px;color:#E2E2E2;white-space:normal;"></p>
            </td>
            <td class="w30" width="30" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td>
        </tr>
        <tr style="border-collapse:collapse;"><td class="w30" width="30" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td><td class="w580 h0" width="360" height="15" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td><td class="w0" width="60" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td><td class="w0" width="160" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td><td class="w30" width="30" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
    </tbody></table>
</td>
                </tr>
                <tr style="border-collapse:collapse;"><td class="w640" width="640" height="60" style="font-family:"Helvetica Neue", Arial, Helvetica, Geneva, sans-serif;border-collapse:collapse;"></td></tr>
            </tbody></table>
        </td>
	</tr>
</tbody></table>
</body></html>
                                             '));
                                            

  $result = curl_exec($ch); 
 
  curl_close($ch);

  return $result;
 
}

// send_notifcations();

echo '_______________________________________________________';
echo PHP_EOL; 
echo 'Ends';
echo PHP_EOL; 
echo '_______________________________________________________';
echo PHP_EOL;

mysql_close($db_connection);
exit();

?>