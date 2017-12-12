<?php
namespace Sparhandy\Sniffs\Php;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks for the existence of octal numbers in arrays, assignments and parameter values.
 *
 * @author Christian Klatt <christian.klatt@sh.de>
 * @author Thorsten Müller <thorsten.mueller@sh.de>
 * @author Sebastian Knott <sebastian@sebastianknott.de>
 */
class OctalValuePresentSniff implements Sniff
{
    /**
     * {@inheritdoc}
     *
     * @return int[]
     */
    public function register()
    {
        return [
            T_LNUMBER,
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
        $tokens            = $phpcsFile->getTokens();
        $currentToken      = $tokens[$stackPointer];
        $currentValue      = $currentToken['content'];
        $currentLine       = $currentToken['line'];
        $regexValueIsOctal = preg_match('/^0[0-9]+$/', $currentValue);
        $isWithinMkDir     = $phpcsFile->findPrevious(T_STRING, $stackPointer, $stackPointer - 7, null, 'mkdir');

        if (!$regexValueIsOctal || $isWithinMkDir !== false)
        {
            return;
        }

        $type  = 'Production.OctalValuePresent.ValueEelementIsOctal';
        $data  = $currentValue;
        $error = 'Value element is octal in Line: ' . $currentLine;
        $phpcsFile->addError($error, $stackPointer, $type, $data);
    }
}