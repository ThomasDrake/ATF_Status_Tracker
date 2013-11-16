<?php
	include('function-library.php');

	function process_meetings_updates_for_year($mysqli, $year, $array)
	{
		$meeting_date_array = array();
		foreach ($array as $date => $value )
		{
			if($value === '2')
				array_push($meeting_date_array, $date);
		}

		sort($meeting_date_array, strcmp);

		$final_meeting_string = "";
		foreach($meeting_date_array as $date)
			$final_meeting_string = $final_meeting_string . "$date:";

		$mysqli->query("UPDATE `Meetings` SET `Dates`='$final_meeting_string' WHERE `Year` = '$year'");

	}

	function display_meetings_in_year($mysqli, $year)
	{
		$result = $mysqli->query("SELECT * FROM `meetings` WHERE `Year` = '$year'");
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$meetingdates = $row['Dates'];

		$all_date_array = get_all_meetings_in_year($year);

		echo "<form name='input' action='dates.php' method='post'>\n";
		echo "<input type='hidden' name='action_category' value='process_results'>";
		echo "<input type='hidden' name='year' value='$year'>";
		
		echo "<table border=1>\n";
		$rowcolor = true;
		foreach($all_date_array as $date)
		{
			if( $date === "")
				continue;

			if($rowcolor)
				echo '<tr style="background-color: #c0c0c0;">';
			else
				echo '<tr>';
			$rowcolor = !$rowcolor;

			echo "<td>$date</td>";

			if(strpos($meetingdates, $date) !== false)
			{
				echo "<td>
					  <input type='radio' name='$date' value='2' checked='checked'>Keep
					  <input type='radio' name='$date' value='1'>Remove
					  </td>
					 ";
			}
			else
			{
				echo "<td>
					  <input type='radio' name='$date' value='2'>Keep
					  <input type='radio' name='$date' value='1' checked='checked'>Remove
					  </td>
					 ";
			}
			echo "</tr>";
		}
		echo '</table>';
		echo "<input type='submit' value='submit'>\n";
		echo "</form>";		
		echo "<a href='index.php'>Return to Main Menu</a>";
	}


	function display_years_for_editing($mysqli)
	{
		$result = $mysqli->query("SELECT * FROM `meetings` ORDER BY Year");

		echo "Select the year you want to edit<br>";

		echo "<form name='input' action='dates.php' method='post'>\n";
		echo "<input type='hidden' name='action_category' value='edit_year'>";

		echo "<select name='year'>";
		while($row = $result->fetch_array(MYSQLI_ASSOC))
			echo "<option value='" . $row["Year"] . "'>" . $row["Year"] . "</option>\n";
		echo "</select>";

		echo "<input type='submit' value='submit'>\n";
		echo "</form>";
		echo "<a href='index.php'>Return to Main Menu</a>";
	}

	$mysqli = connect_to_mysql();

	if(isset($_POST['action_category']) && $_POST['action_category'] === "process_results")
	{
		process_meetings_updates_for_year($mysqli, $_POST['year'], $_POST);
		header("Location: index.php");
	}	

	elseif(isset($_POST['action_category']) && $_POST['action_category'] === "edit_year")
		display_meetings_in_year($mysqli, $_POST['year']);

	else
	{
		check_for_years_in_meeting_table($mysqli);
		display_years_for_editing($mysqli);
	}
	$mysqli->close();

?>
