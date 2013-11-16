<?php
	include('function-library.php');

	function input_meeting_attendance($mysqli, $lower, $upper, $date)
	{
		$result = $mysqli->query("SELECT * FROM `members` WHERE `Rank` >= $lower AND `Rank` <= $upper ORDER BY `Name`");

		echo "<form name='input' action='submitted.php' method='post'>";

		echo "<input type='hidden' name='thedate' value='$date'>";

		echo "<table border=1>\n";
		echo "<tr><th>Name</th><th>Rank</th><th>Attendance for $date</th></tr>";

		$rowcolor = true;
		while ($row = $result->fetch_array(MYSQLI_ASSOC))
		{
			if($rowcolor)
				echo '<tr style="background-color: #c0c0c0;">';
			else
				echo '<tr>';
			$rowcolor = !$rowcolor;
	
			echo "<td>" . $row["Name"] . "</td>";
			$query = "SELECT `RankName` FROM `ranks` WHERE `RankID`=" . $row['Rank'];
			if($result2 = $mysqli->query($query))
			{
				echo "<td>";
				$result3 = $result2->fetch_row();
				echo $result3[0];
				echo "</td>";
			}
			else
				echo "<td> </td>";

			echo "<td>";
			echo "<input type='radio' name='" . $row["Name"] . "' value='1'>Present";
			echo "<input type='radio' name='" . $row["Name"] . "' value='2' checked='checked'>Absent";
			echo "</td>";

			echo "</tr>";

		}
		echo "</table>";
		echo "<input type='submit' value='Submit'>";
		echo "</form>";	
		echo "<a href='index.php'>Return to Main Menu</a>";
	}

	function process_attendance($mysqli, $process_date)
	{
		foreach ($_POST as $person => $attendance )
		{
			$found = 0;
			$newstring = "";

			if(strcmp($attendance,"1") == 0)
			{
				$result = $mysqli->query("SELECT `Attendance` FROM `members` WHERE `Name` = '$person'");

				$result2 = $result->fetch_row();
				$pastdates = explode(':', $result2[0]);
				foreach($pastdates as $date)
				{
					if(strcmp($date, $process_date) == 0)
					{
						$found = 1;		
						break;
					}
				}

				if($found == 0)
				{
					array_push($pastdates , $process_date);
					sort($pastdates, strcmp);

					foreach($pastdates as $entry)
					{
						if($entry === "")
							continue;
						$newstring = $newstring . "$entry:";
					}
				
					$mysqli->query("UPDATE `members` SET `Attendance`='$newstring' WHERE `Name` = '$person'");
				}
			}
		}
	}
	
	function main_attendance($mysqli)
	{
		$possibledates = get_past_meeting_dates($mysqli, intval(date("Y")));

		echo "Select a date to enter attendance<br>\n";

		echo "<form name='input' action='attendance.php' method='post'>\n";

		echo "<select name='group'>\n";
		echo "<option value='NCO'>Non-Coms</option>\n";
		echo "<option value='O'>Officers</option>\n";
		echo "</select>\n";

		echo "<br>\n";

		echo "<select name='date'>\n";
		foreach($possibledates as $input)
		{
			echo "<option value='$input'>$input</option>\n";
		}
		echo "</select><br>\n";
		echo "<input type='submit' value='submit'>\n";
		echo "</form>";	
		echo "<a href='index.php'>Return to Main Menu</a>";
	}

$mysqli = connect_to_mysql();

if(isset($_POST['group']) && ($_POST['group'] === "NCO"))
	input_meeting_attendance($mysqli, 0, 3, $_POST['date']);

elseif(isset($_POST['group']) && ($_POST['group'] === "O"))
	input_meeting_attendance($mysqli, 4, 8, $_POST['date']);

elseif(isset($_POST['name']) && ($_POST['name'] === "O"))
{
	process_attendance($mysqli, $_POST['date']);
	header("Location: display.php?l=1&h=8");
}

else
	main_attendance($mysqli);


$mysqli->close();

?>
