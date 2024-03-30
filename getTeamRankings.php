<?php


if(isset($eventKey)) {
    $rankingUrl = "https://www.thebluealliance.com/event/$eventKey#rankings";
    $rankingHtml = file_get_contents($rankingUrl);

    // Check if HTML data is present
    if(empty($rankingHtml)) {
        echo "<h1>No HTML data received</h1>";
    } else {
        // Use DOMDocument to parse HTML
        $teamRankingsDom = new DOMDocument();
        libxml_use_internal_errors(true);
        $teamRankingsDom->loadHTML($rankingHtml);
        libxml_clear_errors(); // Clear libxml errors

        // Use DOMXPath to query HTML elements
        $teamRankingsXpath = new DOMXPath($teamRankingsDom);

        // Find the tbody element containing table data
        $teamRankingsTbodyNode = $teamRankingsXpath->query('//table[@id="rankingsTable"]/tbody')->item(0);

        if ($teamRankingsTbodyNode) {
            // Create XML structure
            $teamRankingsXml = new SimpleXMLElement('<root></root>');

            // Iterate through table rows (excluding header row)
            $teamRankingsRows = $teamRankingsTbodyNode->getElementsByTagName('tr');
            foreach ($teamRankingsRows as $row) {
                // Get data from each column
                $columns = $row->getElementsByTagName('td');

                // Check if all columns exist before accessing their values
                if ($columns->length == 11) {
                    // Extract data from each column and add it to the XML structure
                    $team = $teamRankingsXml->addChild('team');

                    // Initialize arrays to store values for average and record
                    $averageValues = [];
                    $recordValues = [];

                    // Extract and add data from each column to the XML structure
                    for ($i = 0; $i < $columns->length; $i++) {
                        $value = trim($columns->item($i)->textContent);
                        if ($value !== null) {
                            switch ($i) {
                                case 0:
                                    $team->addChild('rank', $value);
                                    break;
                                case 1:
                                    $team->addChild('number', $value);
                                    break;
                                case 2:
                                    $team->addChild('score', $value);
                                    break;
                                case 3:
                                    $averageValues['coop'] = $value;
                                    break;
                                case 4:
                                    $averageValues['match'] = $value;
                                    break;
                                case 5:
                                    $averageValues['auto'] = $value;
                                    break;
                                case 6:
                                    $averageValues['state'] = $value;
                                    break;
                                case 7:
                                    list($wins, $losses, $ties) = explode('-', $value);
                                    $recordValues['wins'] = $wins;
                                    $recordValues['losses'] = $losses;
                                    $recordValues['ties'] = $ties;
                                    break;
                            }
                        }
                    }

                    // Add average values
                    $average = $team->addChild('average');
                    foreach ($averageValues as $key => $avgValue) {
                        $average->addChild($key, $avgValue);
                    }

                    // Add record values
                    $record = $team->addChild('record');
                    foreach ($recordValues as $key => $recValue) {
                        $record->addChild($key, $recValue);
                    }

                    // Add disqualifiedMatches, played, and points after average and record
                    $team->addChild('disqualifications', trim($columns->item(8)->textContent));
                    $team->addChild('played', trim($columns->item(9)->textContent));
                    $team->addChild('points', trim($columns->item(10)->textContent));
                }
            }
            $xmlString = $teamRankingsXml->asXML();

            // Add line breaks and indentation for formatting
            $teamRankingsDom = new DOMDocument();
            $teamRankingsDom->preserveWhiteSpace = false;
            $teamRankingsDom->formatOutput = true;
            $teamRankingsDom->loadXML($xmlString);
            $formattedXmlString = $teamRankingsDom->saveXML();

            file_put_contents('rankings.xml', $formattedXmlString);
        } else {
            echo "<h1>No data found</h1>";
        }
    }
}