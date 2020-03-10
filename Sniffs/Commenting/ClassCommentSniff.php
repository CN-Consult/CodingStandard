<?php

/**
 *
 * @file
 * @version 0.1
 * @copyright 2012 CN-Consult GmbH
 * @author Daniel Haas <daniel.haas@cn-consult.eu>
 */

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Parses and verifies the class doc comment.
 *
 * Verifies that :
 * <ul>
 *  <li>A class doc comment exists.</li>
 *  <li>The comment uses the correct docblock style.</li>
 *  <li>There are no blank lines after the class comment.</li>
 *  <li>Only allowed tags are used in the docblock.</li>
 * </ul>
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006-2015 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */
class ClassCommentSniff implements Sniff
{
	/** @var array The tags that should be allowed in doc-block tags. */
	private $allowedTags=["@deprecated","@link","@see","@begincode","@endcode","@ingroup",
		 					"@componentBindings","@createRights","@listRights","@modelName","@duplicateFieldName","@enableModelChecks"];

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register()
	{
		return [
			T_CLASS,
			T_INTERFACE,
			T_TRAIT,
		];

	}//end register()


	/**
	 * @inheritDoc
	 */
	public function process(\PHP_CodeSniffer\Files\File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();
		$find   = Tokens::$methodPrefixes;
		$find[] = T_WHITESPACE;

		$commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);
		if ($tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG
			&& $tokens[$commentEnd]['code'] !== T_COMMENT
		) {
			$class = $phpcsFile->getDeclarationName($stackPtr);
			$phpcsFile->addError('Missing doc comment for class %s', $stackPtr, 'Missing', [$class]);
			$phpcsFile->recordMetric($stackPtr, 'Class has doc comment', 'no');
			return;
		}

		$phpcsFile->recordMetric($stackPtr, 'Class has doc comment', 'yes');

		if ($tokens[$commentEnd]['code'] === T_COMMENT) {
			$phpcsFile->addError('You must use "/**" style comments for a class comment', $stackPtr, 'WrongStyle');
			return;
		}

		if ($tokens[$commentEnd]['line'] !== ($tokens[$stackPtr]['line'] - 1)) {
			$error = 'There must be no blank lines after the class comment';
			$phpcsFile->addError($error, $commentEnd, 'SpacingAfter');
		}

		$commentStart = $tokens[$commentEnd]['comment_opener'];
		foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
			$tagName=$tokens[$tag]['content'];
			if (!in_array($tagName,$this->allowedTags))
			{
				$error = '%s tag is not allowed in class comment';
				$data  = [$tokens[$tag]['content']];
				$phpcsFile->addWarning($error, $tag, 'TagNotAllowed', $data);
			}
		}
	}
}

