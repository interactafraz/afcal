<?php 

include("inc/globals.php");

header('Content-type: application/xml');

$URLmain = "https://". $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']) ."/"; //Add base url here

$DateCurrent = date('D, d M Y H:i:s', time());
$DateReference = "";
$DateNext = "";

$json = file_get_contents($reminderFile);
$reminders = json_decode($json, true);

$output = "";

$ContentItem = array(); //Text + Date
$ContentItemText = "";
$ContentItemsUnsorted = array();
$ContentItemsSorted = array();

if(!is_dir($DirTimestamps)){
    //Directory does not exist, so lets create it.
    mkdir($DirTimestamps, 0755);
}

$output .= "<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'>\n";
$output .= "<channel>\n";

$output .= "<title>AFcal</title>\n";
$output .= "<description>AFcal</description>\n";
$output .= "<atom:link href=\"".$URLmain."\" rel=\"self\" type=\"application/rss+xml\"/>\n";
$output .= "<link>".$URLmain."</link>\n";

for ($row = 0; $row < count($reminders); $row++) {
	$ReminderID = $reminders[$row][0];
	$PathReminderTimestamp = "./" . $DirTimestamps . "/" . $ReminderID . ".txt";
	$PathReminderGuid = "./" . $DirTimestamps . "/" . $ReminderID . "_guid.txt";
	
	$ReminderTitle = $reminders[$row][1];
	$ReminderInterval = $reminders[$row][2];
	
	$ContentItem = array(); //Reset Content Item
	$ContentItemText = ""; //Reset Content Item Text
	$guid = ""; //Reset Guid
	
	if (file_exists($PathReminderTimestamp) && file_exists($PathReminderGuid)) { //Check if Timestamp File exists
		$DateReference = file_get_contents($PathReminderTimestamp); //Get Date from Timestamp File
		$GuidReference = file_get_contents($PathReminderGuid); //Get Guid from Guid File
		
		if(strpos($ReminderInterval, 'Monthly') !== false){ //Check if monthly
			$DateNext = str_replace("Monthly", "", $ReminderInterval); //Get day
			$DateNext = date("Y") . '-' . date("m") . '-' . $DateNext; //Add year and month
		}
		elseif(strpos($ReminderInterval, '-') !== false){ //Check if date
			$DateNext = date("Y") . '-' . $ReminderInterval; //Add year
		}
		
		if ( 
		date('Y-m-d', strtotime($DateCurrent) ) != date('Y-m-d', strtotime($DateReference) ) && date('Y-m-d', strtotime($DateCurrent) ) == date('Y-m-d', strtotime($DateNext) )
		){ //If today is not reference date AND today is scheduled date -> Set new reminder
			
			unlink($PathReminderTimestamp); // Delete Timestamp File
			$TimestampFile = fopen($PathReminderTimestamp, "w"); //Create and open Timestamp File
			fwrite($TimestampFile, $DateCurrent); //Insert Current Date
			fclose($TimestampFile); //Close Timestamp File
			
			unlink($PathReminderGuid); // Delete Guid File
			$guid = rand(); //Generate Random Number
			$GuidFile = fopen($PathReminderGuid, "w"); //Create and open Guid File
			fwrite($GuidFile, $guid); //Insert Guid
			fclose($GuidFile); //Close Guid File
			
			$ContentItemText .= "<item>\n";
			$ContentItemText .= "<title>".$ReminderTitle."</title>\n";
			$ContentItemText .= "<guid isPermaLink=\"false\">".$URLmain."?id=".$guid."</guid>\n";
			$ContentItemText .= "<description>".$ReminderID."</description>\n";
			$ContentItemText .= "<pubDate>".$DateCurrent. date(' O') ."</pubDate>\n";
			$ContentItemText .= "<link>".$URLmain."</link>\n";
			$ContentItemText .= "<atom:link href='".$URLmain."' rel='self' type='application/rss+xml'/>\n";
			$ContentItemText .= "</item>\n";
			
			$ContentItem = array("text" => $ContentItemText, "date"=> $DateCurrent); //Prepare Content Item
		}
		else{ //Use existing reminder
			$ContentItemText .= "<item>\n";
			$ContentItemText .= "<title>".$ReminderTitle."</title>\n";
			$ContentItemText .= "<guid isPermaLink=\"false\">".$URLmain."?id=".$GuidReference."</guid>\n";
			$ContentItemText .= "<description>".$ReminderID."</description>\n";
			$ContentItemText .= "<pubDate>".$DateReference. date(' O') ."</pubDate>\n";
			$ContentItemText .= "<link>".$URLmain."</link>\n";
			$ContentItemText .= "<atom:link href='".$URLmain."' rel='self' type='application/rss+xml'/>\n";
			$ContentItemText .= "</item>\n";
			
			$ContentItem = array("text" => $ContentItemText, "date"=> $DateReference); //Prepare Content Item
		}
		
		array_push($ContentItemsUnsorted, $ContentItem); //Add Content Item to other Content Items
	}
	
	
	
}

function sortFunction( $a, $b ) {
    return strtotime($b["date"]) - strtotime($a["date"]);
}
usort($ContentItemsUnsorted, "sortFunction"); //Sort array
$ContentItemsSorted = $ContentItemsUnsorted;

$output .= implode('', array_column($ContentItemsSorted, 'text')); //Add sorted array to output
 
$output .= "</channel>\n";
$output .= "</rss>\n";

echo $output;

?>