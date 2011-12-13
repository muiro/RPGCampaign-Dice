<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
<meta http-equiv="Content-Language" content="en_US" />
<link rel="stylesheet" type="text/css" href="diceroller.css" />
<title>RPGCampaign Dice Roller</title>
</head>
<body>
<div class='main'>

<?php
require_once('config/db.config.php');

$calling_page = $_SERVER["PHP_SELF"];

if ($_REQUEST["doAction"] != "view") {
  display_form();
}

if ($_REQUEST["doAction"] == "roll") {

  $roll_action = $_REQUEST["action"];

  if (empty($roll_action)) {
    $roll_action = "Sneaky Roll";
  }

  $errorMessage = "";
  if(empty($_REQUEST["character_name"])) {
    $errorMessage .= "<li>Please fill in character name</li>"; 
  }
  if(!is_numeric($_REQUEST["dice"])) {
    if(empty($_REQUEST["dice"])) {
      $errorMessage .= "<li>Please enter the number of dice to roll</li>";
    } else {
      $errorMessage .= "<li>Please enter the <b>number</b> of dice to roll</li>";
    }
  } else if ($_REQUEST["dice"] > 50) {
    $errorMessage .= "<li>Please decrease the amount of dice to roll</li>";
  }
  if ($_REQUEST["dice"] == 0) {
    $errorMessage .= "<li>Please enter the number of dice to roll</li>";  
  }

  refill_params($_REQUEST["character_name"], $roll_action, $_REQUEST["dice"], $_REQUEST["reroll"], "n", $_REQUEST["is_rote"]);

  if(!empty($errorMessage)) {
    echo "<div class='failure'>\n";
    echo "<p>Please correct your roll:</p>\n";
    echo "<ul>" . $errorMessage . "</ul>\n";
    echo "</div>\n";
#    echo '<script type="text/javascript">';
#    echo 'document.forms[0].character_name.value = "' . $_REQUEST["character_name"]  . '";';
#    echo 'document.forms[0].action.value = "' . $roll_action . '";';
#    echo 'document.forms[0].dice.value = "' . $_REQUEST["dice"]  . '";';
#    echo 'for (i=0;i<document.forms[0].reroll.length;i++) {';
#    echo ' if (document.forms[0].reroll[i].value == "' . $_REQUEST["reroll"] . '" ) {';
#    echo 'document.forms[0].reroll[i].checked = true;';
#    echo '}';
#    echo '}'; 
##    if ($_REQUEST["1cancel"] == "y" ) {
##      echo 'document.forms[0].1cancel.checked = true;';
##    }
##    if ($_REQUEST["chance_die"] == "y") {
##      echo 'document.forms[0].chance_die.checked = true;';
##    }
#    if ($_REQUEST["is_rote"] == "y") {
#      echo 'document.forms[0].is_rote.checked = true;';
#    }
#    echo '</script>';

  } else {
    // @TODO check for willpower checkbox and add a success
    $rollString = "";
    $successes = 0;
    $reroll = $_REQUEST["reroll"];
    if ($reroll == "n") {
      $reroll = 0;
    }
    if ( is_null($reroll) || !($reroll == 10 || $reroll == 9 || $reroll == 8 || $reroll == 0)) {
      $reroll = 10;
    }

    for ($i = 0; $i < $_REQUEST["dice"]; $i++) {
      $rollInt = rand(1,10);
      $originalRollInt = $rollInt;
#      $rollString .= $rollInt;
      if ($rollInt >= 8) {
        $successes++;
      }
      if ($_REQUEST["is_rote"] == "y") {
         if ($rollInt < 8) {
           $rollString .= "<s>" . $rollInt . "</s>, ";
           $roteReroll = rand(1,10);
           $rollString .= $roteReroll;
           if ($roteReroll >= 8 ) {
             $successes++;
           }
         } else {
           $rollString .= $rollInt;
         }
      } else {
        $rollString .= $rollInt;
      }
      // @TODO reinstate and update 1's cancel functionality
#      if ($_REQUEST["1cancel"] == "y" && $rollInt == 1 && $_REQUEST["is_rote"] != "y") {
#        $successes--;
#      }
      if ($rollInt >= $reroll && $reroll > 0) {
        $rollString .= " ("; 
      }
      while ($rollInt >= $reroll && $reroll > 0) {
        $rollInt = rand(1,10);
        $rollString .= $rollInt;
        if ($rollInt >= 8) {
          $successes++;
        }
        if ($_REQUEST["1cancel"] == "y" && $rollInt == 1) {
          $successes--;
        }
        if ($rollInt >= $reroll) {
          $rollString .= ", ";
        }
      }
      if ($originalRollInt >= $reroll && $reroll > 0) {
        $rollString .= ")";
      }
      if ($i + 1 < $_REQUEST["dice"]) {
        $rollString .= ", ";
      }
    }
    
    echo "<div class='result'>\n";
    echo "Character Name: " . $_REQUEST["character_name"] . "<br />\n";
    echo "Action: $roll_action<br />\n";
    echo "Dice Rolled: " . $_REQUEST["dice"] . "<br />\n";
    echo "Reroll: " . $reroll . "<br />\n";
#    echo "1's remove: " . $_REQUEST["1cancel"] . "<br />";
#    echo "Chance Die: " . $_REQUEST["chance_die"] . "<br />";
    echo "Rote: " . $_REQUEST["is_rote"] . "<br />\n";
    echo "Result: " . $rollString  . "<br />\n";
    echo "Successes: " . $successes . "<br />\n";
    echo "</div>\n";

    if($_REQUEST["is_rote"] == "y") {
      $is_rote = 1;
    } else {
      $is_rote = 0;
    }
    
    insert_roll($_REQUEST["character_name"], $roll_action, $reroll, $_REQUEST["dice"], 0, $successes, $rollString, $is_rote);

  }
  // @TODO add in doAction handler for initiative rolls
} else if ($_REQUEST["doAction"] == "chance") {
  $errorMessage = "";
  if(empty($_REQUEST["character_name"])) {
    $errorMessage .= "<li>Please fill in character name</li>";
  }
  if(!is_numeric($_REQUEST["dice"])) {
    if(empty($_REQUEST["dice"])) {
      $errorMessage .= "<li>Please enter the number of dice to roll</li>";
    } else {
      $errorMessage .= "<li>Please enter the <b>number</b> of dice to roll</li>";
    }
  }

  refill_params($_REQUEST["character_name"], $_REQUEST["action"], $_REQUEST["dice"], $_REQUEST["reroll"], "n", $_REQUEST["is_rote"]);

  if(!empty($errorMessage)) {
    echo "<div class='failure'>\n";
    echo "<p>Please correct your roll:</p>\n";
    echo "<ul>" . $errorMessage . "</ul>\n";
    echo "</div>\n";
#    echo '<script type="text/javascript">';
#    echo 'document.forms[0].character_name.value = "' . $_REQUEST["character_name"]  . '";';
#    echo 'document.forms[0].action.value = "' . $_REQUEST["action"]  . '";';
#    echo 'document.forms[0].dice.value = "' . $_REQUEST["dice"]  . '";';
#    echo 'for (i=0;i<document.forms[0].reroll.length;i++) {';
#    echo ' if (document.forms[0].reroll[i].value == "' . $_REQUEST["reroll"] . '" ) {';
#    echo 'document.forms[0].reroll[i].checked = true;';
#    echo '}';
#    echo '}';
#    echo '</script>';
  } else {
    $rollString = "";
    $successes = 0;
    $reroll = $_REQUEST["reroll"];
    if ($reroll == "n") {
      $reroll = 0;
    }
    if ( is_null($reroll) || !($reroll == 10 || $reroll == 9 || $reroll == 8 || $reroll == 0)) {
      $reroll = 10;
    }
    $rollInt = rand(1,10);
    $originalRollInt = $rollInt;
    $rollString .= $rollInt;
    if ($rollInt >= 8) {
      $successes++;
    }
    if ($rollInt == 1) {
      $successes = -1;
    }
    if ($rollInt >= $reroll && $reroll > 0) {
      $rollString .= "(";
    }
    while ($rollInt >= $reroll && $reroll > 0) {
      $rollInt = rand(1,10);
      $rollString .= $rollInt;
      if ($rollInt >= 8) {
        $successes++;
      }
      if ($rollInt >= $reroll) {
        $rollString .= ", ";
      }
    }
    if ($originalRollInt >= $reroll && $reroll > 0) {
      $rollString .= ")";
    }
    
    echo "<div class='result'>";
    echo "Character Name: " . $_REQUEST["character_name"] . "<br />\n";
    echo "Dice Rolled: 1 (chance roll)<br />\n";
    echo "Reroll: " . $reroll . "<br />\n";
    echo "Result: " . $rollString  . "<br />\n";
    echo "Successes: " . $successes . "<br />\n";
    echo "</div>\n";

    insert_roll($_REQUEST["character_name"], $_REQUEST["action"], $reroll, 1, 1, $successes, $rollString, 0);
  }
} else if ($_REQUEST["doAction"] == "view") {

  if (!empty($_REQUEST["roll"]) && is_numeric($_REQUEST["roll"])) {

    $roll = $_REQUEST["roll"];    

    $con = mysql_connect($dice_db_hostname, $dice_db_username, $dice_db_password) or die('DB connection error: ' . mysql_error());
    mysql_select_db($dice_db_database) or die('DB connection error: ' . mysql_error());
    $roll_data =  mysql_query("SELECT *, date_format(roll_date_time,'%m/%d/%y - %r') as date_string FROM " . $dice_db_table . " WHERE dice_roll_history_id = " . $roll . " ORDER BY dice_roll_history_id DESC LIMIT 1") or die('DB Query failed: ' . mysql_error()); 
  
    if (mysql_numrows($roll_data)) {
      $dice_roll_history_id = mysql_result($roll_data, $i, "dice_roll_history_id");
      $character_name = mysql_result($roll_data, $i, "character_name");
      $action = mysql_result($roll_data, $i, "action");
      $reroll_option = mysql_result($roll_data, $i, "reroll_option");
      $number_of_dice = mysql_result($roll_data, $i, "number_of_dice");
      $chance_die = mysql_result($roll_data, $i, "chance_die");
      $roll_date_time = mysql_result($roll_data, $i, "date_string");
      $number_successes = mysql_result($roll_data, $i, "number_successes");
      $result = mysql_result($roll_data, $i, "result");
      $is_rote = mysql_result($roll_data, $i, "is_rote");

      $success_string = calc_success($number_successes, $chance_die);

      echo "<div class='result'>
Character Name: $character_name <br />
Action: $action <br />
Reroll: $reroll_option <br />
Dice Rolled: $number_of_dice <br />
Chance die: $chance_die <br />
Roll Date: $roll_date_time <br />
Successes: $number_successes <br />
Result: $success_string <br />
Roll: $result <br />
Rote: $is_rote
</div>";

    } else {
      echo "<div class='failure'>\n";
      echo "no data for this roll\n";
      echo "</div>\n";
    }

    mysql_free_result($roll_data);
    mysql_close($con);
  } else {
    echo "<div class='failure'>\n";
    echo "invalid roll number\n";
    echo "</div>\n";
  }
} else {
}
if ($_REQUEST["doAction"] != "view") {
  display_roll_history(20);
}
?>

<br /><br />
<div class="bottom">
<a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-sa/3.0/88x31.png" /></a>
<a href="http://validator.w3.org/check?uri=referer"><img src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0 Transitional" height="31" width="88" /></a>
<a href="http://jigsaw.w3.org/css-validator/check/referer"><img style="border:0;width:88px;height:31px" src="http://jigsaw.w3.org/css-validator/images/vcss" alt="Valid CSS!" /></a>
</div>
</div>
<script type="text/javascript">
function validation() {
	frm = document.forms[0];
        // do any client side validation needed here
        frm.submit();
}
function special_roll(roll_type) {
  switch(roll_type) {
    case "chance":
      document.forms[0].doAction.value = "chance";
      document.forms[0].dice.value = "1";
      document.forms[0].action.value = "Chance Roll";
      break;
    case "initiative":
      document.forms[0].doAction.value = "initiative";
      document.forms[0].dice.value = "1";
      document.forms[0].action.value = "Initiative Roll";
      break;    
  }
  validation();
}
</script>
<?php
function display_roll_history($number_of_rolls){
  global $dice_db_hostname, $dice_db_username, $dice_db_password, $dice_db_database, $dice_db_table, $calling_page;

  if (empty($number_of_rolls) || !is_numeric($number_of_rolls)) {
    $number_of_rolls = 20;
  }
  $con = mysql_connect($dice_db_hostname, $dice_db_username, $dice_db_password)
        or die('DB connection error: ' . mysql_error());
  mysql_select_db($dice_db_database) or die('DB connection error: ' . mysql_error());
  $last_n_rolls =  mysql_query("SELECT *, if(ifnull(action,'') = '',character_name,concat(character_name,':')) as fixed_charname, date_format(roll_date_time, '%m/%d/%y<br />%r') as date_string  FROM " . $dice_db_table . " ORDER BY dice_roll_history_id DESC LIMIT " . $number_of_rolls) or die('DB Query failed: ' . mysql_error());

  $roll_count = mysql_numrows($last_n_rolls);
  $i = 0;
  echo "<div class='roll_history'>\n";
  echo "Last " . $number_of_rolls  . " Rolls<br /><br />\n";
  echo "<table border='1'>\n";
  while ($i < $roll_count) {
    $dice_roll_history_id = mysql_result($last_n_rolls, $i, "dice_roll_history_id");
    $character_name = mysql_result($last_n_rolls, $i, "fixed_charname");
    $action = mysql_result($last_n_rolls, $i, "action");
    $reroll_option = mysql_result($last_n_rolls, $i, "reroll_option");
    $number_of_dice = mysql_result($last_n_rolls, $i, "number_of_dice");
    $chance_die = mysql_result($last_n_rolls, $i, "chance_die");
    $roll_date_time = mysql_result($last_n_rolls, $i, "date_string");
    $number_successes = mysql_result($last_n_rolls, $i, "number_successes");
    $result = mysql_result($last_n_rolls, $i, "result");
    $is_rote = mysql_result($last_n_rolls, $i, "is_rote");

    $rote_roll = "";
    if ($is_rote == 1) {
      $rote_roll = "<br /><b>Rote Roll</b>";
    }
    $modifiers = "";
    if ($reroll_option != 10) {
      switch ($reroll_option) {
        case 9:
          $modifiers = "<br />9again";
          break;
        case 8:
          $modifiers = "<br />8again";
          break;
        case 0:
          $modifiers = "<br />no reroll";
          break;
        default:
          $modifiers = "";
          break;
      }
    }

    $success_string = calc_success($number_successes, $chance_die);

    $roll_class = "normal_roll";

    if ($chance_die == 1) {
      $roll_class = "chance_roll";
    } else if ($is_rote == 1) {
      $roll_class = "rote_roll";
    } else if ($initiative_roll == 1) {
      $roll_class = "initiative_roll";
    }

    echo "<tr class='$roll_class'>";
    echo "<td><a href='$calling_page?doAction=view&amp;roll=$dice_roll_history_id'>$roll_date_time</a></td>\n";
    echo "<td>$character_name $action<br />Dice: $number_of_dice$rote_roll$modifiers</td>\n";
    // @TODO Wrap individual rolls with success/failure/etc css tags, or do this during the building of the string in the rolling logic
    echo "<td>Successes: $number_successes<br />Result: $success_string</td>\n";
    echo "<td>$result</td>\n";
    echo "</tr>\n";

    $i++;
  }
  echo "</table>\n";
  echo "</div>\n";

  mysql_free_result($last_n_rolls);
  mysql_close($con);  
}

function display_form(){
  global $calling_page;
  echo '<div class="roller">
<form method="post" action="' . $calling_page . '"><input type="hidden" name="doAction" value="roll" />
<label for="character">Character Name: </label><input type="text" name="character_name" size="20" maxlength="35" value="" id="character" />
<label for="action">Action: </label><input type="text" name="action" size="20" maxlength="50" value="" id="action" />
<label for="dice">Dice: </label><input type="text" name="dice" size="3" maxlength="2" value="" id="dice" /><br />
<fieldset><legend>Explode/Reroll Options</legend>
<label for="tenagain">10-again: </label><input type="radio" name="reroll" value="10" checked="checked" id="tenagain" /> &nbsp;-&nbsp;
<label for="nineagain">9-Again: </label><input type="radio" name="reroll" value="9" id="nineagain" /> &nbsp;-&nbsp;
<label for="eightagain">8-Again: </label><input type="radio" name="reroll" value="8" id = "eightagain" /> &nbsp;-&nbsp;
<label for="noreroll">No Rerolls: </label><input type="radio" name="reroll" value="n" id="noreroll" /> &nbsp;&nbsp;
</fieldset>
<label for="roteaction">Rote Action: </label><input type="checkbox" name="is_rote" value="y" id = "roteaction"/><br />
<label for="ones_cancel">1\'s Remove: </label><input type="checkbox" name="ones_cancel" value="y" id = "ones_cancel"/><br />
<label for="add_willpower">Add Willpower: </label><input type="checkbox" name="add_willpower" value="y" id = "add_willpower" /><br />
<input type="button" value="Roll" onclick="javascript:validation()" title="Roll" />
<input type="button" value="Chance" title="Chance" onclick="javascript:special_roll(\'chance\')" />
<input type="button" value="Initiative" title="Initiative" onclick="javascript:special_roll(\'initiative\')" />
</form>
</div>';
}

function insert_roll($character_name, $action, $reroll, $dice, $chance_roll, $successes, $result, $is_rote){
    // @TODO Add options to track willpower usage, init roll, 1's cancel (re-add). Will need DB fields as well.
  global $dice_db_hostname, $dice_db_username, $dice_db_password, $dice_db_database, $dice_db_table;
  
//  $insert_roll_query = "INSERT INTO " . $dice_db_table . " (character_name, action, reroll_option, number_of_dice, chance_die, roll_date_time, number_successes, result, is_rote) VALUES ('" . mysql_real_escape_string($character_name) . "', '" . mysql_real_escape_string($action) . "', " . $reroll . ", " . $dice . ", " . $chance_roll . ", now(), " . $successes . ", '" . mysql_real_escape_string($result) . "', " . $is_rote . ")";

  $con = mysql_connect($dic_db_hostname, $dice_db_username, $dice_db_password)
        or die('DB connection error: ' . mysql_error());
  $insert_roll_query = "INSERT INTO " . $dice_db_table . " (character_name, action, reroll_option, number_of_dice, chance_die, roll_date_time, number_successes, result, is_rote) VALUES ('" . mysql_real_escape_string($character_name) . "', '" . mysql_real_escape_string($action) . "', " . $reroll . ", " . $dice . ", " . $chance_roll . ", now(), " . $successes . ", '" . mysql_real_escape_string($result) . "', " . $is_rote . ")";

  mysql_select_db($dice_db_database) or die('DB connection error: ' . mysql_error());
    $inserted =  mysql_query($insert_roll_query) or die('DB Query failed: ' . mysql_error());
  mysql_close($con);
}

function calc_success($number_successes, $chance_die) {
  $success_string = "";
  
  if (!is_numeric($number_successes) || !is_numeric($chance_die) || !($chance_die == 0 || $chance_die == 1)) {
    return("Can't calculate success");
  }

  if ($chance_die == 1) {
    if ($number_successes == -1) {
      $success_string = "Dramatic Failure";
    } else if ($number_successes < 1) {
      $success_string = "Failure";
    } else {
      $success_string = "Success";
    }
  } else {
    if ($number_successes < 1) {
      $success_string = "Failure";
    } else if ($number_successes >= 5) {
      $success_string = "Exceptional Success";
    } else {
      $success_string = "Success";
    }
  }

  return($success_string);
}

function refill_params($character_name, $roll_action, $dice, $reroll, $ones_cancel, $is_rote) {
  echo '<script type="text/javascript">';
    echo 'document.forms[0].character_name.value = "' . $character_name  . '";';
    echo 'document.forms[0].action.value = "' . $roll_action . '";';
    echo 'document.forms[0].dice.value = "' . $dice  . '";';
    echo 'for (i=0;i<document.forms[0].reroll.length;i++) {';
    echo ' if (document.forms[0].reroll[i].value == "' . $reroll . '" ) {';
    echo 'document.forms[0].reroll[i].checked = true;';
    echo '}';
    echo '}';
    if ($ones_cancel == "y") {
      echo 'document.forms[0].ones_cancel.checked = true';
    }
    if ($is_rote == "y") {
      echo 'document.forms[0].is_rote.checked = true;';
    }
    echo '</script>';
}
?>
</body>
</html>
