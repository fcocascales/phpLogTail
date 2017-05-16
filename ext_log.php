<?php
  //---------------------------------------
  // MAIN

  define('NO_REQUIRE','ok');
  require_once('server/session.php');
  require_once('server/util.php');

  $size = '';
  $content = "";

  if (Session::isConnected() && Session::getAccess() == 9)
  {
    $file = strip_tags(Util::query('file', 'url.log'));
    $numlines = intval(Util::query('lines', '10'));
    $cmd = strip_tags(Util::query('cmd', 'view'));
    $tempfile = "temp/$file";

    if (in_array($file, array('url.log', 'log.sql', 'query.log')))
    {
      if ($cmd == "download") cmd_download($tempfile);
      if ($cmd == "empty") file_put_contents($tempfile, "");
      $size = format_bytes(filesize($tempfile));
      $content = cmd_view($tempfile, $numlines);
    }
  }
  else {
    $content = '<p class="msg error">Acceso denegado</p>';
  }

  //---------------------------------------
  // FUNCTIONS

  function cmd_view($file, $numlines) {
    $content = tail($file, $numlines);
    $content = htmlentities($content, null, "UTF-8");
    $lines = explode("\n", $content);
    $lines = format_log($file, $lines);
    return '<p>'.implode("</p>\n<p>", $lines)."</p>\n";
  }

  function format_bytes($bytes) {
    $KiB = 1024;
    $MiB = $KiB*$KiB;
    if ($bytes < $MiB) return round($bytes/$KiB, 2)." KiB";
    else return round($bytes/$MiB, 2)." MiB";
  }

  function cmd_download($file) {
    $quoted = sprintf('"%s"', addcslashes(basename($file), '"\\'));
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.$quoted);
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: '.filesize($file));
    echo file_get_contents($file);
    exit;
  }

  //---------------------------------------
  // FORMAT

  function format_log($file, $lines) {
    switch(basename($file)) {
      case "url.log":
        for($i=0; $i<count($lines); $i++) {
          $lines[$i] = format_url_log($lines[$i]);
        }
        break;
      case "log.sql":
        for($i=0; $i<count($lines); $i++) {
          $lines[$i] = format_log_sql($lines[$i]);
        }
        break;
      case "query.log":
        for($i=0; $i<count($lines); $i++) {
          $lines[$i] = format_query_log($lines[$i]);
        }
        break;
    }
    return $lines;
  }
  function format_url_log($line) {
    $items = explode('|', $line);
    foreach($items as &$item) {
      $item = format_query_log($item);
    }
    return implode('|', $items);

    // $before = str_before($line, 'gps.php');
    // $after = str_after($line, 'gps.php');
    // return "<span>$before</span>".format_query_log($after);
  }
  function format_log_sql($line) {
    //$array = explode('--', $line);
    //return implode('<span class="comment">--',$array).'</span>';
    $before = str_before($line, '--');
    $after = str_after($line, '--');
    return "$before<span>$after</span>";
  }
  function format_query_log($line) {
    $array = explode('&amp;', $line);
    for ($i=0; $i<count($array); $i++) {
      $param = $array[$i];
      $before = str_before($param, '=');
      $after = str_after($param, '=');
      $array[$i] = "<b>$before</b>$after";
    }
    return implode('<i>&amp;</i>',$array);
  }

  function str_before($haystack, $needle) {
    //$before = strstr($haystack, $needle, true); // Desde PHP 5.3.0
    $pos = strpos($haystack, $needle);
    if ($pos === false) return $haystack;
    else return substr($haystack, 0, $pos);
  }
  function str_after($haystack, $needle) {
    $after = strstr($haystack, $needle);
    if ($after === false) return '';
    else return $after;
  }

  //---------------------------------------
  // TAIL
  // What is the best way in PHP to read last lines from a file?
  // https://stackoverflow.com/questions/15025875/what-is-the-best-way-in-php-to-read-last-lines-from-a-file

  function tail($file, $numlines) {
    ////return tailLinux($file, $numlines);
    return tailCustom($file, $numlines);
  }

  /*
    it does not run if tail is not available, as on non-Unix (Windows)
    or on restricted environments that does not allow system functions.
  */
  function tailLinux($file, $numlines) {
    // Method 1: Linux command
    $file = escapeshellarg($file);
    $numlines = escapeshellarg($numlines);
    $content = `tail -n $numlines $file`; // Linux command
    return $content;
  }

  /**
	 * Slightly modified version of http://www.geekality.net/2011/05/28/php-tail-tackling-large-files/
	 * @author Torleif Berger, Lorenzo Stanco
	 * @link http://stackoverflow.com/a/15025877/995958
	 * @license http://creativecommons.org/licenses/by/3.0/
	 */
	function tailCustom($filepath, $lines = 1, $adaptive = true) {
		// Open file
		$f = @fopen($filepath, "rb");
		if ($f === false) return false;
		// Sets buffer size, according to the number of lines to retrieve.
		// This gives a performance boost when reading a few lines from the file.
		if (!$adaptive) $buffer = 4096;
		else $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
		// Jump to last character
		fseek($f, -1, SEEK_END);
		// Read it and adjust line number if necessary
		// (Otherwise the result would be wrong if file doesn't end with a blank line)
		if (fread($f, 1) != "\n") $lines -= 1;

		// Start reading
		$output = '';
		$chunk = '';
		// While we would like more
		while (ftell($f) > 0 && $lines >= 0) {
			// Figure out how far back we should jump
			$seek = min(ftell($f), $buffer);
			// Do the jump (backwards, relative to where we are)
			fseek($f, -$seek, SEEK_CUR);
			// Read a chunk and prepend it to our output
			$output = ($chunk = fread($f, $seek)) . $output;
			// Jump back to where we started reading
			fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
			// Decrease our line counter
			$lines -= substr_count($chunk, "\n");
		}
		// While we have too many lines
		// (Because of buffer size we might have read too many)
		while ($lines++ < 0) {
			// Find first newline and remove all text before that
			$output = substr($output, strpos($output, "\n") + 1);
		}
		// Close file and return
		fclose($f);
		return trim($output);
	}

  //---------------------------------------
  // TEMPLATE

  require_once('config/template.php');
  $template = new Template('Archivos de LOG');
  $template->logo('ext_log');
  $template->begin();

?>
<div id="main" class="xbox">

  <div id="search" class="header">
    <form>
      <div id="size_container">
        <span id="size"><?php echo $size ?></span>
        <button name="cmd" value="empty" id="cmd_empty">Vaciar</button>
      </div>
      <div>
        <label for="file">Archivo:</label>
        <select id="file" name="file">
          <option>url.log</option>
          <option>log.sql</option>
          <option>query.log</option>
        </select>
        <label for="lines">Nº líneas:</label>
        <select id="lines" name="lines">
          <option>10</option>
          <option>25</option>
          <option>50</option>
          <option>100</option>
          <option>500</option>
          <option>1000</option>
        </select>
        <button name="cmd" value="view" id="cmd_view">Ver</button>
        <button name="cmd" value="download" id="cmd_download">Descargar</button>
      </div>
    </form>
  </div>

<div id="result"><?php echo $content; ?></div>

</div>

<?php $template->end(); ?>
