<?php
	include('function-library.php');


	function add_custom_date_to_year($mysqli, $year, $date)
	{
		$result = $mysqli->query("SELECT * FROM `meetings` WHERE `Year` = '$year'");
		$row = $result->fetch_array(MYSQLI_ASSOC);

		$meetingdates = explode(":", $row['Dates']);

		array_push($meetingdates, $date);

		sort($meetingdates);

		$updatedmeetings = array_unique($meetingdates);

		$final_meeting_string = "";
		foreach($updatedmeetings as $existingdate)
		{
			if($final_meeting_string === "")
				$final_meeting_string = $existingdate;
			else
				$final_meeting_string = $final_meeting_string . ":$existingdate";
		}
		$mysqli->query("UPDATE `meetings` SET `Dates`='$final_meeting_string' WHERE `Year` = '$year'");
	}

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

		$mysqli->query("UPDATE `meetings` SET `Dates`='$final_meeting_string' WHERE `Year` = '$year'");

	}

	function display_meetings_in_year($mysqli, $year)
	{
		$result = $mysqli->query("SELECT * FROM `meetings` WHERE `Year` = '$year'");
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$meetingdates = explode(":", $row['Dates']);

		echo "<form name='input' action='dates.php' method='post'>\n";
		echo "<input type='hidden' name='action_category' value='process_results'>";
		echo "<input type='hidden' name='year' value='$year'>";
		
		echo "<table border=1>\n";
		$rowcolor = true;
		foreach($meetingdates as $date)
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

		echo "<br><br>";

		echo "Add a custom meeting date<br>";
		echo "<form name='input' action='dates.php' method='post'>\n";
		echo "<input type='hidden' name='action_category' value='add_custom_date'>";
		echo "<input type='hidden' name='year' value='$year'>";
		echo "<input type='text' value='YYYY-MM-DD' name='new_meeting_date'>";
		echo "<input type='submit' value='submit'>\n";
		echo "</form>";

		echo "<br><br>";

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
	elseif(isset($_POST['action_category']) && $_POST['action_category'] === "add_custom_date")
	{
		if(strtotime($_POST['new_meeting_date']) === false)
			echo "<font color='red'>date " . $_POST['new_meeting_date'] . " is not a real date!</font><br>";
		else
			add_custom_date_to_year($mysqli, $_POST['year'], $_POST['new_meeting_date']);
		display_meetings_in_year($mysqli, $_POST['year']);
	}

	elseif(isset($_POST['action_category']) && $_POST['action_category'] === "edit_year")
	{
		display_meetings_in_year($mysqli, $_POST['year']);	
	}

	else
	{
		check_for_years_in_meeting_table($mysqli);
		display_years_for_editing($mysqli);
	}
	$mysqli->close();

?>
