<?php
	include('function-library.php');

	function display_attendance_for_group($mysqli, $possibledates, $group)
	{
		echo "<table border=1>\n";
		echo "<tr><th>Name</th><th>Rank</th>";
		foreach($possibledates as $date)
		{
			echo "<th>$date</th>";
		}
		echo "</tr>";

		$result = $mysqli->query("SELECT * FROM `members` WHERE `Rank`=$group ORDER BY `Name`");

		$rowcolor = true;
		while($row = $result->fetch_array(MYSQLI_ASSOC))
		{
			if($rowcolor)
				echo '<tr style="background-color: #c0c0c0;">';
			else
				echo '<tr>';

			$rowcolor = !$rowcolor;

			echo '<td><a href="display.php?person=' . $row["Name"] . '">' . $row["Name"] . '</a></td>';
			echo '<td>' . Get_Rank_Name($mysqli, $row["Rank"]) . '</td>';

			foreach($possibledates as $date)
			{
				if(strpos($row["Attendance"],$date) === false)
					echo "<td></td>";
				else
					echo "<td>$date</td>";
			}
			echo "</tr>\n";
		}

		echo "</table>";
	}

	function display_attendance_for_one_person($mysqli, $person)
	{
		$possibledates = get_past_meeting_dates($mysqli, intval(date("Y")));
		$lastyear = get_past_meeting_dates($mysqli, intval(date("Y"))-1);


		$result = $mysqli->query("SELECT * FROM `members` WHERE `Name`='$person'");
		$row = $result->fetch_array(MYSQLI_ASSOC);

		echo "<form name='input' action='edit.php' method='post'>\n";
		echo "<input type='hidden' name='person' value='$person'>";

		echo '<table border=1>';
		echo '<tr style="background-color: #c0c0c0;"><td>Member Name</td><td>' . $row['Name'] . '</td></tr>';
		echo '<tr><td>Rank</td><td>' . Get_Rank_Name($mysqli, $row['Rank']) . '</td></tr>';
		echo '<tr style="background-color: #c0c0c0;"><td>Join Date</td><td>' . $row['JoinedDate'] . '</td></tr>';
		echo '<tr><td>Highest Possible Rank</td><td>' . Get_Rank_Name($mysqli, $row['RequestedMaxRank']) . '</td></tr>';
		echo '</table><br>';

		$history = Get_Promotion_History($mysqli, $row["PromotionHistory"], $row["Rank"]);
		echo "<table border=1>\n";
		echo "<tr>\n";
		for($i = 0; $i < count($history); $i+=2)
		{
			echo "<td>" . Get_Rank_Name($mysqli, $history[$i]) . "</td>\n";
			if($i <= 14)
				echo "<td> ></td>";
		}
		echo "</tr>\n";
		echo "<tr>\n";
		for($i = 0; $i < count($history); $i+=2)
		{
			if($history[$i+1] !== "")
			{
				echo "<td>" . $history[$i+1] . "</td>\n";
				echo "<td>" . Get_Next_Possible_Promo_Date($mysqli, $history[$i+1], $history[$i]) . "</td>\n";
			}
			else
			{
				echo "<td>No History Available</td><td>No History Available</td>\n";
			}
		}
		echo "</tr>\n";
		echo "</table><br>\n";

		echo '<div>';

		echo '<table style="float: left;" border=1>';
		$rowcolor = false;
		foreach($lastyear as $date)
		{
			if($rowcolor)
				echo '<tr style="background-color: #c0c0c0;">';
			else
				echo '<tr>';
			$rowcolor = !$rowcolor;


			echo "<td>$date</td><td>";
			if(strpos($row["Attendance"],$date) === false)
				echo "Absent";
			else
				echo "Present";
			echo "</td></tr>";
		}
		echo "</table>";


		echo '<table style="float: left; margin-left: 50;" border=1>';
		$rowcolor = false;
		foreach($possibledates as $date)
		{
			if($rowcolor)
				echo '<tr style="background-color: #c0c0c0;">';
			else
				echo '<tr>';
			$rowcolor = !$rowcolor;


			echo "<td>$date</td><td>";
			if(strpos($row["Attendance"],$date) === false)
				echo "Absent";
			else
				echo "Present";
			echo "</td></tr>";
		}
		echo "</table>";

		echo '</div>';

		echo '<div style="clear:left;">';
		echo "<input type='submit' value='edit'>\n";
		echo "</form><br>";		
		echo "<a href='index.php'>Return to Main Menu</a>";
		echo '</div>';

	}

	$mysqli = connect_to_mysql();

	$possibledates = get_past_meeting_dates($mysqli, intval(date("Y")));

	if(isset($_GET['person']))
	{
		display_attendance_for_one_person($mysqli, $_GET['person']);
	}
	elseif(isset($_GET['l'])&&isset($_GET['h']))
	{
		for($i = $_GET['l']; $i <= $_GET['h']; $i++)
		{
			display_attendance_for_group($mysqli, $possibledates, $i);
			echo "<br><br>";
		}
	}
	else
	{
		for($i = 1; $i < 9; $i++)
		{

			display_attendance_for_group($mysqli, $possibledates, $i);
			echo "<br><br>";
		}
	}


	$mysqli->close();

?>
