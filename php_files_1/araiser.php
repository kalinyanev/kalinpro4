<?php
//set the ip from where the test is running from
$SERVER_ADDRESS=$_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT'];


$sapi = php_sapi_name(); // cli if cli, other values such apache2handler if cgi
if ($sapi == 'cli') {
$nl = PHP_EOL;
}
else {
$nl = "<br>";
}

$issues = unserialize(file_get_contents("http://$SERVER_ADDRESS/wget_issues.php"));
//$issues = unserialize(file_get_contents("http://localhost:80/wget_issues.php"));
$old_issues = array();

$issues_len = sizeof($issues);
$grps_len = $new_grps_len = $delta = $repeats_len = $new_repeats_len = 0;
if ($issues_len > 0) {
foreach ($issues as $issue=>$grps) {
$old_issues[$issue] = array('grps'=>$grps, 'leng'=>sizeof($grps), 'lenr'=>0);
$repeats_len = 0;
foreach ($grps as $group=>$repeats) {
$repeats_len = $repeats_len + $repeats;
}
$old_issues[$issue]['lenr'] = $repeats_len; // total repeats for issue
}
}
else {
print "No issues found in the old db ! $nl";
}

//$req = file_get_contents("http://localhost:80/mtrig.php?sleep=1.5");


//file_get_contents("http://localhost/delete_all_events.php"); // clearing the db


$slow_exec_warn = 1100;
$slow_exec_crit = 2100;

$mem_warn = 5900000; // will cause a warning event
$mem_crit = 8900000; // will cause a critical event

for ($i=0; $i<2 ; $i++) {
file_get_contents("http://$SERVER_ADDRESS/mtrig.php?custom=classo_$i"); // custom event
file_get_contents("http://$SERVER_ADDRESS/mtrig.php?java=blabla_$i"); // java exception
file_get_contents("http://$SERVER_ADDRESS/mtrig.php?func_error=nosuchfile_$i"); // function error

file_get_contents("http://$SERVER_ADDRESS/mtrig.php?php_warn=nosuchfile_$i"); //    php warning
file_get_contents("http://$SERVER_ADDRESS/mtrig.php?php_error=nosuchfile_$i"); // php error

$slow_exec_warn = $slow_exec_warn + 50;
$slow_exec_crit = $slow_exec_crit + 50;   
file_get_contents("http://$SERVER_ADDRESS/mtrig.php?slow_exec=$slow_exec_warn"); // slow exec - warning
file_get_contents("http://$SERVER_ADDRESS/mtrig.php?slow_exec=$slow_exec_crit"); // slow exec - critical

$mem_warn = $mem_warn + 1000*$i;
$mem_crit = $mem_crit + 1000*$i;
file_get_contents("http://$SERVER_ADDRESS/mtrig.php?mem=$mem_warn"); // large mem usage in kb - warning
file_get_contents("http://$SERVER_ADDRESS/mtrig.php?mem=$mem_crit"); // large mem usage in kb - critical
}


sleep(1.5);

$issues = unserialize(file_get_contents("http://$SERVER_ADDRESS/wget_issues.php"));
#$issues = unserialize(file_get_contents("http://localhost:80/wget_issues.php"));
$issues_len = sizeof($issues);

$format_title = "%-44s %-7s %s \n";
$format = "%-44s \t %d \t %d \n";
$format_chg = "%-44s \t %s \t %s \n";
printf($format_title, 'RULE', 'GROUPS', 'REPEATS');

$new_issues = array();
if ($issues_len > 0) {
foreach ($issues as $issue=>$grps) {
$new_issues[$issue] = array('grps'=>$grps, 'leng'=>sizeof($grps), 'lenr'=>0);
$repeats_len = 0;
foreach ($grps as $group=>$repeats) {
$repeats_len = $repeats_len + $repeats;
}
$new_issues[$issue]['lenr'] = $repeats_len; // total repeats for issue

if (isset($old_issues[$issue])) {
if ($new_issues[$issue]['lenr'] == $old_issues[$issue]['lenr'] ) {
//print "INFO: $issue is unchanged and has " . $new_issues[$issue]['leng'] . " groups & " . $new_issues[$issue]['lenr'] . " repeats $nl";
printf($format, $issue, $new_issues[$issue]['leng'], $new_issues[$issue]['lenr']);
}
elseif ($new_issues[$issue]['leng'] != $old_issues[$issue]['leng']) {
$delta = $new_issues[$issue]['leng'] - $old_issues[$issue]['leng'];
//print "$issue groups have increased by $delta to " . $new_issues[$issue]['leng'] . " (with " . $new_issues[$issue]['lenr'] . " repeats) $nl";
$chgstr = $old_issues[$issue]['leng'] . "+$delta=" . $new_issues[$issue]['leng'];
printf($format_chg, $issue, $chgstr, $new_issues[$issue]['lenr']);
}
else {
$delta = $new_issues[$issue]['lenr'] - $old_issues[$issue]['lenr'];
$chgstr = $old_issues[$issue]['lenr'] . "+$delta=" . $new_issues[$issue]['lenr'];
//print "$issue repeats have increased by $delta to " . $new_issues[$issue]['lenr'] . " (with " . $new_issues[$issue]['leng'] . " groups) $nl";
printf($format_chg, $issue, $new_issues[$issue]['leng'], $chgstr);
}
}
else {
printf($format, "NEW: $issue", $new_issues[$issue]['leng'], $new_issues[$issue]['lenr']);           
}
}
}
else {
print "No issues found in the new db ! $nl";
} 
?>