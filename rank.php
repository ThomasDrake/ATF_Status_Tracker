<?php
	include('function-library.php');

	function update_members_rank_history($mysqli, $person, $input)
	{
		$string = "";
		foreach($input as $rank => $date)
		{
			if(!is_numeric($rank))
				continue;

			if($string === "")
				$string = $string . $rank . ":" . $date;
			else
				$string = $string . "&" . $rank . ":" . $date;
		}
		$mysqli->query("UPDATE `members` SET `PromotionHistory`='$string' WHERE `Name` = '$person'");


	}

	function edit_members_rank_history($mysqli, $person)
	{
		$result = $mysqli->query("SELECT * FROM `members` WHERE `Name` = '$person'");
		$row = $result->fetch_array(MYSQLI_ASSOC);

		echo "<form name='input' action='rank.php' method='post'>\n";
		echo "<input type='hidden' name='updated' value='$person'>";
		echo '<table border=1>';

		$history = Get_Promotion_History($mysqli, $row["PromotionHistory"]);

		$i = 0;

		while($i < count($history))
		{
			echo "<tr><td>" . Get_Rank_Name($mysqli, $history[$i]) . "</td>";
			echo "<td><input type='date' name='" . $history[$i] . "' value='" . $history[$i+1] . "'></td></tr>";

			$i = $i + 2;
		}

		echo "</table>";
		echo "<input type='submit' name='submit' value='submit'><br>\n";
		echo "<a href='index.php'>Return to Main Menu</a>";
		echo "</form>";
	}

	function display_members_rank_history($mysqli, $person)
	{
		$result = $mysqli->query("SELECT * FROM `members` WHERE `Name` = '$person'");
		$row = $result->fetch_array(MYSQLI_ASSOC);

		echo "<form name='input' action='rank.php' method='post'>\n";
		echo "<input type='hidden' name='person' value='$person'>";
		echo '<table border=1>';

		$history = Get_Promotion_History($mysqli, $row["PromotionHistory"]);

		$i = 0;

		while($i < count($history))
		{
			echo "<tr><td>" . Get_Rank_Name($mysqli, $history[$i]) . "</td>";
			echo "<td>" . $history[$i+1] . "</td></tr>";

			$i = $i + 2;
		}

		echo "</table>";
		echo "<input type='submit' name='submit' value='edit'><br>\n";
		echo "<a href='index.php'>Return to Main Menu</a>";
		echo "</form>";
	}

	$mysqli = connect_to_mysql();

	if(isset($_GET["person"]))
		display_members_rank_history($mysqli, $_GET["person"]);

	elseif(isset($_POST["submit"]) && $_POST["submit"] === "edit")
		edit_members_rank_history($mysqli, $_POST["person"]);

	elseif(isset($_POST["submit"]) && $_POST["submit"] === "submit")
	{
		update_members_rank_hisotry($mysqli, $_POST["person"], $_POST);
		header("Location: index.php");
	}

//	else
//		header("Location: index.php");

	$mysqli->close();
?>
