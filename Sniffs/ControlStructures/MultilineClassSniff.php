<?php

/**
 *
 * @file
 * @version 0.1
 * @copyright 2012 CN-Consult GmbH
 * @author Daniel Haas <daniel.haas@cn-consult.eu>
 */

use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks curly brackets after class.
 */
class CNConsult_Sniffs_ControlStructures_MultilineClassSniff implements Sniff
{

	/**
	 * Returns the token types that this sniff is interested in.
	 *
	 * @return array(int)
	 */
	public function register()
	{
		return array(T_CLASS);
	}


	/**
	 * Processes the tokens that this sniff is interested in.
	 *
	 * @param \PHP_CodeSniffer\Files\File $phpcsFile The file where the token was found.
	 * @param int                  $stackPtr  The position in the stack where
	 *                                        the token was found.
	 *
	 * @return void
	 */
	public function process(\PHP_CodeSniffer\Files\File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();


		//check if there is no opening brace on the same line
		$currentLine=$tokens[$stackPtr]['line'];
		$inStack=$stackPtr+1;
		while($currentLine==$tokens[$inStack]['line'])
		{
			if ($tokens[$inStack]['code']==T_OPEN_CURLY_BRACKET)
			{ //
				$phpcsFile->addError('Curly bracket of class should be on next line after class.',$inStack,"CurlyBracketOpenOnNextLine");
				break;
			}
			$inStack++;
		}

	}//end process()


}

?>