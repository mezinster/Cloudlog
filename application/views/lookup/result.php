<?php
echo '
    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
    <table style="width:100%" class="table-sm table table-bordered table-hover table-striped table-condensed text-center">
	    <thead class="sticky-top bg-white">
			<tr>
				<th style="min-width: 60px;">Mode</th>';
			foreach($bands as $band) {
				echo '<th style="min-width: 50px;">' . $band . '</th>';
			}
    echo '</tr>
		</thead>
		<tbody>';

// Consolidate SSB, LSB, USB into SSB
$consolidatedResult = [];
foreach ($result as $mode => $value) {
	$normalizedMode = strtoupper($mode);
	// Convert LSB and USB to SSB
	if ($normalizedMode === 'LSB' || $normalizedMode === 'USB') {
		$normalizedMode = 'SSB';
	}
	// Merge into consolidatedResult
	if (!isset($consolidatedResult[$normalizedMode])) {
		$consolidatedResult[$normalizedMode] = $value;
	} else {
		// Merge band data - prioritize 'C' over 'W', and 'W' over '-'
		foreach ($value as $band => $val) {
			if ($val === 'C' || $consolidatedResult[$normalizedMode][$band] === '-') {
				$consolidatedResult[$normalizedMode][$band] = $val;
			} elseif ($val === 'W' && $consolidatedResult[$normalizedMode][$band] === '-') {
				$consolidatedResult[$normalizedMode][$band] = $val;
			}
		}
	}
}

// Sort to put SSB near the top
uksort($consolidatedResult, function($a, $b) {
	if ($a === 'SSB') return -1;
	if ($b === 'SSB') return 1;
	return strcmp($a, $b);
});

foreach ($consolidatedResult as $mode => $value) {
	echo '<tr>
			<td style="font-weight: 500; background-color: #f8f9fa;">'. strtoupper($mode) .'</td>';
	foreach ($value as $key => $val) {
		switch($type) {
			case 'dxcc': $linkinfo = '<a href=\'javascript:displayContacts("'.str_replace("&", "%26", $dxcc).'","' . $key . '","' . $mode . '","DXCC2")\'>'  . $val . '</a>'; break;
			case 'iota': $linkinfo = '<a href=\'javascript:displayContacts("'.str_replace("&", "%26", $iota).'","' . $key . '","' . $mode . '","IOTA")\'>'   . $val . '</a>'; break;
			case 'vucc': $linkinfo = '<a href=\'javascript:displayContacts("'.str_replace("&", "%26", $grid).'","' . $key . '","' . $mode . '","VUCC")\'>'   . $val . '</a>'; break;
			case 'cq':  $linkinfo = '<a href=\'javascript:displayContacts("'.str_replace("&", "%26", $cqz).'","'  . $key . '","' . $mode . '","CQZone")\'>' . $val . '</a>'; break;
			case 'was':  $linkinfo = '<a href=\'javascript:displayContacts("'.str_replace("&", "%26", $was).'","'  . $key . '","' . $mode . '","WAS")\'>'    . $val . '</a>'; break;
			case 'sota': $linkinfo = '<a href=\'javascript:displayContacts("'.str_replace("&", "%26", $sota).'","' . $key . '","' . $mode . '","SOTA")\'>'   . $val . '</a>'; break;
			case 'wwff': $linkinfo = '<a href=\'javascript:displayContacts("'.str_replace("&", "%26", $wwff).'","' . $key . '","' . $mode . '","WWFF")\'>'   . $val . '</a>'; break;
		}

		$info = '<td>';

		if ($val == 'W') {
			$info .= '<div class=\'bg-danger awardsBgDanger\'>' . $linkinfo . '</div>';
		}
		else if ($val == 'C') {
			$info .= '<div class=\'bg-success awardsBgSuccess\'>' . $linkinfo . '</div>';
		}
		else {
			$info .= $val;
		}

		$info .= '</td>';

		echo $info;
	}
	echo '</tr>';
}

echo '</tbody></table>
    </div>';
?>

