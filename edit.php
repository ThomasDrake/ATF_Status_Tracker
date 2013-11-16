<?php
	include('function-library.php');

	/**
	 * Using the parameters, update the record for the $person.
	 */
	function update_single_person_attendance($mysqli, $person, $input)
	{
		$result = $mysqli->query("SELECT * FROM `members` WHERE `Name` = '$person'");
		$row = $result->fetch_array(MYSQLI_ASSOC);
		$result2 = $row["Attendance"];

		/**
		 * Update the Rank based on the input
		 */
		$mysqli->query("UPDATE `members` SET `Rank`='" . $input["rank"] . "' WHERE `Name` = '$person'");

		/**
		 * Check the validity of the input JoinedDate before updating the record
		 */
		if(strtotime($input["JoinedDate"]) !== false)
			$mysqli->query("UPDATE `members` SET `JoinedDate`='" . $input["JoinedDate"] . "' WHERE `Name` = '$person'");

		/**
		 * Update the RequestedMaxRank based on the input
		 */
		$mysqli->query("UPDATE `members` SET `RequestedMaxRank`=" . $input["requestedmaxrank"] . " WHERE `Name` = '$person'");

		/**
		 * Go through the inputs checking for inputs whose index is a number and who's values are dates
		 */
		$string = "";
		foreach($input as $rank => $date)
		{
			if(is_numeric($rank) === true && strtotime($date) !== false)
			{
				if( $input["rank"] >= $rank)
				{
					if($string === "")
						$string = $string . $rank . ":" . $date;
					else
						$string = $string . "&" . $rank . ":" . $date;
				}
			}
		}
		$mysqli->query("UPDATE `members` SET `PromotionHistory`='$string' WHERE `Name` = '$person'");

		/**
		 * Go through the inputs checking for inputs whose index is a date and whose value is a numeric
		 *  with a value of either 1 or 2
		 */
		foreach ($input as $date => $attended )
		{
			if(strtotime($date) !== false && is_numeric($attended) === true && ($attended == 1 || $attended == 2))
			{
				/**
				 * If $attended is 2 (absent) and the date associated with it is contained in the attendance string
                 *  then the date entry in the attendance string needs to be removed
				 */
				if($attended === "2" && strpos($result2, $date) !== false)
					$result2 = str_replace($date . ":", "", $result2);

				/**
				 * if $attend is '1' (present) and the date associated with it is not contained in the attendance
				 *  string, then it needs to be appended to the list. Sorting it into this correct place will be
				 *  handled in one of the code sections below.
				 */
				elseif($attended === "1" && strpos($result2, $date) === false)
					$result2 = $result2 . "$date:";
			}
		}

		/**
		 * Create an array of the dates and then sort them using sort.
		 */
		$pastdates = explode(':', $result2);
		sort($pastdates, "strcmp");

		/**
		 * Recombine the array of dates back into a single string
		 */
		$result = "";
		foreach($pastdates as $date)
		{
			if($date !== "")
				$result = $result . "$date:";
		}

		/**
		 * Update the Attendance field back into the database
		 */
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
		echo "</select>\n";

		echo "</td></tr>\n";

		echo '<tr style="background-color: #c0c0c0;"><td>Join Date</td>';
		echo '<td><input type="date" name="JoinedDate" value="' . $row['JoinedDate'] . '"></td></tr>';

		echo '<tr><td>Highest Possible Rank</td><td>';

		$result2 = $mysqli->query("SELECT * FROM `ranks` ORDER BY `RankID`");
		echo "<select name='requestedmaxrank'>\n";
		while($row2 = $result2->fetch_array(MYSQLI_ASSOC))
		{
			echo "<option value='" . $row2["RankID"] . "' ";
			if($row2["RankID"] === $row["RequestedMaxRank"])
				echo "selected='selected'";
			echo ">" . $row2["RankName"] . "</option>\n";
		}
		echo "</select>\n";

		echo "</td></tr>\n";

		echo "</table><br>\n\n";

		$history = Get_Promotion_History($mysqli, $row["PromotionHistory"], $row["Rank"]);
		echo "<table border=1>\n";
		echo "<tr>\n";
		for($i = 0; $i < count($history); $i+=2)
		{
			echo "<td>" . Get_Rank_Name($mysqli, $history[$i]) . "</td>\n";
			if($i < 16)
				echo "<td> ></td>";
		}
		echo "</tr>\n";
		echo "<tr>\n";
		for($i = 0; $i < count($history); $i+=2)
		{
			if($history[$i+1] !== "")
			{
				echo "<td><input type='date' name='". $history[$i] ."' value='" . $history[$i+1] . "'></td>\n";
				echo "<td>" . Get_Next_Possible_Promo_Date($mysqli, $history[$i+1], $history[$i]) . "</td>\n";

			}
			else
			{
				echo "<td><input type='date' name='". $history[$i] ."' value=''></td>\n";
				echo "<td>TBD</td>\n";
			}
		}
		echo "</tr>\n";
		echo "</table><br>\n\n";

		echo "<table border=1>\n";
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
		echo "<input type='submit' value='submit'><br>\n";
		echo "<a href='index.php'>Return to Main Menu</a>";
		echo "</form>";		
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

