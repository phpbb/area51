<?php
if($argc != 7)
{
	die('Usage: ' . $argv[0] . ' <output dir> <data dir> <from version> <to version> <side-by-side | inline> <git diff --name-status results file>');
}

$base_dir = dirname(__FILE__);
$out_dir = $argv[1];		//'/path/to/web/code-changes/'
$data_dir = $argv[2];		//'/path/to/data/code-changes/'
$from_version = $argv[3];	//'3.0.0';
$to_version = $argv[4];		//'3.0.10';
$diff_mode = $argv[5];		//'side-by-side';
$file = $argv[6];			//'file.txt'

define('IN_PHPBB', true);
include($base_dir . '/includes/diff.php');
include($base_dir . '/includes/engine.php');
include($base_dir . '/includes/renderer.php');

class user
{
	var $lang = array(
		'LINE'					=> 'Line',
		'LINE_ADDED'			=> 'Added',
		'LINE_MODIFIED'			=> 'Modified',
		'LINE_REMOVED'			=> 'Removed',
		'LINE_UNMODIFIED'		=> 'Unmodified',
		'NO_VISIBLE_CHANGES'	=> 'No visible changes',
	);
}
$user = new user();

/**
* Compares two version of the given file path and generates a diff output file
*
* @param $path	string	The file path
*
* @return null
*/
function generate_diff_file($path)
{
	global $data_dir, $out_dir, $diff_mode, $from_version, $to_version, $user;

	if(file_exists($data_dir . '/versions/' . $from_version . '/' . $path))
	{
		$file1 = file_get_contents($data_dir . '/versions/' . $from_version . '/' . $path);
	}
	else
	{
		$file1 = '';
	}
	
	if(file_exists($data_dir . '/versions/' . $to_version . '/' . $path))
	{
		$file2 = file_get_contents($data_dir . '/versions/' . $to_version . '/' . $path);
	}
	else
	{
		$file2 = '';
	}

	$diff = new diff($file1, $file2);

	if($diff_mode == 'side-by-side')
	{
		$renderer = new diff_renderer_side_by_side();;
	}
	else if($diff_mode == 'inline')
	{
		$renderer = new diff_renderer_inline();
	}

	$output = $renderer->get_diff_content($diff);
	
	$header = file_get_contents($data_dir . '/template/overall_header.html');
	$footer = file_get_contents($data_dir . '/template/overall_footer.html');

	//Set up the navigation menu
	if (strpos($from_version, '3.0') === 0)
	{
		$nav = file_get_contents($data_dir . '/template/30_nav.html');
		$tabs = '<li id="activetab"><a href="/code-changes/3.0.0/"><span>3.0.x</span></a></li>
		<li><a href="/code-changes/3.1.0/"><span>3.1.x</span></a></li>
		<li><a href="/code-changes/3.2.0/"><span>3.2.x</span></a></li>';
	}
	else if (strpos($from_version, '3.1') === 0)
	{
		$nav = file_get_contents($data_dir . '/template/31_nav.html');
		$tabs = '<li><a href="/code-changes/3.0.0/"><span>3.0.x</span></a></li>
		<li id="activetab"><a href="/code-changes/3.1.0/"><span>3.1.x</span></a></li>
		<li><a href="/code-changes/3.2.0/"><span>3.2.x</span></a></li>';
	}
	else if (strpos($from_version, '3.2') === 0)
	{
		$nav = file_get_contents($data_dir . '/template/32_nav.html');
		$tabs = '<li><a href="/code-changes/3.0.0/"><span>3.0.x</span></a></li>
		<li><a href="/code-changes/3.1.0/"><span>3.1.x</span></a></li>
		<li id="activetab"><a href="/code-changes/3.2.0/"><span>3.2.x</span></a></li>';
	}
	else
	{
		exit;
	}
	$header = str_replace('{PREV_VERSIONS_NAV}', $nav, $header);

	//Set the active version in the navigation
	$header = str_replace('<li><a href="/code-changes/' . $from_version . '/">', '<li id="activemenu"><a href="/code-changes/' . $from_version . '/">', $header);
	
	//Set the current file being viewed
	$header = str_replace('{CURRENT_FILE}', '<div style="float: right;"><h2 style="margin-top: 0px;">File: ' . $path . '</h2></div>', $header);

	//Set the active tab
	$header = str_replace('{TABS}', $tabs, $header);
	
	//Build the structure if necessary
	$dir = $out_dir . '/' . $from_version . '/' . $diff_mode . '/' . $to_version;
	if(!is_dir($dir))
	{
		mkdir($dir, 0755, true);
	}

	//Write the file, prefix with phpbb- so that we can go to .htaccess files
	$file_name = 'phpbb-' . str_replace('/', '-', $path);
	$fh = fopen($dir . '/' . $file_name . '.html', 'wb+');
	fwrite($fh, $header);
	fwrite($fh, $output);
	fwrite($fh, $footer);
	fclose($fh);
}

/**
* Given a file, an icon is selected based on the file type and type of changes made to the file (added/modified/removed)
*
* @param $path		string	The file path
* @param $folder	bool	If the file is actually a folder
*
* @return string	The class name to use
*/
function get_icon($path, $folder = false)
{
	global $status_info;

	if($folder)
	{
		return 'folder';
	}
	else
	{
		$status = $status_info[$path];
		return 'file ' . $status;
	}
}

/**
* Determines if a file is a binary or text file
*
* @param $filename	string	The name of the target file
*
* @return boolean
*/
function is_binary($filename)
{
	$binary_extensions = array('gif', 'jpg', 'jpeg', 'png', 'ttf');

	if (strpos($filename, '.') === false)
	{
		return false;
	}

	$extension = explode('.', $filename);
	$extension = array_pop($extension);

	if (in_array($extension, $binary_extensions))
	{
		return true;
	}
	else
	{
		return false;
	}
}

/**
* Builds the index file structure and creates the file diffs
*
* @param &$array	array	The structure containing the file tree
* @param $path		string	The current file being processed
* @param &$output	string	The HTML output for the index file
*
* @return null
*/
function print_structure(&$array, $path, &$output)
{
	global $diff_mode, $from_version, $to_version;

	$temp_path = $path;
	foreach($array as $key => &$folder)
	{
		if(is_array($folder))
		{
			$path .= $key . '/';
			$id = str_replace('/', '-', $path);

			if($id != '-')
			{
				$id = trim($id, '-');
			}
			else
			{
				$id = 'root';
			}

			$class = get_icon($path, true);
			$output .= '<li><a href="?' . $id . '"><span class="' . $class . '">' . $key . '</span></a>' . "\n<ul>";

			print_structure($folder, $path, $output);
			$output .= '</ul></li>';

			$path = $temp_path;
		}
		else
		{
			$id = 'phpbb-' . str_replace('/', '-', $path . $folder);
			$modified = false;
			$class = get_icon($path . $folder, false);
			$output .= '<li><span class="' . $class . '"><a href="' . $diff_mode . '/' . $to_version . '/' . $id . '.html">' . $folder . '</a></span></li>' . "\n";
			
			//Generate the diff view that's being linked to
			generate_diff_file($path . $folder);
		}
	}
}

/**
* Creates a directory structure from arrays
* Folders are array keys, files are stored in an array in the value
*
* @param &$array	array	The array holding the directory structure
* @param $entry		array	An array created from exploding a file path by its separator
* @param $depth		int		The specific file/folder in the path to evaluate and add to the directory structure
*
* @return null
*/
function create_entry(&$array, $entry, $depth = 0)
{
	if(!isset($array[$entry[$depth]]))
	{
		if(strpos($entry[$depth], '.') !== false)
		{
			$array[] = $entry[$depth];
		}
		else
		{
			$array[$entry[$depth]] = array();
		}
	}
	
	if($depth + 1 < sizeof($entry))
	{
		create_entry($array[$entry[$depth]], $entry, ++$depth);
	}
}

/**
* Sorts the directory structure created by calls to create_entry into nicely organized array.
* Files are grouped together and folders are grouped together.
*
* @param &$array	array	The array holding the directory structure
*
* @return null
*/
function sort_array(&$array)
{
	$files = array();
	foreach($array as $key => &$row)
	{
		if(!is_array($row))
		{
			$files[] = $row;
			unset($array[$key]);
		}
		else
		{
			sort_array($row);
		}
	}
	$array = array_merge($array, $files);
}

$ignore_folders = array('develop', 'docs', 'install', 'vendor');
$ignore_files = array('config.php', 'composer.json', 'composer.lock');

$changes = array();
$status_info = array();
$output = '';

$file = file($file, FILE_IGNORE_NEW_LINES);
foreach($file as $line)
{
	//M	phpBB/adm/index.php
	$info = explode("\t", $line);
	$status = $info[0];
	
	$structure = explode('/', $info[1]);
	if($structure[0] != 'phpBB')
	{
		continue;
	}
	
	//Check ignored files and folders, only in the root level
	if(isset($structure[1]) && (in_array($structure[1], $ignore_folders) || in_array($structure[1], $ignore_files)))
	{
		continue;
	}

	//Skip binary files
	if(is_binary($info[1]))
	{
		continue;
	}

	array_shift($structure);
	$status_info[implode('/', $structure)] = $status;

	create_entry($changes, $structure);
}

sort_array($changes);
print_structure($changes, '', $output);

$header = file_get_contents($data_dir . '/template/overall_header.html');

$footer = '</ul>';
$footer .= file_get_contents($data_dir . '/template/overall_footer.html');

//Set the navigation menu
if (strpos($from_version, '3.0') === 0)
{
	$nav = file_get_contents($data_dir . '/template/30_nav.html');
	$tabs = '<li id="activetab"><a href="/code-changes/3.0.0/"><span>3.0.x</span></a></li>
	<li><a href="/code-changes/3.1.0/"><span>3.1.x</span></a></li>
	<li><a href="/code-changes/3.2.0/"><span>3.2.x</span></a></li>';
}
else if (strpos($from_version, '3.1') === 0)
{
	$nav = file_get_contents($data_dir . '/template/31_nav.html');
	$tabs = '<li><a href="/code-changes/3.0.0/"><span>3.0.x</span></a></li>
	<li id="activetab"><a href="/code-changes/3.1.0/"><span>3.1.x</span></a></li>
	<li><a href="/code-changes/3.2.0/"><span>3.2.x</span></a></li>';
}
else if (strpos($from_version, '3.2') === 0)
{
	$nav = file_get_contents($data_dir . '/template/32_nav.html');
	$tabs = '<li><a href="/code-changes/3.0.0/"><span>3.0.x</span></a></li>
	<li><a href="/code-changes/3.1.0/"><span>3.1.x</span></a></li>
	<li id="activetab"><a href="/code-changes/3.2.0/"><span>3.2.x</span></a></li>';
}

$header = str_replace('{PREV_VERSIONS_NAV}', $nav, $header);

//Set up the active version in the navigation
$header = str_replace('<li><a href="/code-changes/' . $from_version . '/">', '<li id="activemenu"><a href="/code-changes/' . $from_version . '/">', $header);

//Remove the file marker
$header = str_replace('{CURRENT_FILE}', '', $header);

//Set the active tab
$header = str_replace('{TABS}', $tabs, $header);

$header .= '<p>The following files have been changed in the update from ' . $from_version . ' to ' . $to_version . ':</p>';
$header .= '<ul id="browser" class="filetree">';

//Write the file
$fh = fopen($out_dir . '/' . $from_version . '/index.html', 'wb+');
fwrite($fh, $header);
fwrite($fh, $output);
fwrite($fh, $footer);
fclose($fh);
?>
