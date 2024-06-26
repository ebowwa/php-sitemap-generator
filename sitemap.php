<?php

/***************************\
** sitemap.php**
\***************************/

error_reporting(E_ALL);

//Read global variables from config file
require_once('sitemap.config.php');

// Include all functions
require_once('sitemap.functions.php');

//Default html header makes browsers ignore \n
header("Content-Type: text/plain");

$color = false;

$version_script = 2;

if ($version_script != $version_functions || $version_functions != $version_config) {
    logger("Script versions mismatch!", 3);
    logger("Update necessary", 3);
    logger("Version of sitemap.functions.php " . $version_functions, 3);
    logger("Version of sitemap.config.php " . $version_config, 3);
    logger("Version of sitemap.php " . $version_script, 3);
    logger("Download new files here: https://www.github.com/knyzorg/sitemap-generator-crawler", 3);
    die("Stopped.");
}

// Add PHP CLI support
if (php_sapi_name() === 'cli' && PHP_OS != 'WINNT') {
    parse_str(implode('&', array_slice($argv, 1)), $args);
    $color = true;
}

//Allow variable overloading with CLI
if (isset($args['file'])) {
    $file = $args['file'];
} else {
    $file = 'sitemap.xml';
}
if (isset($args['site'])) {
    $site = $args['site'];
}
if (isset($args['max_depth'])) {
    $max_depth = $args['max_depth'];
}
if (isset($args['enable_frequency'])) {
    $enable_frequency = $args['enable_frequency'];
}
if (isset($args['enable_priority'])) {
    $enable_priority = $args['enable_priority'];
}
if (isset($args['enable_modified'])) {
    $enable_modified = $args['enable_modified'];
}
if (isset($args['freq'])) {
    $freq = $args['freq'];
}
if (isset($args['priority'])) {
    $priority = $args['priority'];
}
if (isset($args['blacklist'])) {
    $blacklist = $args['blacklist'];
}
if (isset($args['debug'])) {
    $debug = $args['debug'];
}
if (isset($args['ignore_arguments'])) {
    $ignore_arguments = !!$args['ignore_arguments'];
}
if (isset($args['pdf_index'])) {
    $pdf_index = $args['pdf_index'];
}

//Begin stopwatch for statistics
$start = microtime(true);

//Setup file stream
$tempfile = tempnam(sys_get_temp_dir(), '.xml.');
$file_stream = fopen($tempfile, "w") or die("Error: Could not create temporary file $tempfile" . "\n");

fwrite($file_stream, $xmlheader);

// Global variable, non-user defined
$depth = 0;
$indexed = 0;
$scanned = array();
$deferredLinks = array();

// Reduce domain to root in case of monkey
$real_site = domain_root($site);

if ($real_site != $site) {
    logger("Reformatted site from $site to $real_site", 2);
}

// Begin by crawling the original url
scan_url($real_site);

// Finalize sitemap
fwrite($file_stream, "</urlset>\n");
fclose($file_stream);

// Pretty-print sitemap
if ((PHP_OS == 'WINNT') ? `where xmllint` : `which xmllint`) {
    logger("Found xmllint, pretty-printing sitemap", 0);
    $responsevalue = exec('xmllint --format ' . $tempfile . ' -o ' . $tempfile . ' 2>&1', $discardedoutputvalue, $returnvalue);
    if ($returnvalue) {
        die("Error: " . $responsevalue . "\n");
    }
}

// Generate and print out statistics
$time_elapsed_secs = round(microtime(true) - $start, 2);
logger("Sitemap has been generated in " . $time_elapsed_secs . " second" . (($time_elapsed_secs >= 1 ? 's' : '') . " and saved to $file"), 0);
$size = sizeof($scanned);
logger("Scanned a total of $size pages and indexed $indexed pages.", 0);

// Write the sitemap file directly to the final destination
if (file_put_contents($file, file_get_contents($tempfile)) === false) {
    logger("Error: Could not write sitemap file to $file", 3);
    unlink($tempfile);
    die("Operation failed.");
}

// Apply permissions
chmod($file, $permissions);

// Delete the temporary file
unlink($tempfile);

// Declare that the script has finished executing and exit
logger("Operation Completed", 0);