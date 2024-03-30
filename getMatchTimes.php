<?php

$timeZone = isset($timeZone) ? $timeZone : "UTC";

if(isset($eventKey)) {
    $matchTimesUrl = "https://www.thebluealliance.com/event/$eventKey#results";
    $matchTimesHtml = file_get_contents($matchTimesUrl);

    if(empty($matchTimesHtml)) {
        echo "<h1>No HTML data received</h1>";
    } else {
        // Use DOMDocument to parse HTML
        $matchTimesDom = new DOMDocument();
        libxml_use_internal_errors(true);
        $matchTimesDom->loadHTML($matchTimesHtml);
        libxml_clear_errors();

        // Use DOMXPath to query HTML elements
        $matchTimesXpath = new DOMXPath($matchTimesDom);

        // Find the tbody element containing table data
        $matchTimeTbodyNode = $matchTimesXpath->query('//table[@id="qual-match-table"]/tbody')->item(0);

        // Create a new DOMDocument for XML
        $xmlDoc = new DOMDocument();
        $xmlDoc->formatOutput = true;

        // Create root element
        $root = $xmlDoc->createElement('root');
        $xmlDoc->appendChild($root);

        // Loop through each row in the tbody
        foreach ($matchTimeTbodyNode->getElementsByTagName('tr') as $row) {
            // Check if the row has the class "visible-lg"
            if ($row->getAttribute('class') === 'visible-lg') {
                // Extract match name, number, and time
                $matchName = $row->getElementsByTagName('a')[0]->nodeValue;
                $matchData = explode(' ', $matchName);
                $number = trim($matchData[1]);
                $day = 'None'; // Default day is None
                $time = 'None'; // Default time is None

                // Extract time if available
                $timeElement = $row->getElementsByTagName('time');
                if ($timeElement->length > 0) {
                    $utcTime = trim($timeElement[0]->nodeValue);
                    $dateTime = new DateTime($utcTime, new DateTimeZone('UTC'));
                    $dateTime->setTimeZone(new DateTimeZone($timeZone));

                    $day = $dateTime->format('D');
                    $time = $dateTime->format('H:i');
                }

                // Create <match> element
                $match = $xmlDoc->createElement('match');
                $root->appendChild($match);

                // Append <number> element
                $numberElement = $xmlDoc->createElement('number', $number);
                $match->appendChild($numberElement);

                // Append <day> element
                $dayElement = $xmlDoc->createElement('day', $day);
                $match->appendChild($dayElement);

                // Append <time> element
                $timeElement = $xmlDoc->createElement('time', $time);
                $match->appendChild($timeElement);
            }
        }

        $xmlDoc->save('times.xml');
    }
}

