<?php
	include('function-library.php');

	function update_single_person_attendance($mysqli, $person, $input)
	{

		$result = $mysqli->query("SELECT * FROM `members` WHERE `Name` = '$person'");
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$result2 = $row["Attendance"];

		if(strtotime($input["JoinDate"]) !== false)
			$mysqli->query("UPDATE `members` SET `JoinDate`='" . $input["JoinDate"] . "' WHERE `Name` = '$person'");

		if(strtotime($input["LastPromoDate"]) !== false)
			$mysqli->query("UPDATE `members` SET `LastPromotionDate`='" . $input["LastPromoDate"] . "' WHERE `Name` = '$person'");

		$mysqli->query("UPDATE `members` SET `Rank`='" . $input["rank"] . "' WHERE `Name` = '$person'");

		foreach ($input as $date => $attended )
		{
			if($attended === "2" && strpos($result2, $date) !== false)
			{
				$result2 = str_replace($date . ":", "", $result2);
			}
			elseif($attended === "1" && strpos($result2, $date) === false)
				$result2 = $result2 . "$date:";
		}

		$pastdates = explode(':', $result2);
		uksort($pastdates, strcmp);

		$result = "";
		foreach($pastdates as $date)
		{
			if($date !== "")
				$result = $result . "$date:";
		}
		$mysqli->query("UPDATE `members` SET `Attendance`='$result' WHERE `Name` = '$person'");
		return;
	}

	function display_editable_single_person_information($mysqli, $person, $year)
	{
		$possibledates = get_past_meeting_dates($mysqli, $year);

		$result = $mysqli->query("SELECT * FROM `members` WHERE `Name`='$person'");
		$row = $result->fetch_array(MYSQLI_ASSOC);

		echo "<form name='input' action='edit.php' method='post'>\n";

		echo "<input type='hidden' name='updated' value='$person'>";

		echo '<table border=1>';
		echo '<tr style="background-color: #c0c0c0;"><td>Member Name</td><td>' . $row['Name'] . '</td></tr>';
		echo '<tr><td>Rank</td><td>';

		$result2 = $mysqli->query("SELECT * FROM `ranks` ORDER BY `RankID`");
		echo "<select name='rank'>\n";
		while($row2 = $result2->fetch_array(MYSQLI_ASSOC))
		{
			echo "<option value='" . $row2["RankID"] . "' ";
			if($row2["RankID"] === $row["Rank"])
				echo "selected='selected'";
			echo ">" . $row2["RankName"] . "</option>\n";
		}
		echo "</select>";

		echo '</td></tr>';

		echo '<tr style="background-color: #c0c0c0;"><td>Join Date</td>';
		echo '<td><input type="date" name="JoinDate" value="' . $row['JoinedDate'] . '"></td></tr>';

		echo '<tr><td>Last Promotion Date</td><td><input type="date" name="LastPromoDate" value="' . $row['LastPromotionDate'] . '"></td></tr>';

		echo '<tr style="background-color: #c0c0c0;"><td>Next Promotion Date</td>';
		echo '<td colspan>' . Get_Next_Possible_Promo_Date($mysqli, $row['LastPromotionDate'], $row['Rank']) . '</td></tr>';


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
			{
				echo "<select name='$date'>
					  <option value='1'>Present</option>
					  <option value='2' selected='selected'>Absent</option>
					  </select>";
			}
			else
			{
				echo "<select name='$date'>
					  <option value='1' selected='selected'>Present</option>
					  <option value='2'>Absent</option>
					  </select>";
			}
			echo "</td></tr>";
		}
		echo "</table>";
		echo "<input type='submit' value='submit'>\n";
		echo "</form>";		
		echo "<a href='index.php'>Return to Main Menu</a>";
	}

	
	function display_editable_group($mysqli, $group)
	{
		$result = $mysqli->query("SELECT * FROM `members` WHERE `Rank`=$group ORDER BY `Name`");

		echo "<form name='input' action='edit.php' method='post'>\n";
		echo "<table border=1>\n";
		echo "<tr><th>Name</th><th></th></tr>\n";
	
		$rowcolor = true;
		while($row = $result->fetch_array(MYSQLI_ASSOC))
		{
			if($rowcolor)
				echo '<tr style="background-color: #c0c0c0;">';
			else
				echo '<tr>';
			$rowcolor = !$rowcolor;

			echo "<td>" . $row["Name"] . "</td>";

			echo "<td><input type='radio' name='person' value='" . $row["Name"] . "'></td>";
			echo "</tr>\n";
		}

		echo "</table>";
		echo "<input type='submit' value='submit'>\n";
		echo "</form>";	
		echo "<a href='index.php'>Return to Main Menu</a>";
	}

	function display_editable_ranks($mysqli)
	{
		$result = $mysqli->query("SELECT * FROM `ranks` ORDER BY `RankID`");

		echo "<form name='input' action='edit.php' method='post'>\n";
		echo "<table border=1>\n";

		echo "<select name='rank'>";
		while($row = $result->fetch_array(MYSQLI_ASSOC))
			echo "<option value='" . $row["RankID"] . "'>" . $row["RankName"] . "</option>\n";
		echo "</select>";

		echo "</table>";
		echo "<input type='submit' value='submit'>\n";
		echo "</form>";	
		echo "<a href='index.php'>Return to Main Menu</a>";
	}
	
	$mysqli = connect_to_mysql();
	if(isset($_GET['person']))
		display_editable_single_person_information($mysqli, $_GET["person"], intval(date("Y")));

	elseif(isset($_POST["updated"]))
	{
		update_single_person_attendance($mysqli, $_POST["updated"], $_POST);
		header("Location: display.php?&person=" . $_POST['updated']);
	}

	elseif(isset($_POST["person"]))
		display_editable_single_person_information($mysqli, $_POST["person"], intval(date("Y")));

	elseif(isset($_POST["rank"]))
		display_editable_group($mysqli, $_POST["rank"]);

	else
		display_editable_ranks($mysqli);
		
	$mysqli->close();
?>

