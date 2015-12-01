<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require __DIR__ . '/vendor/autoload.php';
include "credentials.php";

use TwitterOAuth\Auth\SingleUserAuth;
use TwitterOAuth\Serializer\ArraySerializer;
$credentials = array(
    'consumer_key' => CONSUMER_KEY,
    'consumer_secret' => CONSUMER_SECRET,
);
$serializer = new ArraySerializer();
$auth = new SingleUserAuth($credentials, $serializer);
function get_info($auth, $user_id)
{
    $params = array(
        'user_id' => $user_id,
    );

    $response = $auth->get('users/lookup', $params);
    $info = array();
    foreach ($response as $i => $resp)
    {
        $id = $resp['id'];
        $info[$id] = array();
        $info[$id]['name'] = $resp['name'];
        $info[$id]['screen_name'] = $resp['screen_name'];
        $info[$id]['profile_image_url'] = $resp['profile_image_url'];
    }
    return $info;
}

$con = mysqli_connect($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);
if (!$con)
{
    echo mysqli_error();
    die('Couldn\'t connect to the database');
}
else
{
    // A big SQL injection can happen riiiiight here! Gotta prevent that later
    // like maybe after the project is done!!
    $lat = $_GET['lat'];
    $lng = $_GET['lng'];
    $search_terms = "'" . implode("','", $_GET['search_terms']) . "'";
    if(strcmp(gettype($_GET['search_times']), "array") == 0)
    {
        $search_times = $_GET['search_times'];
        $query = "SELECT tweet_id, text, created_at, user_id, search_term FROM Tweets WHERE ABS(latitude - $lat) < 0.05 AND ABS(longitude - $lng) < 0.05 and search_term IN ($search_terms) and (created_at BETWEEN '$search_times[0]' and '$search_times[1]')";
    }
    else
    {
        $query = "SELECT tweet_id, text, created_at, user_id, search_term FROM Tweets WHERE ABS(latitude - $lat) < 0.05 AND ABS(longitude - $lng) < 0.05 and search_term IN ($search_terms)";
    }
    $results = mysqli_query($con, $query);

    $tweets = array();
    $counter = 0;
    $user_ids = "";
    while($row = mysqli_fetch_row($results))
    {
        if ($counter++ < 20)
        {
            $user_ids .= strval($row[3]) . ",";
        }
        array_push($tweets, $row);
    }
    $info = get_info($auth, rtrim($user_ids, ","));
    foreach ($tweets as $i => $tweet)
    {
        $user_id = $tweet[3];
        if (array_key_exists($user_id, $info))
        {
            $tweets[$i]['name'] = $info[$user_id]['name'];
            $tweets[$i]['screen_name'] = $info[$user_id]['screen_name'];
            $tweets[$i]['profile_image_url'] = $info[$user_id]['profile_image_url'];
        }
    }
    echo json_encode($tweets);
}
?>
