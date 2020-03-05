<?php
/**
 *
 * @file
 * @version 1.0
 * @copyright 2020 CN-Consult GmbH
 * @author Daniel Haas <daniel.haas@cn-consult.eu>
 */

use PHPUnit\Framework\TestCase;


/**
 * Tests that the examples in examples/good don't issue any errors
 */
class GoodExamplesTest extends TestCase
{

	private function runPHPCS($_filename)
	{
		$output=shell_exec("../vendor/bin/phpcs --standard=../ --report=full $_filename");
		return $output;
	}


	/**
	 * @param $_filename string The filename that should be tested
	 * @dataProvider goodExampleProvider
	 */
	function testGoodExample($_filename)
	{
		$output=$this->runPHPCS($_filename);
		//we use assertEquals instead of assert-Empty to make it easier to view the difference in case it fails
		$this->assertEquals("",$output,"Found issues in good file '$_filename'!");
	}

	public function goodExampleProvider()
	{
		$data=[];
		$goodFiles=glob(__DIR__."/examples/good/*.php");
		foreach ($goodFiles as $goodFile)
		{
			$data[]=[$goodFile];
		}
		return $data;
	}
}





