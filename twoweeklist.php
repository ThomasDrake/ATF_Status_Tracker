<?php
	include('function-library.php');

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

				$month_array[$month-1] = $month_array[$month] + 1;
			}
		}
		return $month_array;
	}

	function Update_Members_Two_Week_History($mysqli, $person, $date)
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

					$string = "";
					foreach($list as $i)
					{
						if($string === "")
							$string = $i;
						else
							$string = $string . ":" . $i;
					}

					$result2 = $mysqli->query("UPDATE `members` SET `TwoWeekList`='$string' WHERE `Name`='$person'");
				}
			}
		}
	}

	function Display_Members_Two_Week_History($mysqli, $person)
	{
		$result = $mysqli->query("SELECT * FROM `members` WHERE `Name` = '$person'");
		$row = $result->fetch_array(MYSQLI_ASSOC);
		echo "<form name='input' action='twoweeklist.php' method='post'>\n";
		echo "<input type='hidden' name='person' value='$person'>\n";

		echo "<table border=1>\n";
		echo "<tr>";
		echo "<th>Name</th><th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>May</th><th>Jun</th><th>Jul</th><th>Aug</th><th>Sep</th><th>Oct</th><th>Nov</th><th>Dec</th><th>Total</th>\n";
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
		echo "</tr>\n";
		echo "</table>\n";

		echo "<input type='date' name='new_twoweek_violation' value='YYYY-MM-DD'><br>\n";

		echo "<input type='submit' value='submit'>\n";
		echo "</form>\n";
	}


	function Display_Two_Week_List($mysqli)
	{
		$result = $mysqli->query("SELECT * FROM `members` ORDER BY `Rank`, `Name`");
	
		echo "<table border=1>\n";
		echo "<tr>";
		echo "<th>Name</th><th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>May</th><th>Jun</th><th>Jul</th><th>Aug</th><th>Sep</th><th>Oct</th><th>Nov</th><th>Dec</th><th>Total</th>\n";
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
			echo "</tr>\n";
		}
		echo "</table>\n";
		echo "<br>\n";
		echo "<a href='index.php'>Return to Main Menu</a>\n";
	}


	$mysqli = connect_to_mysql();
	if(isset($_GET['person']))
	{
		Display_Members_Two_Week_History($mysqli, $_GET["person"]);	
	}

	elseif(isset($_POST["new_twoweek_violation"]))
	{
		Update_Members_Two_Week_History($mysqli, $_POST["person"], $_POST["new_twoweek_violation"]);
		Display_Members_Two_Week_History($mysqli, $_POST["person"]);
		header("Location: index.php");
	}
	elseif(isset($_POST["action"]) && $_POST["action"] == "")
	{
		Update_Members_Two_Week_History($mysqli, $_POST[""], $_POST);
	}
	else
	{
		Display_Two_Week_List($mysqli);
	}
	$mysqli->close();
?>