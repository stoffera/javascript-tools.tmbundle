<?
$input = file_get_contents($argv[1]);
$messages = explode("\n",$input);
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
echo "<ul>";

for ($i=0; $i<count($messages); $i++) {
	if ($argc == 2 && matchWarning($messages[$i],$messages, $i)) continue;
	if (matchError($messages[$i], $messages, $i)) continue;
}

echo "</ul>";

function matchWarning($text,&$messages,&$i) {
	if (preg_match("/^([\/\s\w]+)\/(\w+\.js):(\d+): WARNING - ([\w\s]+)/",$text,$matches)) {
		$path = $matches[1];
		$fil = $matches[2];
		$linje = $matches[3];
		$msg = $matches[4];
		echo "<li class='warning'><a href='txmt://open?url=file://$path/$fil&line=$linje'>WARNING: $fil - $linje</a> <p>$msg</p>\n";
		echo "<small><pre>".$messages[$i+1]."\n".$messages[$i+2]."</pre></small></li>";
		$i += 2;
	}
	else return false;
}

function matchError($text,&$messages,&$i) {
	if (preg_match("/^([\/\s\w]+)\/(\w+\.js):(\d+): ERROR - ([\w\s\"\.]+)/",$text,$matches)) {
		$path = $matches[1];
		$fil = $matches[2];
		$linje = $matches[3];
		$msg = $matches[4];
		echo "<li class='error'><a href='txmt://open?url=file://$path/$fil&line=$linje'>ERROR: $fil - $linje</a> <p>$msg</p>\n";
		echo "<small><pre>".$messages[$i+1]."\n".$messages[$i+2]."</pre></small></li>";
		$i += 2;
	}
	else return false;
}

?>