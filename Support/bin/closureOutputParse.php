<?

$input = file_get_contents($argv[1]);
$messages = explode("\n",$input);
$showWarnings = $argc < 3;

echo "<style>
ul {
	list-style: none;
	margin-left: 0;
	padding-left: 0;
}
.warning {
	background-color: #FFD685;
}
.error {
	background-color: #CC8080;
}
</style>";

$errors = $warnings = array();

// We start from 3, because the first 3 lines not are to be used in output
for ($i = 3; $i < count($messages); $i++) {
	if ($showWarnings) {
		$info = match($messages, $i, 'WARNING');
		if (!empty($info)) {
			$warnings[] = $info;
			continue;
		}
	} 
	$info = match($messages, $i, 'ERROR');
	if (!empty($info)) {
		$errors[] = $info;
		continue;
	}
}

echo "<ul>";
if (!empty($errors)) {
	foreach ($errors as $info) {
		print outputLi($info,'ERROR');
	}
}
if (!empty($warnings)) {
	foreach ($warnings as $info) {
		print outputLi($info,'WARNING');
	}
}
echo "</ul>";

function match(&$messages, &$i, $type) {
	$info = array();
	
	// Check if output matches regex
	if (preg_match("/^([\/\s\w]+)\/(\w+\.js):(\d+): ".$type." - (.+)$/", $messages[$i], $matches)) {
		
		// The errorText is all the lines until next empty line
		$errorText = array();;
		while ($messages[$i++] != "") {
			$errorText[] = $messages[$i];
		}
		$info = array(
			// Path to the file
			'path' => $matches[1],
			
			// File name
			'file' => $matches[2],
			
			// Line number
			'lineNo' => $matches[3],
			
			// Error message from the compiler
			'msg' => $matches[4],
			
			// Where in the code the bug is found
			'errorText' => join("\n",$errorText)
		);
	}
	return $info;
}

function outputLi($m, $type) {
	$str  = '<li class="' . strtolower($type) . '">';
		$str .= '<a href="txmt://open?url=file://' . $m['path'] . '/' . $m['file'] . '&line=' . $m['lineNo'] . '">';
			$str .= $type . ': ' . $m['file'] . ' - ' . $m['lineNo'];
		$str .= '</a>';
		$str .= '<p>' . $m['msg'] . '</p>';
		$str .= '<small><pre>';
			$str .= $m['errorText'];
		$str .= '</pre></small>';
	$str .= '</li>';
	return $str;
}
?>