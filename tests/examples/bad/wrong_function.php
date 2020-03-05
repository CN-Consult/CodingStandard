<?php
/**
 *
 * @file
 * @version 2.11
 * @copyright 2020 CN-Consult GmbH
 * @author Daniel Haas <daniel.haas@cn-consult.eu>
 */

/**
 * Test function
 * @param $a
 */
function test($a,$b) {
	return"1$a$b";
}

$a = test(1,3);
