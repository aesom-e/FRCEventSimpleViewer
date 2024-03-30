<html lang="enCa">
<head>
    <title>FRC Event Simple Viewer</title>
    <link rel="stylesheet" href="base.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="teamNumbers.css">
</head>
<body>

<?php
// User-defined variables
$eventKey = "2024onham";
$specialTeam = "1334";
$onlyShowMatchesWithTeam = true;
$showTeamRankings = true;
$hidePastMatches = true;
$showMatchTime = true;
$timeZone = "EDT";
$showNextMatch = true;

// Parse HTML
include "printData.php";
?>

</body>
</html>