<?php
/**
 *
 * @file
 * @version 1.0
 * @copyright 2020 CN-Consult GmbH
 * @author Daniel Haas <daniel.haas@cn-consult.eu>
 */


try
{
	echo "Hello World!";
	$a = 10 / 0;
}
catch (Exception $e)
{
	echo "Caught Exception with message ".$e->getMessage();
}