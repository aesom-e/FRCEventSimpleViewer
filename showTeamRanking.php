<?php

$showSuffix = false;

if(isset($teamNumber)) {
    // Load the XML file
    $xml = simplexml_load_file('rankings.xml');

    // Search for the team with the specified team number
    $teamNode = $xml->xpath("/root/team[number='$teamNumber']")[0];

    if ($teamNode) {
        // Retrieve the rank of the team
        $rank = (int)$teamNode->rank;

        // Print the rank with the appropriate suffix (if asked to)
        echo '<p class="teamRank">';
        if(!$showSuffix) {
            echo "($rank)";
        } else {
            echo "$rank";
            if($rank > 10 && $rank < 20) {
                echo 'th';
            } else {
                switch ($rank % 10) {
                    case 1: echo 'st'; break;
                    case 2: echo 'nd'; break;
                    case 3: echo 'rd'; break;
                    default: echo 'th';
                }
            }
        }

        echo '</p>';
    }
}