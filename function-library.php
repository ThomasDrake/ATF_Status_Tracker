<?php

define("DEBUG", false);

	function debug_message($message)
	{
		if(DEBUG === true)
			echo $message;
	}

	/**
	 * @brief Connect to the MySql server.
	 *
	 * @retval Returns an object which represents this connection to a MySQL server.
	 */
	function connect_to_mysql()
	{
		$mysqli = new mysqli("localhost", "root", "", "ATF");

		if (mysqli_connect_errno()) 
		{
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}
		return $mysqli;
	}

	function Get_Promotion_History($mysqli, $history)
	{
		$finalset = array();

		$history_list = explode("&", $history);

		foreach($history_list as $set)
		{
			$array = explode(":", $set);
			array_push($finalset, $array[0], $array[1]);
		}
		return $finalset;
	}

	function Get_Next_Possible_Promo_Date($mysqli, $lastpromodate, $currentrank)
	{
		if($currentrank == 8)
			return "Highest Rank Achieved";

		if($currentrank > 6) 
			return "No Set Time";

		debug_message("$lastpromodate<br>");
		/* Split the join date into its year, month and day */
		$year = intval(strftime("%Y", strtotime($lastpromodate)));
		$month = intval(strftime("%m", strtotime($lastpromodate)));
		$day =  intval(strftime("%d", strtotime($lastpromodate)));

		debug_message("$year - $month - $day<br>");

		$result = $mysqli->query("SELECT `TimeToPromotion` FROM `ranks` WHERE `RankID`= $currentrank");
		$total = 0;
		$result2 = $result->fetch_row();
		$total = $result2[0];

		debug_message("starting total: $total<br>");

		/* add the number of months to the month */
		$month = $month + $total;

		debug_message("next promo month: $month<br>");
		/* handle the case where month has gone past 12. Months in php are 1 based */
		while($month > 12)
		{
			$year = $year + 1;
			$month = $month - 12;
		}

		debug_message("adjusted year-month: $year-$month<br>");

		/* Get the date of the first meeting in the month/year of the next possible
		   promotion date
		*/
		$date_array = get_sunday_meetings_by_month_year($year, $month);
		$calculated_day = intval(strftime("%d", strtotime($date_array[0])));

		debug_message("calculated day of next promo month: $calculated_day<br>");
		debug_message("actual day of last promo: $day<br>");
		/* If the day of the first meeting in the next promo month is after the
		  day they joined, then they have to wait another month.
		*/
		if($calculated_day > $day)
			$month = $month + 1;

		debug_message("updated month: $month<br>");
		/* handle the case that the month has gone past 12.
		*/
		while($month > 12)
		{
			$year = $year + 1;
			$month = $month - 12;
		}

		debug_message("readjusted year-month: $year-$month<br>");

		/* Get the day of the first meeting for the freshly calculated date.
		*/
		$date_array = get_sunday_meetings_by_month_year($year, $month);

		debug_message("final date: $date_array[0]<br><br>");
		return $date_array[0];
	}

	function Get_Last_Promo_Date($history)
	{
		$promo_history_array = explode(":", $history);

		$length = count($promo_history_array);
		if($length > 1)
			$length = $length - 1;

		return $promo_history_array[$length];
	}

	/**
	 * @brief Get the rank string associated a rankid
	 *
	 * @param $mysqli Object which represent the connection to the MySQL server.
	 * @param $rankid An integer associated with a Rank
	 *
	 * @retval Returns the string associated with the rankid
	 */
	function Get_Rank_Name($mysqli, $rankid)
	{
		$result = $mysqli->query("SELECT `RankName` FROM `ranks` WHERE `RankID`= $rankid");
		$result2 = $result->fetch_row();
		return $result2[0];
	}

	function get_sunday_meetings_by_month_year($year, $month)
	{
		$return_array = array();

		$sundays = 0;

		for($day = 1; $day < 23; $day = $day + 1)
		{
			if(strftime("%A", strtotime("$year-$month-$day")) == 'Sunday')
			{
				if($sundays == 0 || $sundays == 2)
					array_push($return_array, strftime("%Y-%m-%d", strtotime("$year-$month-$day")));

				$sundays = $sundays + 1;
			}
		}
		return $return_array;
	}

	function get_all_meetings_in_year($year)
	{
		$all_date_array = array();

		for($i = 1; $i < 12; $i++)	
		{
			$temp = get_sunday_meetings_by_month_year($year, $i);

			array_push($all_date_array, $temp[0], $temp[1]);
		}
		return $all_date_array;
	}

	function get_past_meeting_dates($mysqli, $year)
	{
		$result = $mysqli->query("SELECT * FROM `meetings` WHERE `Year` = '$year'");

		$row = $result->fetch_array(MYSQLI_ASSOC);
		$meetingdates = explode(':', $row['Dates']);

		$possibledates = array();
		
		foreach($meetingdates as $date)
		{
			if($date !== "")
			{
				array_push($possibledates, $date);
			}
		}
		return $possibledates;
	}
?>
