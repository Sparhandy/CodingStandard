<?php
/**
 * Checks for the existence of an assignment inside a control structure.
 *
 * @author Julian Hübner <julian.huebner@sh.de>
 * @author Andy Grunwald <andygrunwald@gmail.com>
 */
class Production_Sniffs_ControlStructures_AssignmentInControlStructurSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [
            T_WHILE,
            T_IF,
        ];
    }

    /**
     * {@inheritdoc}
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