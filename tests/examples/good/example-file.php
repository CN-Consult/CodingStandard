<?php
/**
 *
 * @file
 * @version 1.0
 * @copyright 2020 CN-Consult GmbH
 * @author Daniel Haas <daniel.haas@cn-consult.eu>
 */

/**
 * Just a test function.
 * @param boolean $a What this does
 * @param int $b A number
 * @return bool the result of the operation
 */
function test($a, $b)
{
	return true;
}

test(1,2);

//this is how we want closures to look like
$b=1;
$closure = function ($b) use ($b) {
	echo $b;
};