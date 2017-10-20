<?php
namespace Sparhandy\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks for the existence of a comma at the last element of an array.
 *
 * @author Andreas Mirl <andreas.mirl@sh.de>
 * @author Sebastian Knott <sebastian.knott@sh.de>
 */
class LastArrayElementCommaSniff implements Sniff
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [T_ARRAY];
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPointer)
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
            $type  = 'Production.LastArrayElementComma.ArrayElementWithoutComma';
            $data  = $commaCandidate['content'];
            $error = 'Array element without comma: ' . $commaCandidate['line'];
            $phpcsFile->addWarning($error, $closingParenthesisCandidate - 3, $type, $data);
        }
    }
}