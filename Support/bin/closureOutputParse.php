<?
class CompileOutputPrinter {
	
	/**
	 * How long in the array we have read
	 *
	 * @var int
	 */
	private $i = 0;
	
	/**
	 * The data array. One entry for each line
	 *
	 * @var array
	 */
	private $data = array();
	
	/**
	 * Array containing the warnings found
	 *
	 * @var array
	 */
	private $warnings = array();
	
	/**
	 * Array containing the errors found
	 *
	 * @var string
	 */
	private $errors = array();
	
	/**
	 * Run the parsning of the output data from the compiler
	 *
	 * @param string $filename The name and path of the file to parse
	 * @param boolean $showWarnings Whether warnings should be shown
	 * @return void
	 * @author Jesper Skytte Hansen
	 */
	public function run($filename, $showWarnings = false) {
		$this->_loadContents($filename);
		$types = array('ERROR');
		if ($showWarnings) {
			$types[] = 'WARNING';
		}
		$this->_parse($types);
	}
	
	/**
	 * Load the contents from the file
	 *
	 * @param string $filename 
	 * @return void
	 * @author Jesper Skytte Hansen
	 */
	private function _loadContents($filename) {
		$this->data = explode("\n", file_get_contents($filename));
	}
	
	/**
	 * Parse the contents of the $this->data array and put content into the corresponding arrays
	 *
	 * @param array $types Array of types to parse for
	 * @return void
	 * @author Jesper Skytte Hansen
	 */
	private function _parse($types) {

		// Change the types to regex structure
		$types = join('|', $types);

		// Find the length of the data array
		$length = count($this->data);
		
		// Run through each line
		for (; $this->i < $length; $this->i++) {
			$info = array();

			// Check if output matches regex
			if (preg_match("/^([\/\s\w]+)\/(\w+\.js):(\d+): (".$types.") - (.+)$/", $this->data[$this->i], $matches)) {

				// The errorText is all the lines until next empty line
				$errorText = array();;
				while ($this->data[$this->i+1] != "") {
					$errorText[] = $this->data[++$this->i];
				}
				
				// Find the charnum to set the cursor at
				$charNum = preg_match("/\^/", $errorText[count($errorText)-1], $charNumMatches, PREG_OFFSET_CAPTURE);

				$info = array(
					// Path to the file
					'path' => $matches[1],

					// File name
					'file' => $matches[2],

					// Line number
					'lineNo' => $matches[3],
					
					// Kind of output (error, warning)
					'type' => $matches[4],
					
					// Error message from the compiler
					'msg' => $matches[5],

					// Where in the code the bug is found
					'errorText' => join("\n",$errorText),

					// Which charnum to set the cursor at
					'charNo' => $charNum > 0 ? $charNumMatches[0][1] + 1 : 1
				);
				switch (strtolower($matches[4])) {
					case 'warning':
						$this->warnings[] = $info;
						break;
					case 'error':
						$this->errors[] = $info;
						break;
					default:
						break;
				}
			}
		}
		
	}
	
	/**
	 * Returns the HTML output
	 *
	 * @return void
	 * @author Jesper Skytte Hansen
	 */
	public function output() {
		
		if (count($this->errors) == 0 && count($this->warnings) == 0) {
			return "No warnings or errors was found.";
		}
		
		$d = "
			<style>
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
		
		$d .= "<ul>";
		
		// First print the errors
		if (!empty($this->errors)) {
			foreach ($this->errors as $info) {
				$d .= $this->_getLiHtml($info);
			}
		}
		
		// Then print warnings
		if (!empty($this->warnings)) {
			foreach ($this->warnings as $info) {
				$d .= $this->_getLiHtml($info);
			}
		}
		$d .= "</ul>";
		
		// Return the HTML
		return $d;
	}
	
	/**
	 * Get a single LI line
	 *
	 * @param string $m 
	 * @return void
	 * @author Jesper Skytte Hansen
	 */
	private function _getLiHtml($m) {
		$str  = '<li class="' . strtolower($m['type']) . '">';
			$str .= '<a href="txmt://open?url=file://' . $m['path'] . '/' . $m['file'] . '&line=' . $m['lineNo'] . '&column=' . $m['charNo'] . '" title="' . $m['path'] . '/' . $m['file'] . '">';
				$str .= $m['type'] . ': ' . $m['file'] . ' - ' . $m['lineNo'];
			$str .= '</a>';
			$str .= '<p>' . $m['msg'] . '</p>';
			$str .= '<small><pre>';
				$str .= $m['errorText'];
			$str .= '</pre></small>';
		$str .= '</li>';
		return $str;
	}
}

$class = new CompileOutputPrinter();
$class->run($argv[1], $argc < 3);
print $class->output();
?>