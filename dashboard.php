<?php

include("inc/globals.php");

$DateCurrent = date('D, d M Y H:i:s', time());
$DateReference = "";
$DateLast = "";
$DateNext = "";

$json = file_get_contents($reminderFile);
$reminders = json_decode($json, true);

$ReminderTitle = "";
$StatusMessage = "";

include_once 'inc/lang/'.$languageCode.'.php';

if(isset($_GET['start'])) {
	$StatusType = "start";
	$ReminderID = $_GET['start'];
	
	$PathReminderTimestamp = "./" . $DirTimestamps . "/" . $ReminderID . ".txt";
	$PathReminderGuid = "./" . $DirTimestamps . "/" . $ReminderID . "_guid.txt";
	
	$row = array_search($ReminderID, array_column($reminders, 0)); // Get array index
	$ReminderInterval = $reminders[$row][2];
	
	if(strpos($ReminderInterval, 'Monthly') !== false){ //Check if monthly
		$DateLast = str_replace("Monthly", "", $ReminderInterval); //Get day
		
		$MonthLast = date("m") - 1;
		if ($MonthLast == 0){
			$MonthLast = 12;
			$YearLast = date("Y") - 1;
		}
		else {
			$YearLast = date("Y");
		}
		
		$DateLast = $YearLast . '-' . $MonthLast . '-' . $DateLast; //Add year and month		
	}
	elseif(strpos($ReminderInterval, '-') !== false){ //Check if date
		$DateLast = date("Y") - 1 . '-' . $ReminderInterval; //Add year
	}
	
	$DateLast = date('D, d M Y H:i:s', strtotime($DateLast)); //Add correct formatting
	$DateLast = date('D, d M Y H:i:s', strtotime('+6 hours', strtotime($DateLast))); //Add 6 hrs
	
	if(file_exists($PathReminderTimestamp)) {
		unlink($PathReminderTimestamp); // Delete Timestamp File
	}
	$TimestampFile = fopen($PathReminderTimestamp, "w"); //Create and open Timestamp File
	fwrite($TimestampFile, $DateLast); //Insert last scheduled Date
	fclose($TimestampFile); //Close Timestamp File	
	
	
	if(file_exists($PathReminderGuid)) {
		unlink($PathReminderGuid); // Delete Guid File
	}
	$guid = rand(); //Generate Random Number
	$GuidFile = fopen($PathReminderGuid, "w"); //Create and open Guid File
	fwrite($GuidFile, $guid); //Insert Guid
	fclose($GuidFile); //Close Guid File
	
	header("Location: dashboard.php?status=success-" . $StatusType . "&" . "id=" . $ReminderID);
	die();
}
elseif(isset($_GET['stop'])) {
	$StatusType = "stop";
	$ReminderID = $_GET['stop'];
	$PathReminderTimestamp = "./" . $DirTimestamps . "/" . $ReminderID . ".txt";
	$PathReminderGuid = "./" . $DirTimestamps . "/" . $ReminderID . "_guid.txt";
	
	if(file_exists($PathReminderTimestamp)) {
		unlink($PathReminderTimestamp); // Delete Timestamp File
	}
	
	if(file_exists($PathReminderGuid)) {
		unlink($PathReminderGuid); // Delete Guid File
	}
	
	header("Location: dashboard.php?status=success-" . $StatusType . "&" . "id=" . $ReminderID);
	die();
}

$output = "";

if(isset($_GET['status']) && isset($_GET['id'])) {
	$ReminderID = $_GET['id'];
	
	for ($row = 0; $row < count($reminders); $row++) {
		if($reminders[$row][0] == $ReminderID){
			$ReminderTitle = $reminders[$row][1];
			break;
		}
	}
	
	if ($_GET['status'] == "success-start"){
		$StatusMessage = "<strong>" . $ReminderTitle . "</strong>" . " ".$language['statusMessageStarted'].".";
	}
	elseif ($_GET['status'] == "success-stop"){
		$StatusMessage = "<strong>" . $ReminderTitle . "</strong>" . " ".$language['statusMessageStopped'].".";
	}
	
	$output .= "<div class=\"message_status\">" . $StatusMessage . "</div>";
}

$output .= "<table id=\"sortTable\">";

$output .= "<tr>";
$output .= "<th>".$language['tableTitle']."<br><i>ID (".$language['tableGroup'].")</i></th>";
$output .= "<th>Update<br><i>".$language['tableUpdateLast']."</i></th>";
$output .= "<th>".$language['tableIntervalType']."</th>"; //Interval
$output .= "<th>Update<br><i>".$language['tableUpdateNext']."</i></th>";
$output .= "<th> </th>"; //Restart Link
$output .= "</tr>";

for ($row = 0; $row < count($reminders); $row++) {
	$ReminderID = $reminders[$row][0];
	$ReminderGroup = $reminders[$row][3];
	$PathReminderTimestamp = "./" . $DirTimestamps . "/" . $ReminderID . ".txt";
	$PathReminderGuid = "./" . $DirTimestamps . "/" . $ReminderID . "_guid.txt";
	
	$ReminderTitle = $reminders[$row][1];
	$ReminderInterval = $reminders[$row][2];

	$guid = ""; //Reset Guid
	
	$output .= "<tr>";

	$output .= "<td>" .$ReminderTitle. "<br><i>".$ReminderID." (".$ReminderGroup.")</i></td>"; //Titel
	
	if (file_exists($PathReminderTimestamp) && file_exists($PathReminderGuid)) { //Check if Timestamp File exists
		$DateReference = file_get_contents($PathReminderTimestamp); //Get Date from Timestamp File	

		$output .= "<td>" . date("d.m.Y", strtotime($DateReference)) . "</td>"; //Letztes Update	
		
		if(strpos($ReminderInterval, 'Monthly') !== false){ //Check if monthly
		
			$DatePreCalculated = str_replace("Monthly", "", $ReminderInterval); //Get day		
			$DatePreCalculated = date("Y") . '-' . date("m") . '-' . $DatePreCalculated; //Pre-calculate next date
			$date = new DateTime($DatePreCalculated);
			$now = new DateTime();

			if($date < $now) { //Check if pre-calculated date is in past
				$DateNext = str_replace("Monthly", "", $ReminderInterval); //Get day

				$MonthNext = date("m") + 1;
				if ($MonthNext == 13){
					$MonthNext = "01";
					$YearNext = date("Y") + 1;
				}
				else {
					$YearNext = date("Y");
				}
				
				$DateNext = $YearNext . '-' . $MonthNext . '-' . $DateNext; //Add year and month
			}
			else {
				$DateNext = str_replace("Monthly", "", $ReminderInterval); //Get day		
				$DateNext = date("Y") . '-' . date("m") . '-' . $DateNext; //Add current year and current month
			}
		
			
			$output .= "<td>&rarr;<br><i>".$language['tableIntervalMonthly']."</i></td>"; //Intervall-Typ
		}
		elseif(strpos($ReminderInterval, '-') !== false){ //Check if date
			$DatePreCalculated = date("Y") . '-' . $ReminderInterval; //Pre-calculate next date
			$date = new DateTime($DatePreCalculated);
			$now = new DateTime();

			if($date < $now) { //Check if pre-calculated date is in past
				$DateNext = date("Y") + 1 . '-' . $ReminderInterval; //Add next year
			}
			else {
				$DateNext = date("Y") . '-' . $ReminderInterval; //Add current year
			}
			
			$output .= "<td>&rarr;<br><i>".$language['tableIntervalYearly']."</i></td>"; //Intervall-Typ
		}
		
		$output .= "<td>". date("d.m.Y", strtotime($DateNext)) ."</td>"; //Nächstes Update		

		$output .= "<td><a class=\"stop\" href=\""."dashboard.php?stop=".$ReminderID."\" target=\"_self\">".$language['tableStop']."</a></td>"; //Stop Link

	}
	else { //If Timestamp File does NOT exist
		$output .= "<td>-</td>"; //Letztes Update
		
		if(strpos($ReminderInterval, 'Monthly') !== false){ //Check if monthly
			$DateNext = str_replace("Monthly", "", $ReminderInterval); //Get day
			$DateNext = date("Y") . '-' . date("m") . '-' . $DateNext; //Add year and month
			$output .= "<td>&rarr;<br><i>".$language['tableIntervalMonthly']."<br>".$language['tableIntervalOn']." " . 
			date("d.", strtotime($DateNext)) 
			."</i></td>"; //Intervall-Typ
		}
		elseif(strpos($ReminderInterval, '-') !== false){ //Check if date
			$DateNext = date("Y") . '-' . $ReminderInterval; //Add year
			$output .= "<td>&rarr;<br><i>".$language['tableIntervalYearly']."<br>".$language['tableIntervalOn']." " . 
			date("d.m.", strtotime($DateNext)) 
			."</i></td>"; //Intervall-Typ
		}
		
		$output .= "<td>-</td>"; //Nächstes Update
		$output .= "<td><a class=\"start\" href=\""."dashboard.php?start=".$ReminderID."\" target=\"_self\">".$language['tableStart']."</a></td>"; //Start Link
	}	
	$output .= "</tr>";
}


 
$output .= "</table>";



?>

<!doctype html>

<html lang="<?php echo $languageCode; ?>">
<head>
  <meta charset="utf-8">
  <meta name="robots" content="noindex"/>
  <meta name="robots" content="nofollow"/>

  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="viewport" content="initial-scale=1,minimum-scale=1,maximum-scale=1">
  <meta name="apple-mobile-web-app-title"content="Dashboard">

  <title>Dashboard</title>
  <meta name="description" content="Dashboard">
  <meta name="author" content="Dashboard">
  
  <link rel="stylesheet" href="inc/styles.css">

</head>

<body>

<input type="text" id="searchbar" onkeyup="filterElements()" placeholder="<?php echo $language['search']; ?>">

<?php echo $output; ?>

<a class="footer_button" href="edit.php" target="_self"><?php echo $language['editList']; ?></a>

<script>
function sortTable() {
  var table, rows, switching, i, x, y, shouldSwitch;
  table = document.getElementById("sortTable");
  switching = true;
  /*Make a loop that will continue until
  no switching has been done:*/
  while (switching) {
    //start by saying: no switching is done:
    switching = false;
    rows = table.rows;
    /*Loop through all table rows (except the
    first, which contains table headers):*/
    for (i = 1; i < (rows.length - 1); i++) {
      //start by saying there should be no switching:
      shouldSwitch = false;
      /*Get the two elements you want to compare,
      one from current row and one from the next:*/
      x = rows[i].getElementsByTagName("TD")[0];
      y = rows[i + 1].getElementsByTagName("TD")[0];
      //check if the two rows should switch place:
      if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
        //if so, mark as a switch and break the loop:
        shouldSwitch = true;
        break;
      }
    }
    if (shouldSwitch) {
      /*If a switch has been marked, make the switch
      and mark that a switch has been done:*/
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
    }
  }
}

sortTable();
</script>

<script>
function filterElements() {
  // Declare variables
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById("searchbar");
  filter = input.value.toUpperCase();
  table = document.getElementById("sortTable");
  tr = table.getElementsByTagName("tr");

  // Loop through all table rows, and hide those who don't match the search query
  for (i = 0; i < tr.length; i++) {
    td = tr[i].getElementsByTagName("td")[0];
    if (td) {
      txtValue = td.textContent || td.innerText;
      if (txtValue.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
    }
  }
}

var startFocusInput = document.getElementById('searchbar');
startFocusInput.focus();
startFocusInput.select();
</script>

</body>
</html>