<?php
/**
 *
 * @file
 * @version 2.11
 * @copyright 2020 CN-Consult GmbH
 * @author Daniel Haas <daniel.haas@cn-consult.eu>
 */


namespace tests;


/**
 * A helper class that is able to run php-cs and generates string-comparable output
 * that does not depend on the php-codesniffer version or the paths to files
 */
class PhpCsRunner
{
	/**
	 * Returns the xml output of phpcs but modifies it to discard the phpcs version number
	 * and makes the filename path relative to make the unit-tests more robust.
	 *
	 * @param $_filename
	 * @return mixed|string
	 */
	function getOutputForFile($_filename)
	{
		$output=shell_exec("../vendor/bin/phpcs --standard=../ --report=xml $_filename");
		$outputXml=simplexml_load_string($output);
		if ($outputXml!==false)
		{
			//fix version to a fixed string
			$outputXml["version"]="does not matter";
			//use the our relative filename so full paths in unit-test environments don't break tests
			$outputXml->file["name"]=$_filename;

			//we want the output to be formatted correctly, so we push this through DOMDocument to get nice formatting
			$dom = new \DOMDocument("1.0");
			$dom->preserveWhiteSpace = false;
			$dom->formatOutput = true;
			$dom->loadXML($outputXml->asXML());
			return str_replace("  ","    ",$dom->saveXML());

		}
		else return "XML output of phpcs was not parseable! Output was:\n$output";
	}

	/**
	 * Outputs the results of the given filename.
	 * @param $_filename string The filename that should be checked.
	 */
	function checkFile($_filename)
	{
		system("../vendor/bin/phpcs --standard=../ --report=full $_filename");
	}
}
