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
        $search_terms = "'" . implode("','", $_GET['search_terms']) . "'";

        if(strcmp(gettype($_GET['search_times']), "array") == 0)
        {
            $search_times = $_GET['search_times'];
            $query = "select max(cnt) from (select count(*) as cnt from Tweets where search_term IN ($search_terms) and (created_at BETWEEN '$search_times[0]' and '$search_times[1]') group by search_term, CAST(created_at as DATE)) counter;";
        }
        else
        {
            $query = "select max(cnt) from (select count(*) as cnt from Tweets where search_term IN ($search_terms) group by search_term, CAST(created_at as DATE)) counter;";
        }
        $results = mysqli_query($con, $query);
        $row = mysqli_fetch_row($results);
        $max_count = intval($row[0]);

        // A big SQL injection can happen riiiiight here! Gotta prevent that later
        // like maybe after the project is done!!
        $term_counts = array();
        foreach ($_GET['search_terms'] as $term)
        {
            if(strcmp(gettype($_GET['search_times']), "array") == 0)
            {
                $search_times = $_GET['search_times'];
                $query = "select CAST(created_at as DATE), count(*) as cnt from Tweets where search_term = '$term' and (created_at BETWEEN '$search_times[0]' and '$search_times[1]') group by CAST(created_at as DATE);";
            }
            else
            {
                $query = "select CAST(created_at as DATE), count(*) as cnt from Tweets where search_term = '$term' group by CAST(created_at as DATE);";
            }
            $results = mysqli_query($con, $query);
            $term_counts[$term] = array();
            while ($row = mysqli_fetch_row($results))
            {
                $term_counts[$term][$row[0]] = intval($row[1]) / (float) $max_count;
            }
        }
        
        echo json_encode($term_counts);
    }
?>
