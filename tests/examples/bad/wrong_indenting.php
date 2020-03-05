<?php
/**
 *
 * @file
 * @version 2.11
 * @copyright 2020 CN-Consult GmbH
 * @author Daniel Haas <daniel.haas@cn-consult.eu>
 */

/**
 * Testfunction
 */
function testIndent()
{
    echo "this line is indented with space instead of tab!";
  $a = 1;
    if ($a)
    {
      echo "this is not indented enough!";
    }
}