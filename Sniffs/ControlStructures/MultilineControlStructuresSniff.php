<?php
/**
 * Verifies that control statements conform to our coding standards.
 * @file
 * @version 1.0
 * @copyright 2020 CN-Consult GmbH
 * @author Daniel Haas <daniel.haas@cn-consult.eu>
 */


namespace PHP_CodeSniffer\Standards\Squiz\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class MultilineControlStructuresSniff implements Sniff
{

	/**
	 * How many spaces should precede the colon if using alternative syntax.
	 *
	 * @var integer
	 */
	public $requiredSpacesBeforeColon = 1;

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = [
		'PHP',
		'JS',
	];


	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return int[]
	 */
	public function register()
	{
		return [
			T_TRY,
			T_CATCH,
			T_FINALLY,
			T_DO,
			T_WHILE,
			T_FOR,
			T_IF,
			T_FOREACH,
			T_ELSE,
			T_ELSEIF,
			T_SWITCH,
		];

	}//end register()


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
	 * @param int $stackPtr The position of the current token in the
	 *                                               stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process(File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();
		$controlStructureName=$tokens[$stackPtr]['content'];

		$nextNonEmpty = $phpcsFile->findNext(Tokens::$emptyTokens, ($stackPtr + 1), null, true);
		if ($nextNonEmpty === false) {
			return;
		}

		$isAlternative = false;
		if (isset($tokens[$stackPtr]['scope_opener']) === true
			&& $tokens[$tokens[$stackPtr]['scope_opener']]['code'] === T_COLON
		) {
			$isAlternative = true;
		}

		// Single space after the keyword.
		$expected = 1;
		if (isset($tokens[$stackPtr]['parenthesis_closer']) === false && $isAlternative === true) {
			// Catching cases like:
			// if (condition) : ... else: ... endif
			// where there is no condition.
			$expected = (int)$this->requiredSpacesBeforeColon;
		}

		$found = 1;
		if ($tokens[($stackPtr + 1)]['code'] !== T_WHITESPACE) {
			$found = 0;
		} else if ($tokens[($stackPtr + 1)]['content'] !== ' ') {
			if (strpos($tokens[($stackPtr + 1)]['content'], $phpcsFile->eolChar) !== false) {
				$found = 'newline';
			} else {
				$found = $tokens[($stackPtr + 1)]['length'];
			}
		}

		if ($found !== $expected && $tokens[$stackPtr]['content']!=="else" && $tokens[$stackPtr]['content']!=="try") {
			$error = 'Expected %s space(s) after %s keyword; %s found';
			$data = [
				$expected,
				strtoupper($tokens[$stackPtr]['content']),
				$found,
			];

			$fix = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceAfterKeyword', $data);
			if ($fix === true) {
				if ($found === 0) {
					$phpcsFile->fixer->addContent($stackPtr, str_repeat(' ', $expected));
				} else {
					$phpcsFile->fixer->replaceToken(($stackPtr + 1), str_repeat(' ', $expected));
				}
			}
		}

		//also check if there is no opening brace on the same line
		$currentLine=$tokens[$stackPtr]['line'];
		$inStack=$stackPtr+1;
		$positionOfLastClosingBracket = 0;

		while($currentLine==$tokens[$inStack]['line'])
		{//Search the position of the last closing bracket
			if ($tokens[$inStack]['code']==T_CLOSE_PARENTHESIS) $positionOfLastClosingBracket = $inStack;
			$inStack++;
			if (!isset($tokens[$inStack])) break;
		}

		$inStack=$stackPtr+1;
		while($currentLine==$tokens[$inStack]['line'])
		{
			if ($tokens[$inStack]['code']==T_OPEN_CURLY_BRACKET && $positionOfLastClosingBracket < $inStack)
			{ //
				$phpcsFile->addError("Curly bracket of $controlStructureName should be on next line after $controlStructureName.",$inStack,"CurlyBracketOpenOnNextLine");
				break;
			}
			$inStack++;
			if (!isset($tokens[$inStack])) break;
		}


		// Single newline after opening brace.
		if (isset($tokens[$stackPtr]['scope_opener']) === true) {
			$opener = $tokens[$stackPtr]['scope_opener'];
			for ($next = ($opener + 1); $next < $phpcsFile->numTokens; $next++) {
				$code = $tokens[$next]['code'];

				if ($code === T_WHITESPACE
					|| ($code === T_INLINE_HTML
						&& trim($tokens[$next]['content']) === '')
				) {
					continue;
				}

				// Skip all empty tokens on the same line as the opener.
				if ($tokens[$next]['line'] === $tokens[$opener]['line']
					&& (isset(Tokens::$emptyTokens[$code]) === true
						|| $code === T_CLOSE_TAG)
				) {
					continue;
				}

				// We found the first bit of a code, or a comment on the
				// following line.
				break;
			}//end for

			if ($tokens[$next]['line'] === $tokens[$opener]['line']) {
				$error = 'Newline required after opening brace';
				$fix = $phpcsFile->addFixableError($error, $opener, 'NewlineAfterOpenBrace');
				if ($fix === true) {
					$phpcsFile->fixer->beginChangeset();
					for ($i = ($opener + 1); $i < $next; $i++) {
						if (trim($tokens[$i]['content']) !== '') {
							break;
						}

						// Remove whitespace.
						$phpcsFile->fixer->replaceToken($i, '');
					}

					$phpcsFile->fixer->addContent($opener, $phpcsFile->eolChar);
					$phpcsFile->fixer->endChangeset();
				}
			}//end if
		} else if ($tokens[$stackPtr]['code'] === T_WHILE) {
			// Zero spaces after parenthesis closer, but only if followed by a semicolon.
			$closer = $tokens[$stackPtr]['parenthesis_closer'];
			$nextNonEmpty = $phpcsFile->findNext(Tokens::$emptyTokens, ($closer + 1), null, true);
			if ($nextNonEmpty !== false && $tokens[$nextNonEmpty]['code'] === T_SEMICOLON) {
				$found = 0;
				if ($tokens[($closer + 1)]['code'] === T_WHITESPACE) {
					if (strpos($tokens[($closer + 1)]['content'], $phpcsFile->eolChar) !== false) {
						$found = 'newline';
					} else {
						$found = $tokens[($closer + 1)]['length'];
					}
				}

				if ($found !== 0) {
					$error = 'Expected 0 spaces before semicolon; %s found';
					$data = [$found];
					$fix = $phpcsFile->addFixableError($error, $closer, 'SpaceBeforeSemicolon', $data);
					if ($fix === true) {
						$phpcsFile->fixer->replaceToken(($closer + 1), '');
					}
				}
			}
		}//end if

		// Only want to check multi-keyword structures from here on.
		if ($tokens[$stackPtr]['code'] === T_WHILE) {
			if (isset($tokens[$stackPtr]['scope_closer']) !== false) {
				return;
			}

			$closer = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);
			if ($closer === false
				|| $tokens[$closer]['code'] !== T_CLOSE_CURLY_BRACKET
				|| $tokens[$tokens[$closer]['scope_condition']]['code'] !== T_DO
			) {
				return;
			}
		} else if ($tokens[$stackPtr]['code'] === T_ELSE
			|| $tokens[$stackPtr]['code'] === T_ELSEIF
			|| $tokens[$stackPtr]['code'] === T_CATCH
			|| $tokens[$stackPtr]['code'] === T_FINALLY
		) {
			if (isset($tokens[$stackPtr]['scope_opener']) === true
				&& $tokens[$tokens[$stackPtr]['scope_opener']]['code'] === T_COLON
			) {
				// Special case for alternate syntax, where this token is actually
				// the closer for the previous block, so there is no spacing to check.
				return;
			}

			$closer = $phpcsFile->findPrevious(Tokens::$emptyTokens, ($stackPtr - 1), null, true);
			if ($closer === false || $tokens[$closer]['code'] !== T_CLOSE_CURLY_BRACKET) {
				return;
			}
		} else {
			return;
		}//end if



	}//end process()


}//end class