<?php

	include('function-library.php');


	function display_members_ready_for_promotions($mysqli)
	{
		echo "<table border=1>";
		echo "<tr><th>Name</th><th>Rank</th><th>Date of Last Promotion</th><th>Next possible promo date</th></tr>";

		$rowcolor = true;

		$result = $mysqli->query("SELECT * FROM `members` ORDER BY `Rank`,`Name`");
		while($row = $result->fetch_array(MYSQLI_ASSOC))
		{
			$promodate = Get_Next_Possible_Promo_Date($mysqli, $row["LastPromotionDate"], $row["Rank"]);

			if(strcmp($promodate, date("Y-m-d")) <= 0 && $row["Rank"] <= 6)
				echo "<tr style='background-color: #44FF00;'>";
			elseif($rowcolor)
				echo "<tr style='background-color: #c0c0c0;'>";
			else
				echo "<tr>";
			
			$rowcolor = !$rowcolor;
			echo "<td><a href='display.php?person=" . $row["Name"] . "'>" . $row["Name"] . "</a></td>";
			echo "<td>" . Get_Rank_Name($mysqli, $row["Rank"]) . "</td>";
			echo "<td>" . $row["LastPromotionDate"] . "</td>\n";
			echo "<td>" . $promodate . "</td></tr>\n";

		}

		echo "</table>";
	}



	$mysqli = connect_to_mysql();
	display_members_ready_for_promotions($mysqli);
	$mysqli->close();
?>
