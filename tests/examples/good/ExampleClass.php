<?php
/**
 *
 * @file
 * @version 1.0
 * @copyright 2020 CN-Consult GmbH
 * @author Daniel Haas <daniel.haas@cn-consult.eu>
 */


namespace Examples\Good;


/**
 * This is an example class that shows how code should look like in our CodingStandard.
 * @see Documenation of our code-style
 */
class ExampleClass
{
	private $blub;

	/**
	 * Constructs a new ExampleClass.
	 */
	function __construct()
	{
		echo "Example class was constructed!\n";

		//both of these are allowed, but the latter one is preferred
		$a=1+2;
		$b = 1 + 2;

	}

	/**
	 * Shows how we write foreach loops.
	 */
	private function foreachLoopExample()
	{
		$names = ["Bill Gates","Steve Jobs","Jeff Bezos"];
		foreach ($names as $index => $name)
		{
			echo $name;
		}
	}

	/**
	 * Shows how we write for-loops.
	 */
	private function forLoopExample()
	{
		$i = 0;
		for ($i=0; $i<10; $i++) echo "This is like in-line for loops should look like!";

		for ($i=0; $i<10; $i++)
		{
			echo "This is like multi-line for loops should look like!";
		}
	}

	/**
	 * Shows how we write ifs.
	 */
	private function ifExample()
	{
		$a=10;
		if ($a > 0)
		{
			echo "$a is bigger than 0";
		}

		if ($a <0 ) echo "$a is smaller than 0";
	}

	/**
	 * A method that does something with parameters.
	 * We allow both orders of parameter type documentation but prefer the first one.
	 *
	 * @param int $a int The number of echos
	 * @param $b string What should be output
	 */
	final public function withParameters($a, $b)
	{

	}

	/**
	 * This is how we write code when we call other functions.
	 */
	function functionCall()
	{
		//we allow spaces between parameters
		$this->withParameters(1, "string");
		//and we allow no spaces between parameters
		$this->withParameters(1,"string");
	}
}
