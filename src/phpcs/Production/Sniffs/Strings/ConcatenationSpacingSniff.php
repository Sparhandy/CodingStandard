<?php
/**
 * Makes sure there are spaces between the concatenation operator (.) and
 * the strings being concatenated.
 *
 * @author    Stefano Kowalke <blueduck@gmx.net>
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 */
class Production_Sniffs_Strings_ConcatenationSpacingSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [T_STRING_CONCAT];
    }

    /**
     * {@inheritdoc}
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
            // Indents in multi-line concatenations must not throw warnings.
            if ($currentToken['code'] === T_STRING_CONCAT && $prevToken['code'] === T_WHITESPACE)
            {
                return;
            }

            $error = 'Concat operator should be surrounded by just one space';
            $phpcsFile->addWarning($error, $stackPointer, 'OnlyOneSpaceAroundConcat');
        }
    }
}