<?php

include("inc/globals.php");

header('Access-Control-Allow-Origin: *'); 

$json = file_get_contents($reminderFile);
$reminders = json_decode($json, true);

$StatusMessage = "";
$outputTop = "";
$output = "";

include_once 'inc/lang/'.$languageCode.'.php';

if( isset($_GET['status']) ) {
	if ($_GET['status'] == "success"){
		$StatusMessage = $language['statusMessageAdded'];
		$outputTop .= "<div class=\"message_status\">" . $StatusMessage . "</div>";
	}
	else {
		$StatusMessage = $language['statusMessageErrorInput'];
		$outputTop .= "<div class=\"message_status\">" . $StatusMessage . "</div>";
	}
}
elseif(isset($_GET['delete'])) {
	$ReminderID = $_GET['delete'];
	$PathReminderTimestamp = "./" . $DirTimestamps . "/" . $ReminderID . ".txt";
	$PathReminderGuid = "./" . $DirTimestamps . "/" . $ReminderID . "_guid.txt";
	
	if(!file_exists($PathReminderTimestamp) && !file_exists($PathReminderGuid)) { //If not active
		if(file_exists($PathReminderTimestamp)) {
			unlink($PathReminderTimestamp); // Delete Timestamp File
		}
		
		if(file_exists($PathReminderGuid)) {
			unlink($PathReminderGuid); // Delete Guid File
		}
		
		foreach($reminders as &$reminder) {
			if($reminder[0] == $ReminderID) {
				$reminder = null;
				break; //if there will be only one then break out of loop
			}
		}
		
		$reminders = array_filter($reminders); //Remove nulled arrays
		$reminders = array_values($reminders);//Reset array index
		
		$fp = fopen($reminderFile, 'w');
		fwrite($fp, json_encode($reminders));
		fclose($fp);

		$StatusMessage = $language['statusMessageDeleted'];
		$outputTop .= "<div class=\"message_status\">" . $StatusMessage . "</div>";
	}
	
	
}

$output .= "<table class=\"edit\" id=\"sortTable\">";

$output .= "<tr>";
$output .= "<th> </th>";
$output .= "<th> </th>";
$output .= "<th> </th>";
$output .= "<th> </th>";
$output .= "</tr>";

for ($row = 0; $row < count($reminders); $row++) {
	$ReminderID = $reminders[$row][0];
	$ReminderGroup = $reminders[$row][3];
	$ReminderTitle = $reminders[$row][1];
	$ReminderInterval = $reminders[$row][2];
	
	$PathReminderTimestamp = "./" . $DirTimestamps . "/" . $ReminderID . ".txt";
	$PathReminderGuid = "./" . $DirTimestamps . "/" . $ReminderID . "_guid.txt";

	$guid = ""; //Reset Guid
	
	$output .= "<tr>";

	if(file_exists($PathReminderTimestamp) || file_exists($PathReminderGuid)) { //If active
		$output .= "<td>" .$ReminderTitle. "<br>".$language['activated']."</td>"; //Titel
	}
	else {
		$output .= "<td>" .$ReminderTitle. "<br>".$language['deactivated']."</td>"; //Titel
	}
	
	$output .= "<td>".$ReminderID." (".$ReminderGroup.")</td>"; //ID
	$output .= "<td>" .$ReminderInterval. "</td>"; //Intervall
	
	if(file_exists($PathReminderTimestamp) || file_exists($PathReminderGuid)) { //If active
		$output .= "<td>".$language['deleteHint']."</td>"; //Stop Link
	}
	else {
		$output .= "<td><a onclick=\"confirmation(event)\" class=\"stop\" href=\""."edit.php?delete=".$ReminderID."\" target=\"_self\" >".$language['delete']."</a></td>"; //Stop Link
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
  <meta name="apple-mobile-web-app-title"content="Edit">

  <title>Edit</title>
  <meta name="description" content="Edit">
  <meta name="author" content="Edit">
  
  <link rel="stylesheet" href="inc/styles.css">

  <script>
function confirmation(e)
	{
		
		if(!confirm('<?php echo $language['deletePrompt']; ?>')) {
			e.preventDefault();
		}
	}
</script>
  
</head>

<body>

<input type="text" id="searchbar" onkeyup="filterElements()" placeholder="<?php echo $language['search']; ?>">

<?php echo $outputTop; ?>

<div class="form_wrapper">
<form enctype="multipart/form-data" class="form_edit" method="POST" action="update.php">

<label><?php echo $language['formLabelId']; ?></label>
<input type="text" name="ReminderID">

<label><?php echo $language['formLabelTitle']; ?></label>
<input type="text" name="ReminderTitle">

<label><?php echo $language['formLabelInterval']; ?></label>
<input type="text" name="ReminderInterval">

<label><?php echo $language['formLabelGroup']; ?></label>
<select name="ReminderGroup">
	<option value="All"><?php echo $language['formLabelGroupAll']; ?></option>
	<option value="Essential"><?php echo $language['formLabelGroupEssential']; ?></option>
	<option value="Sabbatical"><?php echo $language['formLabelGroupSabbatical']; ?></option>
</select>

<input type="submit" value="<?php echo $language['formSave']; ?>">

</form>

</div>

<?php echo $output; ?>

<a class="button_back" id="button_back" href="dashboard.php"><?php echo $language['back']; ?></a>

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
</script>

</body>
</html>