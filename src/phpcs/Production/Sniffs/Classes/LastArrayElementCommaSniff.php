<?php
/**
 * Prüft auf das Vorhandensein eines Kommas beim letzten Arrayelement.
 *
 * @author Andreas Mirl <andreas.mirl@sh.de>
 */
class Production_Sniffs_Classes_LastArrayElementCommaSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return int[]
     */
    public function register()
    {
        return [T_ARRAY];
    }

    /**
     * Durchlaufe diesen Prozess, wenn eines der registrierten Tokens auftritt.
     *
     * @param PHP_CodeSniffer_File $phpcsFile Die durchsuchte Datei.
     * @param int                  $stackPointer Die Position des aktuellen Tokens im $tokens-Stack
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPointer)
    {
        $tokens       = $phpcsFile->getTokens();
        $currentToken = $tokens[$stackPointer];

        $closingParenthesisCandidate = $currentToken['parenthesis_closer'];
        $whitespaceCandidate         = $tokens[$closingParenthesisCandidate - 1];
        $linebreakCandidate          = $tokens[$closingParenthesisCandidate - 2];
        $commaCandidate              = $tokens[$closingParenthesisCandidate - 3];

        $hasClosingParenthesis = ($tokens[$closingParenthesisCandidate]['type'] === 'T_CLOSE_PARENTHESIS'
            && $whitespaceCandidate['type'] === 'T_WHITESPACE');

        $hasLinebreakBeforeClosingParenthesis = ($linebreakCandidate['type'] === 'T_WHITESPACE'
            && $linebreakCandidate['content'] === chr(10));

        $hasCommaAfterLastElement = $commaCandidate['type'] === 'T_COMMA';

        if ($hasClosingParenthesis && $hasLinebreakBeforeClosingParenthesis && !$hasCommaAfterLastElement)
        {
            $type  = 'Array element without comma.';
            $data  = $commaCandidate['content'];
            $error = 'Array element without comma: ' . $commaCandidate['line'];
            $phpcsFile->addWarning($error, $closingParenthesisCandidate - 3, $type, $data);
        }
    }
}