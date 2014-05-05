<?php

	include('function-library.php');


	function Promote_Member($mysqli, $person)
	{
		$result = $mysqli->query("SELECT * FROM `members` WHERE `Name` = '$person'");
		if($result !== false && $result->num_rows !== 0)
		{
			$row = $result->fetch_array(MYSQLI_ASSOC);

			$newrank = $row["Rank"] + 1;
			$status = $mysqli->query("UPDATE `members` SET `Rank`='$newrank' WHERE `Name` = '$person'");

			$meetings = get_sunday_meetings_by_month_year(date("Y"), date("m"));

			if($row["PromotionHistory"] === "")
				$new_promohistory = $row["Rank"]+1 . ":" . $meetings[0];
			else
				$new_promohistory = $row["PromotionHistory"] . "&" . $row["Rank"]+1 . ":" . $meetings[0];

			$status = $mysqli->query("UPDATE `members` SET `PromotionHistory`='$new_promohistory' WHERE `Name` = '$person'");

		}
		
	}

	function Get_Members_Ready_For_Promotions($mysqli, $rank)
	{
		$header_displayed = false;

		$result = $mysqli->query("SELECT * FROM `members` WHERE `Rank`=$rank ORDER BY `Name`");

		while($row = $result->fetch_array(MYSQLI_ASSOC))
		{
			$lastpromodate = Get_Last_Promo_Date($row["PromotionHistory"]);

			if(strtotime($lastpromodate) === false)
				continue;

			$promodate = Get_Next_Possible_Promo_Date($mysqli, $lastpromodate, $row["Rank"]);

			if(strtotime($promodate) === false)
				continue;

			if(strcmp($promodate, date("Y-m-d")) >= 0)
				continue;

			if($header_displayed === false)
			{
				echo "<table border=1>\n";
				echo "<tr>";

				echo "<th>" . Get_Rank_Name($mysqli, $rank) . " to " . Get_Rank_Name($mysqli, intval($rank)+1) . "</th>";
				echo "<th>Two Week Infractions</th>";
				echo "<th colspan=8>Past Attendance</th>";
				echo "</tr>\n";
				$header_displayed = true;
			}	

			echo "\t<tr>\n";
			echo "\t\t<td>\n";
			echo "\t\t\t" . $row["Name"] . "\n";
			echo "\t\t</td>\n";

			echo "\t\t<td>\n";
			echo "\t\t\t" . $row["TwoWeekList"];
			echo "\t\t</td>\n";

			$pastattendance = Get_Past_Eight_Meeting_Attendance($mysqli, date("Y"), $row["Name"]);
			foreach($pastattendance as $entry)
			{
				echo "\t\t<td>\n";
				echo "\t\t\t<p>$entry</p>\n";
				echo "\t\t</td>\n";
			}

			echo "\t\t<td>\n";
			echo "<form name='input' action='promotions.php' method='post'>";
			echo "<input type='hidden' name='person' value='" . $row['Name'] . "'>";
			echo "<input type='submit' value='promote'>";
			echo "</form>";
			echo "\t\t</td>\n";

			echo "\t</tr>\n";
		}
		if($header_displayed === true)
		{
			echo "</table>\n";
			echo "<br>\n";
		}
	}

	$mysqli = connect_to_mysql();

	if(isset($_POST['person']))
	{
		Promote_Member($mysqli, $_POST['person']);
		header("Location: display.php?&person=" . $_POST['person']);
	}

	for($i = 1; $i < 8; $i++)
		Get_Members_Ready_For_Promotions($mysqli, $i);
		echo "<a href='index.php'>Return to Main Menu</a>";

	$mysqli->close();
?>
