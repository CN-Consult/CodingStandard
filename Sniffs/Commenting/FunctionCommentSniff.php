<?php
/**
 *
 * @file
 * @version 2.11
 * @copyright 2020 CN-Consult GmbH
 * @author Daniel Haas <daniel.haas@cn-consult.eu>
 */


/**
 * Overridden function comment sniff that does not require @return tags for all method comments.
 */
class FunctionComment extends \PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FunctionCommentSniff
{
	/**
	 * Process the return comment of this function comment.
	 *
	 * @param \PHP_CodeSniffer\Files\File $phpcsFile    The file being scanned.
	 * @param int                         $stackPtr     The position of the current token
	 *                                                  in the stack passed in $tokens.
	 * @param int                         $commentStart The position in the stack where the comment started.
	 *
	 * @return void
	 */
	protected function processReturn(\PHP_CodeSniffer\Files\File $phpcsFile, $stackPtr, $commentStart)
	{
		$tokens = $phpcsFile->getTokens();

		// Skip constructor and destructor.
		$methodName      = $phpcsFile->getDeclarationName($stackPtr);
		$isSpecialMethod = ($methodName === '__construct' || $methodName === '__destruct');

		$return = null;
		foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
			if ($tokens[$tag]['content'] === '@return') {
				if ($return !== null) {
					$error = 'Only 1 @return tag is allowed in a function comment';
					$phpcsFile->addError($error, $tag, 'DuplicateReturn');
					return;
				}

				$return = $tag;
			}
		}

		if ($isSpecialMethod === true) {
			return;
		}



	}//end processReturn()
}
