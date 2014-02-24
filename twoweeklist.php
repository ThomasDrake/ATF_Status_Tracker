<?php
	include('function-library.php');

	/**
	 * 
	 */
	function Get_Two_Week_Array_By_Month($DateList)
	{
		$month_array = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

		$date_list = explode(":", $DateList);
		foreach($date_list as $date)
		{
			$year = strftime("%Y", strtotime($date));
			if($year === strftime("%Y"))
			{
				$month = intval(strftime("%m", strtotime($date)));

				$month_array[$month-1] = $month_array[$month-1] + 1;

			}
		}
		return $month_array;
	}

	function Get_Two_Week_String($DateList)
	{
		$date_string = "";

		$date_list = explode(":", $DateList);
		foreach($date_list as $date)
		{
			$year = strftime("%Y", strtotime($date));
			if($year === strftime("%Y"))
			{
				if($date_string === "")
					$date_string = $date;
				else
					$date_string = $date_string . ", $date";
			}
		}
		return $date_string;
	}

	/**
     *
     */
	function Update_Members_Two_Week_History($mysqli, $person, $input)
	{
		$string = "";

		foreach($input as $index => $value)
		{			
			if(strtotime($index) !== false && is_numeric($value))
			{
				if($value === "2")
				{
					if($string === "")
						$string = $index;
					else
						$string = $string . ":$index";
				}
			}
		}

		$mysqli->query("UPDATE `members` SET `TwoWeekList`='$string' WHERE `Name`='$person'");

		$result = $mysqli->query("SELECT * FROM `members` WHERE `Name` = '$person'");
		if($result !== false && $result->num_rows !== 0)
		{
			$row = $result->fetch_array(MYSQLI_ASSOC);
			if($row["Rank"] >= 3)
			{
				$newrank = $row["Rank"];
				$mysqli->query("UPDATE `members` SET `Rank`='$newrank' WHERE `Name` = '$person'");
			}
		}
	}

	/**
	 * 
	 */
	function Add_To_Members_Two_Week_History($mysqli, $person, $date)
	{
		$result = $mysqli->query("SELECT * FROM `members` WHERE `Name` = '$person'");
		if($result !== false && $result->num_rows !== 0)
		{
			$row = $result->fetch_array(MYSQLI_ASSOC);

			if(strtotime($date))
			{
				$reformatted_date = strftime("%Y-%m-%d", strtotime($date));

				if(strpos($row["TwoWeekList"], $reformatted_date) === false)
				{
					$list = explode(":", $row["TwoWeekList"]);

					array_push($list, $reformatted_date);

					sort($list);

					$string = "";
					foreach($list as $i)
					{
						if($string === "")
							$string = $i;
						else
							$string = $string . ":" . $i;
					}

					$mysqli->query("UPDATE `members` SET `TwoWeekList`='$string' WHERE `Name`='$person'");

					$rank = $row["Rank"];

					if($rank >= 3)
					{
						$newrank = intval($rank) - 1;

						$mysqli->query("UPDATE `members` SET `Rank`='$newrank' WHERE `Name`='$person'");

						$history = explode("&", $row["PromotionHistory"]);

						$rank = $row["Rank"];

						$updatedhistory = "";
						foreach($history as $entry)
						{
							$array = explode(":", $entry);
							if(intval($array[0]) === intval($newrank))
							{
								if($updatedhistory !== "")
									$updatedhistory = $updatedhistory . "&";
	
								$updatedhistory = $updatedhistory . $array[0] . ":" . date("Y-m-d");
			
							}	
							elseif($array[0] < $rank)
							{
								if($updatedhistory !== "")
									$updatedhistory = $updatedhistory . "&";

								$updatedhistory = $updatedhistory . $array[0] . ":" . $array[1];
							}							
						}
						$mysqli->query("UPDATE `members` SET `PromotionHistory`='$updatedhistory' WHERE `Name`='$person'");
					}
				}
			}
		}
	}

	/**
	 * 
	 */
	function Display_Members_Two_Week_Violations($mysqli, $person)
	{
		$result = $mysqli->query("SELECT * FROM `members` WHERE `Name` = '$person'");
		$row = $result->fetch_array(MYSQLI_ASSOC);
		
		if($row["TwoWeekList"] === "")
			return false;

		$week_array = explode(":", $row["TwoWeekList"]);
	
		echo "<form name='input' action='twoweeklist.php' method='post'>\n";
		echo "<input type='hidden' name='person' value='$person'>\n";
		echo "<input type='hidden' name='action' value='update-violations'>\n";

		echo "<table border=1>\n";
	
		foreach($week_array as $date)
		{
			if($date === "")
				continue;

			echo "<tr>";
			echo "<td>$date</td>\n";


			echo "<td>\n";
			echo "<input type='radio' name='$date' value='2' checked='checked'>Keep\n";
			echo "<input type='radio' name='$date' value='1'>Remove\n";
			echo "</td>\n";

			echo "</tr>\n";
			
		}

		echo "</table>\n";
		echo "<input type='submit' value='submit'>\n";
		echo "</form><br>\n";
		echo "<a href='index.php'>Return to Main Menu</a>";
		return true;
	}

	/**
	 * 
	 */
	function Display_Members_Two_Week_History($mysqli, $person)
	{
		$result = $mysqli->query("SELECT * FROM `members` WHERE `Name` = '$person'");
		$row = $result->fetch_array(MYSQLI_ASSOC);
		echo "<form name='input' action='twoweeklist.php' method='post'>\n";
		echo "<input type='hidden' name='person' value='$person'>\n";

		echo "<table border=1>\n";
		echo "<tr>";
		echo "<th>Name</th><th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>May</th><th>Jun</th><th>Jul</th><th>Aug</th><th>Sep</th><th>Oct</th><th>Nov</th><th>Dec</th><th>Total</th><th>Dates</th>\n";
		echo "</tr>";

		echo "<tr>\n";
		echo "<td><a href='twoweeklist.php?person=$person'>$person</a></td>\n";

		$month_array = Get_Two_Week_Array_By_Month($row["TwoWeekList"]);

		$total = 0;
		foreach($month_array as $val)
		{
			if(intval($val) === 0)
				echo '<td style="background-color: #00FF00;">';
			else
				echo '<td style="background-color: #FFFF00;">';
			echo "$val</td>";
			$total = $total + intval($val);
		}
		echo "<td>$total</td>";
		echo "<td>" . Get_Two_Week_String($row["TwoWeekList"]) . "</td>";
		echo "</tr>\n";
		echo "</table>\n";

		echo "<table border=0>\n";
		echo "<tr>\n";
		echo "<td>Input Date of Two Week Violation</td>\n";
		echo "<td><input type='date' name='new_twoweek_violation' value='YYYY-MM-DD'></td>\n";
		echo "<tr>\n";

		echo "<tr>\n";
		echo "<td></td>\n";
		echo "<td><input type='submit' name='submit' value='submit'></td>\n";
		echo "</tr>\n";
		echo "</table>\n";

		if($row["TwoWeekList"] !== "")
			echo "<input type='submit' name='submit' value='edit'>\n";
		echo "</form>\n";
		echo "<a href='index.php'>Return to Main Menu</a>";
	}

	/**
	 * 
	 */
	function Display_Two_Week_List($mysqli)
	{
		$result = $mysqli->query("SELECT * FROM `members` ORDER BY `Rank`, `Name`");
	
		echo "<table border=1>\n";
		echo "<tr>";
		echo "<th>Name</th><th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>May</th><th>Jun</th><th>Jul</th><th>Aug</th><th>Sep</th><th>Oct</th><th>Nov</th><th>Dec</th><th>Total</th><th>Dates</th>\n";
		echo "</tr>";

		while($row = $result->fetch_array(MYSQLI_ASSOC))
		{
			echo "<tr>\n";
			echo "<td><a href='twoweeklist.php?person=" . $row["Name"] . "'>" . $row["Name"] . "</a></td>\n";

			$month_array = Get_Two_Week_Array_By_Month($row["TwoWeekList"]);

			$total = 0;
			foreach($month_array as $val)
			{
				if(intval($val) === 0)
					echo '<td style="background-color: #00FF00;">';
				else
					echo '<td style="background-color: #FFFF00;">';
				echo "$val</td>";

				$total = $total + intval($val);

			}
			echo "<td>$total</td>";
	
			echo "<td><p>" . Get_Two_Week_String($row["TwoWeekList"]) . "</p></td>\n";
			echo "</tr>\n";
		}
		echo "</table>\n";
		echo "<br>\n";
		echo "<a href='index.php'>Return to Main Menu</a>\n";
	}

	/**
	 * 
	 */
	$mysqli = connect_to_mysql();
	if(isset($_GET['person']))
	{
		Display_Members_Two_Week_History($mysqli, $_GET["person"]);	
	}

	elseif(isset($_POST["submit"]) && ($_POST["submit"] === "edit"))
	{
		if(!Display_Members_Two_Week_Violations($mysqli, $_POST["person"]))
			Display_Members_Two_Week_History($mysqli, $_POST["person"]);
	}

	elseif(isset($_POST["new_twoweek_violation"]))
	{
		Add_To_Members_Two_Week_History($mysqli, $_POST["person"], $_POST["new_twoweek_violation"]);
		Display_Members_Two_Week_History($mysqli, $_POST["person"]);
//		header("Location: twoweeklist.php?person=" . $_POST["person"]);
	}
	elseif(isset($_POST["action"]) && $_POST["action"] == "update-violations")
	{
		Update_Members_Two_Week_History($mysqli, $_POST["person"], $_POST);
		header("Location: twoweeklist.php?person=" . $_POST["person"]);
	}

	else
	{
		Display_Two_Week_List($mysqli);
	}
	$mysqli->close();
?>