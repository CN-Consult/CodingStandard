<?php

/**
 *
 * @file
 * @version 0.1
 * @copyright 2012 CN-Consult GmbH
 * @author Daniel Haas <daniel.haas@cn-consult.eu>
 */

use PHP_CodeSniffer\Files\File;

/**
 * Checks if file comments are as required.
 */
class FileCommentSniff extends \PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FileCommentSniff
{

	protected $tags = array(
		'@file' => array(
					'required'=>true,
					'allow_multiple'=>false),
		'@version' => array(
					'required'=>true,
					'allow_multiple'=>false,
					'order_text'=>'follows @file'),
		'@copyright' => array(
					'required'=>true,
					'allow_multiple'=>true,
					'order_text'=>'follows @version'),
		'@author' => array(
					'required'=>true,
					'allow_multiple'=>true,
					'order_text'=>'follows @copyright'),

	);


	/**
	 * Processes this test, when one of its tokens is encountered.
	 * This was overriden from the base class to remove the PHP Version warning.
	 *
	 * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
	 * @param int                         $stackPtr  The position of the current token
	 *                                               in the stack passed in $tokens.
	 *
	 * @return int
	 */
	public function process(File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();

		// Find the next non whitespace token.
		$commentStart = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);

		// Allow declare() statements at the top of the file.
		if ($tokens[$commentStart]['code'] === T_DECLARE) {
			$semicolon    = $phpcsFile->findNext(T_SEMICOLON, ($commentStart + 1));
			$commentStart = $phpcsFile->findNext(T_WHITESPACE, ($semicolon + 1), null, true);
		}

		// Ignore vim header.
		if ($tokens[$commentStart]['code'] === T_COMMENT) {
			if (strstr($tokens[$commentStart]['content'], 'vim:') !== false) {
				$commentStart = $phpcsFile->findNext(
					T_WHITESPACE,
					($commentStart + 1),
					null,
					true
				);
			}
		}

		$errorToken = ($stackPtr + 1);
		if (isset($tokens[$errorToken]) === false) {
			$errorToken--;
		}

		if ($tokens[$commentStart]['code'] === T_CLOSE_TAG) {
			// We are only interested if this is the first open tag.
			return ($phpcsFile->numTokens + 1);
		} else if ($tokens[$commentStart]['code'] === T_COMMENT) {
			$error = 'You must use "/**" style comments for a file comment';
			$phpcsFile->addError($error, $errorToken, 'WrongStyle');
			$phpcsFile->recordMetric($stackPtr, 'File has doc comment', 'yes');
			return ($phpcsFile->numTokens + 1);
		} else if ($commentStart === false
			|| $tokens[$commentStart]['code'] !== T_DOC_COMMENT_OPEN_TAG
		) {
			$phpcsFile->addError('Missing file doc comment', $errorToken, 'Missing');
			$phpcsFile->recordMetric($stackPtr, 'File has doc comment', 'no');
			return ($phpcsFile->numTokens + 1);
		}

		$commentEnd = $tokens[$commentStart]['comment_closer'];

		$nextToken = $phpcsFile->findNext(
			T_WHITESPACE,
			($commentEnd + 1),
			null,
			true
		);

		$ignore = [
			T_CLASS,
			T_INTERFACE,
			T_TRAIT,
			T_FUNCTION,
			T_CLOSURE,
			T_PUBLIC,
			T_PRIVATE,
			T_PROTECTED,
			T_FINAL,
			T_STATIC,
			T_ABSTRACT,
			T_CONST,
			T_PROPERTY,
		];

		if (in_array($tokens[$nextToken]['code'], $ignore, true) === true) {
			$phpcsFile->addError('Missing file doc comment', $stackPtr, 'Missing');
			$phpcsFile->recordMetric($stackPtr, 'File has doc comment', 'no');
			return ($phpcsFile->numTokens + 1);
		}

		$phpcsFile->recordMetric($stackPtr, 'File has doc comment', 'yes');


		// Check each tag.
		$this->processTags($phpcsFile, $stackPtr, $commentStart);

		// Ignore the rest of the file.
		return ($phpcsFile->numTokens + 1);

	}//end process()





	/**
	 * Processes each required or optional tag.
	 * Overriden from base-class to fix class/file comment detection and ignore empty file tags.
	 *
	 * @param \PHP_CodeSniffer\Files\File $phpcsFile    The file being scanned.
	 * @param int                         $stackPtr     The position of the current token
	 *                                                  in the stack passed in $tokens.
	 * @param int                         $commentStart Position in the stack where the comment started.
	 *
	 * @return void
	 */
	protected function processTags($phpcsFile, $stackPtr, $commentStart)
	{
		$tokens = $phpcsFile->getTokens();

		$docBlock = "file";

		$commentEnd = $tokens[$commentStart]['comment_closer'];

		$foundTags = [];
		$tagTokens = [];
		foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
			$name = $tokens[$tag]['content'];
			if (isset($this->tags[$name]) === false) {
				continue;
			}

			if ($this->tags[$name]['allow_multiple'] === false && isset($tagTokens[$name]) === true) {
				$error = 'Only one %s tag is allowed in a %s comment';
				$data  = [
					$name,
					$docBlock,
				];
				$phpcsFile->addError($error, $tag, 'Duplicate'.ucfirst(substr($name, 1)).'Tag', $data);
			}

			$foundTags[]        = $name;
			$tagTokens[$name][] = $tag;

			$string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
			if ($name!="@file" && ($string === false || $tokens[$string]['line'] !== $tokens[$tag]['line'])) {
				$error = 'Content missing for %s tag in %s comment';
				$data  = [
					$name,
					$docBlock,
				];
				$phpcsFile->addError($error, $tag, 'Empty'.ucfirst(substr($name, 1)).'Tag', $data);
				continue;
			}
		}//end foreach

		// Check if the tags are in the correct position.
		$pos = 0;
		foreach ($this->tags as $tag => $tagData) {
			if (isset($tagTokens[$tag]) === false) {
				if ($tagData['required'] === true) {
					$error = 'Missing %s tag in %s comment';
					$data  = [
						$tag,
						$docBlock,
					];
					$phpcsFile->addError($error, $commentEnd, 'Missing'.ucfirst(substr($tag, 1)).'Tag', $data);
				}

				continue;
			} else {
				$method = 'process'.substr($tag, 1);
				if (method_exists($this, $method) === true) {
					// Process each tag if a method is defined.
					call_user_func([$this, $method], $phpcsFile, $tagTokens[$tag]);
				}
			}

			if (isset($foundTags[$pos]) === false) {
				break;
			}

			if ($foundTags[$pos] !== $tag) {
				$error = 'The tag in position %s should be the %s tag';
				$data  = [
					($pos + 1),
					$tag,
				];
				$phpcsFile->addError($error, $tokens[$commentStart]['comment_tags'][$pos], ucfirst(substr($tag, 1)).'TagOrder', $data);
			}

			// Account for multiple tags.
			$pos++;
			while (isset($foundTags[$pos]) === true && $foundTags[$pos] === $tag) {
				$pos++;
			}
		}//end foreach

	}//end processTags()


	/**
	 * Process the author tag(s) that this header comment has.
	 *
	 * This function is different from other _process functions
	 * as $authors is an array of SingleElements, so we work out
	 * the errorPos for each element separately
	 *
	 * @param int $commentStart The position in the stack where
	 *                          the comment started.
	 *
	 * @return void
	 */
	protected function processAuthors($commentStart)
	{
		$authors = $this->commentParser->getAuthors();
		// Report missing return.
		if (empty($authors) === false)
		{
			foreach ($authors as $author)
			{
				$errorPos = ($commentStart + $author->getLine());
				$content  = $author->getContent();
				if ($content !== '')
				{
					$local = '\da-zA-Z-_+';
					// Dot character cannot be the first or last character
					// in the local-part.
					$localMiddle = $local.'.\w';
					if (preg_match('/^([^<]*)\s+<(['.$local.']['.$localMiddle.']*['.$local.']@[\da-zA-Z][-.\w]*[\da-zA-Z]\.[a-zA-Z]{2,7})>$/', $content) === 0)
					{
	                    $error = 'Content of the @author tag must be in the form "Display Name <username@example.com>"';
	                    $this->currentFile->addError($error, $errorPos, 'InvalidAuthors');
	                }
	            }
				else
				{
	                $error    = 'Content missing for @author tag in %s comment';
					$docBlock = (get_class($this) === 'PEAR_Sniffs_Commenting_FileCommentSniff') ? 'file' : 'class';
	                $data     = array($docBlock);
	                $this->currentFile->addError($error, $errorPos, 'EmptyAuthors', $data);
				}
			}
		}

	}//end processAuthors()


	/**
	 * Process the copyright tags.
	 *
	 * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
	 * @param array                       $tags      The tokens for these tags.
	 *
	 * @return void
	 */
	protected function processCopyright($phpcsFile, array $tags)
	{
		$tokens = $phpcsFile->getTokens();
		foreach ($tags as $tag) {
			if ($tokens[($tag + 2)]['code'] !== T_DOC_COMMENT_STRING) {
				// No content.
				continue;
			}

			$content = $tokens[($tag + 2)]['content'];

			if (strpos($content,"CN-Consult GmbH")===false)
			{ //no cn-consult copyright
				$phpcsFile->addError("The copyright must contain 'CN-Consult GmbH'", $tag, 'CopyrightCNConsult');
			}


			$matches = [];
			if (preg_match('/^([0-9]{4})((.{1})([0-9]{4}))? (.+)$/', $content, $matches) !== 0) {
				// Check earliest-latest year order.
				if ($matches[3] !== '' && $matches[3] !== null) {
					if ($matches[3] !== '-') {
						$error = 'A hyphen must be used between the earliest and latest year';
						$phpcsFile->addError($error, $tag, 'CopyrightHyphen');
					}

					if ($matches[4] !== '' && $matches[4] !== null && $matches[4] < $matches[1]) {
						$error = "Invalid year span \"$matches[1]$matches[3]$matches[4]\" found; consider \"$matches[4]-$matches[1]\" instead";
						$phpcsFile->addWarning($error, $tag, 'InvalidCopyright');
					}
				}
			} else {
				$error = '@copyright tag must contain a year and the name of the copyright holder';
				$phpcsFile->addError($error, $tag, 'IncompleteCopyright');
			}
		}//end foreach


		/*

		$copyrights = $this->commentParser->getCopyrights();
		foreach ($copyrights as $copyright)
		{
			$errorPos = ($commentStart + $copyright->getLine());
			$content  = $copyright->getContent();
			if (strpos($content,"CN-Consult GmbH")===false)
			{ //no cn-consult copyright
				$this->currentFile->addError("The copyright must contain 'CN-Consult GmbH'", $errorPos, 'CopyrightCNConsult');
			}
			if ($content !== '')
			{
				$matches = array();
				if (preg_match('/^([0-9]{4})((.{1})([0-9]{4}))? (.+)$/', $content, $matches) !== 0)
				{
					// Check earliest-latest year order.
					if ($matches[3] !== '')
					{
						if ($matches[3] !== '-')
						{
							$error = 'A hyphen must be used between the earliest and latest year';
							$this->currentFile->addError($error, $errorPos, 'CopyrightHyphen');
						}

						if ($matches[4] !== '' && $matches[4] < $matches[1])
						{
							$error = "Invalid year span \"$matches[1]$matches[3]$matches[4]\" found; consider \"$matches[4]-$matches[1]\" instead";
							$this->currentFile->addWarning($error, $errorPos, 'InvalidCopyright');
						}
					}
				}
				else
				{
					$error = '@copyright tag must contain a year and the name of the copyright holder';
					$this->currentFile->addError($error, $errorPos, 'EmptyCopyright');
				}
			}
			else
			{
				$error = '@copyright tag must contain a year and the name of the copyright holder';
				$this->currentFile->addError($error, $errorPos, 'EmptyCopyright');
			}//end if
		}//end if
		*/

	}//end processCopyright()


	/**
	 * Process the version tag.
	 *
	 * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
	 * @param array                       $tags      The tokens for these tags.
	 *
	 * @return void
	 */
	protected function processVersion($phpcsFile, array $tags)
	{
		$tokens = $phpcsFile->getTokens();
		foreach ($tags as $tag) {
			if ($tokens[($tag + 2)]['code'] !== T_DOC_COMMENT_STRING) {
				$error = 'Content missing for @version tag in file comment';
				$phpcsFile->addError($error, $tag, 'EmptyVersion');
			} else {
				$content = $tokens[($tag + 2)]['content'];
				if (strstr($content, '.') === false) {
					$error = 'Invalid version "%s" in file comment; consider "0.1" or "0.1.1"  or "2.1" etc. instead';
					$data = array($content);
					$phpcsFile->addWarning($error, $tag, 'InvalidVersion', $data);
				}
			}
		}



	}//end processVersion()


	protected function processFile($phpcsFile, array $tags)
	{


	}

}