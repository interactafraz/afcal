<?php

include("inc/globals.php");

$ReminderID = strtolower($_POST['ReminderID']);
$ReminderTitle = $_POST['ReminderTitle'];
$ReminderInterval = $_POST['ReminderInterval'];
$ReminderGroup = $_POST['ReminderGroup'];

$IDexists = false;
$IntervalValid = false;

if (empty($ReminderID) || empty($ReminderTitle) || empty($ReminderInterval) ) {
	$headercontent = "location:edit.php?status=InputEmpty";
	header($headercontent);
	die();
}
else{
	if ( preg_match("/^[A-Za-z0-9-]+$/",$ReminderID) ){ //If ID only contains letters/numbers/hyphens
	
		if( preg_match("/^Monthly\d\d$/m",$ReminderInterval) || preg_match("/^\d\d-\d\d$/m",$ReminderInterval) ){ //If intervall matches required patterns
				
			if(strpos($ReminderInterval, 'Monthly') !== false){ //Check if monthly
				$parts = explode("Monthly", $ReminderInterval);
				// $indicator =  $parts[0]; // "Monthly"
				$numberDay = $parts[1] * 1; //Avoid numbers with leading zeros

				if($numberDay > 0 && $numberDay < 32){	//Check if valid day number
					$IntervalValid = true;
				}
			}
			elseif(strpos($ReminderInterval, '-') !== false){ //Check if date
			
				$parts = explode("-", $ReminderInterval);
				$numberMonth = $parts[0] * 1; //Avoid numbers with leading zeros
				$numberDay = $parts[1] * 1; //Avoid numbers with leading zeros

				if($numberMonth > 0 && $numberMonth < 13){	//Check if valid month number
					$IntervalValid = true;
				}
				if($numberDay > 0 && $numberDay < 32){	//Check if valid day number
					$IntervalValid = true;
				}
			
			}
	
		}
		
	}
	else {
		$headercontent = "location:edit.php?status=InputWrongIDFormat";
		header($headercontent);
		die();
	}
}


if ($IntervalValid == true){
	$entry = [$ReminderID,$ReminderTitle,$ReminderInterval,$ReminderGroup];

	$json = file_get_contents($reminderFile);
	$reminders = json_decode($json);
	
	foreach($reminders as &$reminder) {
		if($reminder[0] == $ReminderID) {
			$IDexists = true;
			break; //if there will be only one then break out of loop
		}
	}
	
	if($IDexists == false){ //ID does not already exist
		array_push($reminders, $entry);

		$fp = fopen($reminderFile, 'w');
		fwrite($fp, json_encode($reminders));
		fclose($fp);
		
		$headercontent = "location:edit.php?status=success";
		header($headercontent);
		die();
	}
	else{ //ID already in use
		$headercontent = "location:edit.php?status=IDexists";
		header($headercontent);
		die();
	}
}
else {
	$headercontent = "location:edit.php?status=InputWrongFormat";
	header($headercontent);
	die();
}

?>