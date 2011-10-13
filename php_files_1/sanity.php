<script>
	function toggleDisplayById(id,display_style,new_display) {
		if (!id) return false;
		ele = document.getElementById(id);
		if (!ele)
			return false;
		if (!display_style)
			display_style = 'inline';
		if (new_display)
			ele.style.display = new_display;
		else
			ele.style.display = (ele.style.display == 'none')? display_style : 'none';
	}
</script>

<?php
$scriptList = array (	'datacache/datacache.php' . '?method=disk' ,
						'datacache/datacache.php' . '?method=shm' ,
						'debugger/debugger.php',
						'jb/jb.php',
						'ZF/html/index.php',
						'loader/classes/tryAllClasses.php',
						'loader/Escalation23540/enc_no_optimization/testenc32-64.php',
						'loader/Escalation23540/enc/testenc32-64.php',
						//'monitor_create_events.php',
						);

//if not win run also the zds test
if (DIRECTORY_SEPARATOR != '\\') {
	$scriptList[] = 'zds/zds.php';
}				

?>
<table border="1" cellspacing="2" width="100%">
<tr>
	<th style="background-color: yellow">Request</th>
	<th style="background-color: yellow">Status</th>
	<th style="background-color: yellow">Output</th>
</tr>

<?php 
foreach($scriptList as $scriptUrl) {
	echo "<tr>";
	$retValue = file(	'http://' . $_SERVER["SERVER_NAME"] .
						':' . $_SERVER["SERVER_PORT"] .
						dirname($_SERVER["REQUEST_URI" ]) . '/' .
						$scriptUrl,
						FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

	echo "<td width=\"10%\" style=\"font-weight: bold\" >$scriptUrl</td>";
	if ($retValue) {
		$lastLineIndex = count($retValue) - 1;
		$retValue[$lastLineIndex] = trim($retValue[$lastLineIndex]);
		$status = substr($retValue[$lastLineIndex] , -1);
		
		if ($status == 1) {
			$status = 'Passed';
			$style = 'style="background-color: green"';
		} else {
			$status = 'Failed';
			$style = 'style="background-color: red"';
		}
		echo "<td $style>$status</td>";
		
		if (count($retValue) == 1) {
			$output = substr($retValue[0] , 0, (strlen($retValue[0]) - 1));
		} else {
			$output = implode('<br>', $retValue);
		}
		$spanId = md5($scriptUrl);
		echo '<td>
				<span 
						id="output_link' .  $spanId . '" 
						style="cursor:pointer;" 
						onclick="toggleDisplayById(\'output_link' .  $spanId . '\');toggleDisplayById(\'output' .  $spanId . '\')">click to see output
				</span>
				<span 
						style="cursor:pointer;display: none;" 
						id="output' .  $spanId . '" 
						onclick="toggleDisplayById(\'output_link' .  $spanId . '\');toggleDisplayById(\'output' .  $spanId . '\')">
					' . trim($output) . '
				</span>
			</td>';
		
	} else {
		echo '<td width="10%" style="background-color: red">Failed</td>';
		echo '<td>Failed to perform request' . '</td>';
	}
	echo "</tr>";
}

?>

</table>