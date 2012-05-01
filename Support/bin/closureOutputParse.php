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
	if (preg_match("/^([\/\s\w]+)\/(\w+\.js):(\d+): ".$type." - ([\w\s]+)/", $messages[$i], $matches)) {
		$info = array(
			'path' => $matches[1],
			'file' => $matches[2],
			'lineNo' => $matches[3],
			'msg' => $matches[4],
			'lineText' => $messages[$i++],
			'marker' => $messages[$i++]
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
			$str .= $m['lineText'] . "\n" . $m['marker'];
		$str .= '</pre></small>';
	$str .= '</li>';
	return $str;
}
?>