<?php

if(!isset($eventKey)) {
    die("<h1>No event given</h1>");
}

$feedUrl = "https://www.thebluealliance.com/event/" . $eventKey . "/feed";
$feedHtml = file_get_contents($feedUrl);

$onlyShowMatchesWithTeam = isset($onlyShowMatchesWithTeam) && isset($specialTeam) && $onlyShowMatchesWithTeam;
$showTeamRankings = isset($showTeamRankings) && $showTeamRankings;
$hidePastMatches = isset($hidePastMatches) && $hidePastMatches;
$showMatchTime = isset($showMatchTime) && $showMatchTime;
$timeZone = isset($timeZone) ? $timeZone : "UTC";

if($showTeamRankings) {
    include "getTeamRankings.php";
}

if($showMatchTime) {
    include "getMatchTimes.php";
}

// Check if HTML data is present
if(empty($feedHtml)) {
    echo "<h1>No HTML data received</h1>";
} else {
    // Check if RSS element is present
    if (strpos($feedHtml, "<rss") !== false) {
        // RSS element found, proceed with updating HTML content
        // Use DOMDocument to parse XML
        $dom = new DOMDocument();
        $dom->loadXML($feedHtml);

        // Get all <item> elements
        $items = $dom->getElementsByTagName('item');

        // Iterate over <item> elements
        foreach ($items as $item) {
            // Get the <description> element within each <item>
            $descriptionElement = $item->getElementsByTagName('description')->item(0);

            // Check if <description> element exists
            if ($descriptionElement) {
                // Get the inner HTML of <description> element
                $descriptionHTML = $dom->saveHTML($descriptionElement);

                if($onlyShowMatchesWithTeam) {
                    $passed = false;
                    preg_match_all('/<li.*?>(.*?)<\/li>/s', $descriptionHTML, $matches);
                    if(isset($matches[0])) {
                        foreach($matches[0] as $match) {
                            $teamNumber = str_replace("<li>", "", $match);
                            $teamNumber = str_replace("</li>", "", $teamNumber);
                            if($teamNumber == $specialTeam) {
                                $passed = true;
                            }
                        }
                        if(!$passed) {
                            continue;
                        }
                    }
                }

                // Check if we need to hide past matches
                if ($hidePastMatches) {
                    // Check if Red Alliance or Blue Alliance scores are not equal to -1
                    if (preg_match('/<h1>Red Alliance: (-?\d+)<\/h1>/', $descriptionHTML, $redMatches)) {
                        if ($redMatches[1] != -1) {
                            continue; // Skip this match
                        }
                    }
                    if (preg_match('/<h1>Blue Alliance: (-?\d+)<\/h1>/', $descriptionHTML, $blueMatches)) {
                        if ($blueMatches[1] != -1) {
                            continue; // Skip this match
                        }
                    }
                }

                // Get the <title> element within each <item>
                $titleElement = $item->getElementsByTagName('title')->item(0);

                // Check if <title> element exists
                if ($titleElement) {
                    // Get the text content of <title> element
                    $title = $titleElement->textContent;

                    // Replace "Quals x" with "Qualification x" in the title
                    $title = preg_replace('/Quals (\d+)/', 'Qualification $1', $title);

                    // Echo the modified title as <h1> header
                    echo '<h1 class="matchNumber">' . $title;

                    // Check if $showMatchTime is true
                    if ($showMatchTime) {
                        // Load times.xml
                        $timesXml = simplexml_load_file('times.xml');

                        // Find the corresponding match time using match number
                        $matchNumber = preg_replace('/[^0-9]/', '', $title); // Extract match number from title
                        $matchData = $timesXml->xpath("/root/match[number='$matchNumber']"); // Get match data

                        // If match data is found, extract day and time
                        if (!empty($matchData)) {
                            $day = (string) $matchData[0]->day;
                            $time = (string) $matchData[0]->time;

                            // Check if both day and time are not "None"
                            if ($day !== 'None' && $time !== 'None') {
                                // Create a DateTime object for the current time with the specified timezone
                                $currentTime = new DateTime('now', new DateTimeZone($timeZone));

                                // Format the current time with the specified timezone
                                $currentDate = $currentTime->format('D');

                                // Check if the extracted day is different from the current day

                                if ($day !== $currentDate) {
                                    echo ' (' . $day . ' ' . $time . ')';
                                } else {
                                    echo ' (' . $time . ')';
                                }
                            }
                        }
                    }

                    echo '</h1>';
                }


                // Extract and echo only the <li> elements from description
                preg_match_all('/<li.*?>(.*?)<\/li>/s', $descriptionHTML, $matches);

                $liCount = 0;
                echo '<div class="teamNumberContainer">';
                // Replace <li> with <p>
                if (!empty($matches[0])) {
                    foreach ($matches[0] as $match) {
                        $liCount++;
                        $class = ($liCount <= 3) ? 'red' : 'blue';
                        $teamNumber = str_replace("<li>", "", $match);
                        $teamNumber = str_replace("</li>", "", $teamNumber);
                        if(isset($specialTeam)) {
                            if($teamNumber == $specialTeam) {
                                $class .= ' specialNumber';
                            }
                        }
                        echo '<div class="teamNumber-' . $liCount . '">';
                        $match = str_replace('<li', '<p class="numberText ' . $class . '"', $match);
                        $match = str_replace('</li>', '</p>', $match);
                        echo $match;
                        if($showTeamRankings) {
                            include "showTeamRanking.php";
                        }
                        echo '</div>';
                    }
                }
                echo '</div>';
            }
        }
    } else {
        // RSS element not found, set HTML to a message indicating no event
        if(isset($eventKey)) {
            echo "<h1>No event: $eventKey</h1>";
        } else {
            echo "<h1>No event given</h1>";
        }
    }
}