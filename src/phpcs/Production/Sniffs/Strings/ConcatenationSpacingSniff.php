<?php
namespace Sparhandy\Sniffs\Strings;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Makes sure there are spaces between the concatenation operator (.) and
 * the strings being concatenated.
 *
 * @author Stefano Kowalke <blueduck@gmx.net>
 * @author Greg Sherwood <gsherwood@squiz.net>
 * @author Marc McIntyre <mmcintyre@squiz.net>
 * @author Sebastian Knott <sebastian@sebastianknott.de>
 */
class ConcatenationSpacingSniff implements Sniff
{
    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public function register()
    {
        return [T_STRING_CONCAT];
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
        $tokens       = $phpcsFile->getTokens();
        $currentToken = $tokens[$stackPointer];
        $prevToken    = $tokens[($stackPointer - 1)];
        $nextToken    = $tokens[($stackPointer + 1)];

        if ($prevToken['code'] !== T_WHITESPACE || $nextToken['code'] !== T_WHITESPACE)
        {
            $error = 'Concat operator must be surrounded by spaces. ';
            $phpcsFile->addError($error, $stackPointer, 'Production.ConcatenationSpacing.NoSpaceAroundConcat');
        }

        if (($prevToken['code'] === T_WHITESPACE && false !== strpos($prevToken['content'], '  '))
            || ($nextToken['code'] === T_WHITESPACE && false !== strpos($nextToken['content'], '  '))
        )
        {
            // Indents in multi-line concatenations must not throw warnings.
            if ($currentToken['code'] === T_STRING_CONCAT && $prevToken['code'] === T_WHITESPACE)
            {
                return;
            }

            $error = 'Concat operator should be surrounded by just one space';
            $phpcsFile->addWarning($error, $stackPointer, 'Production.ConcatenationSpacing.OnlyOneSpaceAroundConcat');
        }
    }
}