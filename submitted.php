<?php
	include('function-library.php');

	$mysqli = connect_to_mysql();

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
				if(strcmp($date, $_POST['thedate']) == 0)
				{
					$found = 1;		
					break;
				}
			}

			if($found == 0)
			{
				array_push($pastdates , $_POST['thedate']);
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

	$mysqli->close();
	header("Location: display.php?l=1&h=8");
?>
