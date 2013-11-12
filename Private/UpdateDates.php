 <?php
	include('..//function-library.php');


	$mysqli = connect_to_mysql();
/*
	exit(1);
	for($year = 2012; $year <= 2020; $year++)
	{
		$meetingdates = "";

		for($month = 1; $month < 13; $month++)
		{
			$dates = get_sunday_meetings_by_month_year($year, $month);
			if($month == 12)
				$meetingdates = $meetingdates . "$dates[0]:" . "$dates[1]";
			else
				$meetingdates = $meetingdates . "$dates[0]:" . "$dates[1]:";
		}
		$mysqli->query("INSERT IGNORE INTO `Meetings` (Year, Dates) VALUES ($year, '$meetingdates')");
		echo "Added meetings for the year $year<br>\n";
	}
*/
/*
	$result = $mysqli->query("SELECT * FROM `members` ORDER BY `Name`");

	while($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$person = $row["Name"];
		echo $row["LastPromotionDate"] . "<br>";
		if($row["LastPromotionDate"] === "0000-00-00")
		{
			$status = $mysqli->query("UPDATE `members` SET `LastPromotionDate`='" . $row["JoinedDate"] . "' WHERE `Name` = '$person'");
			if($status) echo "true<br>";
			else echo "false<br>";
		}
	}
*/
/*
	$result = $mysqli->query("SELECT * FROM `members`");
	$result2 = $mysqli->query("SELECT * FROM `ranks` ORDER BY `RankID`");
	while($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$person = $row["Name"];

		$newstring = "";

		for($i = 1; $i <= $row["Rank"]; $i++)
		{
			if($i === 1)
				$newstring = $newstring . $i . ":" . $row["JoinedDate"];
			else
				$newstring = $newstring . "&" . $i . ":" . $row["JoinedDate"];
		}
		$mysqli->query("UPDATE `members` SET `PromotionHistory`='$newstring' WHERE `Name` = '$person'");
	}
*/

/*
	$result = $mysqli->query("SELECT * FROM `members`");
	while($row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$person = $row["Name"];
		$mysqli->query("UPDATE `members` SET `RequestedMaxRank`=8 WHERE `Name` = '$person'");
	}
*/
?>
