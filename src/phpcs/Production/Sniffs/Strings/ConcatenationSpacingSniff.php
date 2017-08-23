<?php
/**
 * Makes sure there are no spaces between the concatenation operator (.) and
 * the strings being concatenated.
 *
 * @author    Stefano Kowalke <blueduck@gmx.net>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 */
class Production_Sniffs_Strings_ConcatenationSpacingSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return int[]
     */
    public function register()
    {
        return [T_STRING_CONCAT];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPointer The position of the current token in the stack passed in $tokens
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPointer)
    {
        $tokens       = $phpcsFile->getTokens();
        $currentToken = $tokens[$stackPointer];
        $prevToken    = $tokens[($stackPointer - 1)];
        $nextToken    = $tokens[($stackPointer + 1)];

        if ($prevToken['code'] !== T_WHITESPACE || $nextToken['code'] !== T_WHITESPACE)
        {
            $error = 'Concat operator must be surrounded by spaces. ';
            $phpcsFile->addError($error, $stackPointer, 'NoSpaceAroundConcat');
        }

        if (($prevToken['code'] === T_WHITESPACE && stristr($prevToken['content'], '  ') !== false)
            || ($nextToken['code'] === T_WHITESPACE && stristr($nextToken['content'], '  ') !== false)
        )
        {
            // Dieser IF, damit Einrückungen bei einem Multi-Line String Concat keine Fehler werfen
            if ($currentToken['code'] === T_STRING_CONCAT && $prevToken['code'] === T_WHITESPACE)
            {
                return;
            }

            // Ansonsten ist es wirklich ein Fehler:
            $error = 'Concat operator should be surrounded by just one space';
            $phpcsFile->addWarning($error, $stackPointer, 'OnlyOneSpaceAroundConcat');
        }
    }
}