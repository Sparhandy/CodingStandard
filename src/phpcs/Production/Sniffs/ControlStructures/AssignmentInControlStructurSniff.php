<?php
/**
 * Prüfen, ob eine Zuweisung innerhalb einer Kontrollstruktur existiert.
 *
 * @author Julian Hübner <julian.huebner@sh.de>
 * @author Andy Grunwald <andygrunwald@gmail.com>
 */
class Production_Sniffs_ControlStructures_AssignmentInControlStructurSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return int[]
     */
    public function register()
    {
        return [
            T_WHILE,
            T_IF,
        ];
    }

    /**
     * Processes this sniff when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPointer The position of the current token in the stack passed in $tokens
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPointer)
    {
        $tokens = $phpcsFile->getTokens();

        $nextParenthesisIndex     = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $stackPointer);
        $parenthesisPositionStart = $tokens[$nextParenthesisIndex]['parenthesis_opener'];
        $parenthesisPositionEnd   = $tokens[$nextParenthesisIndex]['parenthesis_closer'];
        $equalOperatorIndex       = $phpcsFile->findNext(T_EQUAL, $parenthesisPositionStart, $parenthesisPositionEnd);
        if ($equalOperatorIndex === false)
        {
            return;
        }

        $braceBeforeParenthesis = $phpcsFile->findPrevious(T_OPEN_PARENTHESIS, $equalOperatorIndex, $nextParenthesisIndex);

        if ($braceBeforeParenthesis === $parenthesisPositionStart)
        {
            $type  = 'Assignments in conditions';
            $data  = [$tokens[$stackPointer]['content']];
            $error = 'Please extract the assignment before the condition.';
            $phpcsFile->addWarning($error, $stackPointer, $type, $data);
        }
    }
}