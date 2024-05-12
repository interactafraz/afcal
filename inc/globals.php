<?php

$languageCode = "de"; //Set language file to "de" or "en"
$reminderFile = "afcal-reminders.json";
$DirTimestamps = "timestamps";
$AttributesDefault = "none"; //Default attribute

if ( !file_exists($reminderFile) ) { //Check if data does not exist yet
	$fp = fopen($reminderFile, 'w+');
	flock($fp, LOCK_EX); //Lock file to avoid other processes writing to it simlutanously 
	fwrite($fp, json_encode([])); //Save empty array to file	
	flock($fp, LOCK_UN); //Unlock file for further access
	fclose($fp);
}

?>