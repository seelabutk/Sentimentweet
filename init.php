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
        $results = mysqli_query($con, "SELECT DISTINCT search_term FROM Tweets");
        $terms = array();
        while($row = mysqli_fetch_row($results))
        {
            array_push($terms, $row[0]);
        }
        echo json_encode($terms);
    }
?>
