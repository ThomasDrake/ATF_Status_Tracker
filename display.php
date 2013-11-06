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

	function display_attendance_for_one_person($mysqli, $possibledates, $person)
	{
		$possibledates = get_past_meeting_dates($mysqli, intval(date("Y")));

		$result = $mysqli->query("SELECT * FROM `members` WHERE `Name`='$person'");
		$row = $result->fetch_array(MYSQLI_ASSOC);

		echo "<form name='input' action='edit.php' method='post'>\n";
		echo "<input type='hidden' name='person' value='$person'>";

		echo '<table border=1>';
		echo '<tr style="background-color: #c0c0c0;"><td>Member Name</td><td>' . $row['Name'] . '</td></tr>';

		echo '<tr><td>Rank</td><td>' . Get_Rank_Name($mysqli, $row['Rank']) . '</td></tr>';

		echo '<tr style="background-color: #c0c0c0;"><td>Join Date</td><td>' . $row['JoinedDate'] . '</td></tr>';

		echo '<tr><td>Last Promotion Date</td><td>' . $row['LastPromotionDate'] . '</td></tr>';

		echo '<tr style="background-color: #c0c0c0;"><td>Next Promotion Date</td>';
		echo '<td colspan>' . Get_Next_Possible_Promo_Date($mysqli, $row['LastPromotionDate'], $row['Rank']) . '</td></tr>';

		echo '</table><br>';

		echo '<table border=1>';
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
		echo "<input type='submit' value='edit'>\n";
		echo "</form>";		
		echo "<a href='index.php'>Return to Main Menu</a>";

	}

	$mysqli = connect_to_mysql();

	$possibledates = get_past_meeting_dates($mysqli, intval(date("Y")));

	if(isset($_GET['person']))
	{
		display_attendance_for_one_person($mysqli, $possibledates, $_GET['person']);
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
