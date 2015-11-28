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
        $search_terms = "'" . implode("','", $_GET['search_terms']) . "'";
        $query = "SELECT place, sent_score FROM Tweets WHERE place != 'None' and search_term IN ($search_terms)";
        $results = mysqli_query($con, $query);

        $positions = array();
        while($row = mysqli_fetch_row($results))
        {

            $tag = "coordinates=[[[";
            $start_pos = strpos($row[0], $tag) + strlen($tag);
            $end_pos = strpos($row[0], "]", $start_pos);
            $coord_str = substr($row[0], $start_pos, $end_pos - $start_pos);
            $token = strtok($coord_str, ", ");

            $lat = floatval($token);
            $token = strtok(", ");
            $lng = floatval($token);
            $score = floatval($row[1]);

            array_push($positions, array($lng, $lat, $score));
        }
        echo json_encode($positions);
    }
?>
