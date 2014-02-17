<?php
	include('function-library.php');

	function validate_name($mysqli, $name)
	{
		$result = $mysqli->query("SELECT * FROM `members` WHERE `Name`='$name'");
		$row = $result->fetch_array(MYSQLI_ASSOC);
		if($row !== NULL && $row['Name'] === $name)
		{
			echo "<font color='red'>Member $name already exists!</font><br>";

			return false;
		}
		return true;
	}

	function validate_joindate($joindate)
	{
		if(strtotime($joindate) === false)
		{
			echo "<font color='red'>date $joindate is not a real date!</font><br>";

			return false;
		}
		return true;
	}

	function validate_rank($mysqli, $rank)
	{
		$result = $mysqli->query("SELECT * FROM `ranks` WHERE `RankID`='$rank'");
		if($result === false)
		{
			echo "<font color='red'>Rank is not valid!</font><br>";

			return false;
		}
		return true;
	}

	function add_new_member($mysqli, $name, $joindate, $rank)
	{
		$reformatted_date = strftime("%Y-%m-%d", strtotime($joindate));
		$result = $mysqli->query("INSERT IGNORE INTO `members` (Name, Rank, JoinedDate) VALUES ('$name', '$rank', '$reformatted_date')");
		if($result === true)
		{
			echo "Sucessfully added $name<br>";
			echo "<a href='index.php'>Return to Main Menu</a>";
		}
		else
		{
			echo "Unknown error adding $name<br>";
			echo "<a href='index.php'>Return to Main Menu</a>";
		}
	}

	function verify_information_before_adding($mysqli, $name, $joindate, $rank)
	{
		$reformatted_date = strftime("%Y-%m-%d", strtotime($joindate));

		echo "<form name='input' action='add.php' method='post'>\n";
		echo "<input type='hidden' name='action_category' value='verified-or-canceled'>";
		echo "<input type='hidden' name='name' value='$name'>";
		echo "<input type='hidden' name='join-date' value='$reformatted_date'>";
		echo "<input type='hidden' name='rank' value='$rank'>";
		echo "You are about to create the following member<br>";
		echo "<b>$name</b><br>";
		echo "<b>$reformatted_date</b><br>";
		echo "<b>" . Get_Rank_Name($mysqli, $rank) . "</b><br>";
		echo "<input type='submit' name='proceed' value='submit'>";
		echo "<input type='submit' name='proceed' value='cancel'>";

		echo "</form>";
	}

	function start_adding_new_member($mysqli, $name, $joindate, $rank)
	{
		echo "<form name='input' action='add.php' method='post'>\n";
		echo "<table border=1>\n";

		echo "<input type='hidden' name='action_category' value='add_verify'>";

		echo "<table border=0>";

		echo "<tr>";
		echo "<td>New Member Name:</td><td><input type='text' value='$name' name='name'></td>";
		echo "</tr><tr>";

		echo "<td>Join Date:</td><td><input type='text' value='$joindate' name='join-date'></td>";

		$result = $mysqli->query("SELECT * FROM `ranks` ORDER BY `RankID`");

		echo "</tr><tr>";
		echo "<td>New Member's Rank</td><td><select name='rank'>";
		while($row = $result->fetch_array(MYSQLI_ASSOC))
		{
			echo "<option value='" . $row["RankID"] . "'";

			if($rank === $row["RankID"])
				echo "selected='selected'";

			echo ">" . $row["RankName"] . "</option>\n";
		}
		echo "</select></td>";

		echo "</tr></table>";
		echo "<input type='submit' value='submit'><br><br>\n";
		echo "</form>";	
		echo "<a href='index.php'>Return to Main Menu</a>";
	}



	$mysqli = connect_to_mysql();


	if(isset($_POST["action_category"]))
	{
		if($_POST["action_category"] === "verified-or-canceled")
		{
			if($_POST["proceed"] === "cancel")
				start_adding_new_member($mysqli, $_POST["name"], $_POST["join-date"], $_POST["rank"]);
			elseif($_POST["proceed"] === "submit")
				add_new_member($mysqli, $_POST["name"], $_POST["join-date"], $_POST["rank"]);
		}
		elseif($_POST["action_category"] === "add_verify")
		{
			$r1 = validate_name($mysqli, $_POST["name"]);
			$r2 = validate_joindate($_POST["join-date"]);
			$r3 = validate_rank($mysqli, $_POST["rank"]);

			if($r1 === true && $r2 === true && $r3 === true)
				verify_information_before_adding($mysqli, $_POST["name"], $_POST["join-date"], $_POST["rank"]);
			else
				start_adding_new_member($mysqli, $_POST["name"], $_POST["join-date"], $_POST["rank"]);
		}
	}
	else
	{
		$name = "";
		$join = "";
		$rank = "";

		if(isset($_POST["name"]))
			$name = $_POST["name"];

		if(isset($_POST["join-date"]))
			$join = $_POST["join-date"];

		if(isset($_POST["rank"]))
			$date = $_POST["rank"];

		start_adding_new_member($mysqli, $name, $join, $rank);
	}

	$mysqli->close();
?>
