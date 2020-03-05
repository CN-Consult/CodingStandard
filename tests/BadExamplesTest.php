<?php
/**
 *
 * @file
 * @version 1.0
 * @copyright 2020 CN-Consult GmbH
 * @author Daniel Haas <daniel.haas@cn-consult.eu>
 */

use PHPUnit\Framework\TestCase;

require_once "PhpCsRunner.php";
/**
 * Tests that the examples in examples/bad output the expected issues.
 */
class BadExamplesTest extends TestCase
{
	private $phpCsRunner;


	private function runPHPCS($_filename)
	{
		if (!$this->phpCsRunner) $this->phpCsRunner = new \tests\PhpCsRunner();
		return $this->phpCsRunner->getOutputForFile($_filename);
	}


	/**
	 * Tests bad example files.
	 *
	 * @param $_filename string The filename that should be tested.
	 * @param $_expectedOutput string The expected xml output this bad file should generate.
	 * @dataProvider badExampleProvider
	 */
	function testBadExample($_filename,$_expectedOutput)
	{
		$output=$this->runPHPCS($_filename);
		//we use assertEquals instead of assert-Empty to make it easier to view the difference in case it fails
		$this->assertEquals($_expectedOutput,$output,"Found issues in bad file '$_filename'!");
	}

	public function badExampleProvider()
	{
		$data=[];
		$badFiles=glob(__DIR__."/examples/bad/*.php");
		foreach ($badFiles as $badFile)
		{
			$expectedOutputFilename=str_replace(".php",".expected.xml",$badFile);
			$relativeExpectedFilename=str_replace(__DIR__."/","",$expectedOutputFilename);
			$relativeBadFilename=str_replace(__DIR__."/","",$badFile);

			if (is_readable($expectedOutputFilename))
			{
				$currentFile=[$relativeBadFilename, file_get_contents($expectedOutputFilename)];
			}
			else
			{
				$currentFile=[$badFile,"Could not find expected output-file '$relativeExpectedFilename'!\nPlease create it with\n  ./generate-expected-bad-output.php $relativeBadFilename"];
			}
			$data[]=$currentFile;
		}
		var_dump($data);
		return $data;
	}
}





