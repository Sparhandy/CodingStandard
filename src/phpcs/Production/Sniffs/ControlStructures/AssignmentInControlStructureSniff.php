<?php
namespace Sparhandy\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks for the existence of an assignment inside a control structure.
 *
 * @author Julian Hübner <julian.huebner@sh.de>
 * @author Andy Grunwald <andygrunwald@gmail.com>
 * @author Sebastian Knott <sebastian@sebastianknott.de>
 */
class AssignmentInControlStructureSniff implements Sniff
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
     * {@inheritdoc}
     *
     * @param File $phpcsFile
     * @param int  $stackPointer
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPointer)
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
            $type  = 'Production.AssignmentInControlStructure.AssignmentsInConditions';
            $data  = [$tokens[$stackPointer]['content']];
            $error = 'Please extract the assignment before the condition.';
            $phpcsFile->addWarning($error, $stackPointer, $type, $data);
        }
    }
}