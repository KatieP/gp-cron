<?php
/**
 * 
 */

$db_connection = mysql_connect("127.0.0.1", "s2-wordpress", "7BXmxPmwy4LJZNhR") or die(mysql_error());
//$db_connection = mysql_connect("127.0.0.1", "root", "") or die(mysql_error());

mysql_select_db("s2-wordpress") or die(mysql_error());
//mysql_select_db("s1-wordpress") or die(mysql_error());

$sql = "SELECT ID
        FROM wp_users";

$db_result = mysql_query($sql);      
$data_set = mysql_num_rows($db_result);

echo 'User meta update begins ...';
echo PHP_EOL;

$i = 0;

while ($i < $data_set) { 

    mysql_data_seek($db_result, $i);
    $row = mysql_fetch_object($db_result);
    $user_id = $row->ID;

    $insert_query = 'INSERT INTO wp_usermeta 
			         SET user_id = "' . $user_id . '", meta_key="notification_setting", meta_value="weekly_email"';
    
    mysql_query($insert_query);
    
    echo 'User '. $user_id . ' updated.';
    echo PHP_EOL;
    
    $i++;
}

mysql_close($db_connection);

echo 'User meta update complete.';
echo PHP_EOL;

exit();

?>
