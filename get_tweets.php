<?php
    include("credentials.php");
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
        while($row = mysqli_fetch_row($results))
        {
            array_push($tweets, $row);
        }
        echo json_encode($tweets);
    }
?>
