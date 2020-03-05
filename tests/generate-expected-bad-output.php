#!/usr/bin/php
<?php
/**
 * This script generates the expected output for a given examples/bad/*.php file.
 * Just call it with the path to the bad-file and it will output the expected output that should be pasted into the .expected file.
 * This is then used inside the unit-tests to compare the expected output with the current output.
 *
 * @file
 * @version 1.0
 * @copyright 2020 CN-Consult GmbH
 * @author Daniel Haas <daniel.haas@cn-consult.eu>
 */


if ($argc==1) echo "Call me with the path to an examples/bad/.php-file to generate comparable output for this file.\n";
else
{
	require_once("PhpCsRunner.php");
	$phpCsRunner = new \tests\PhpCsRunner();
	$filename=$argv[1];
	if (is_readable($argv[1]))
	{
	    echo "This is the sourcecode of the bad file $filename that is being checked:\n-----------\n";
	    echo file_get_contents($filename);
	    echo "\n-----------\n";
	    echo "These are the errors that phpcs found in this file:\n";
	    $phpCsRunner->checkFile($filename);
	    echo "\n";
	    echo "If this is what you expected, please add the following output to the file ".str_replace(".php",".expected.xml",$filename)."\n\n";
		echo $phpCsRunner->getOutputForFile($filename);
		echo "\n";

	}
	else echo "Could not find file '$filename'!\nPlease specify relative path to this directory and check if the file exists!\n";

}