<?php
/**
 * Checks for the existence of octal numbers in arrays, assignments and parameter values.
 *
 * @author Christian Klatt <christian.klatt@sh.de>
 * @author Thorsten Müller <thorsten.mueller@sh.de>
  */
class Production_Sniffs_Php_OctalValuePresentSniff
{
    /**
     * {@inheritdoc}
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
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPointer)
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

        $type  = 'Value element is octal.';
        $data  = $currentValue;
        $error = 'Value element is octal in Line: ' . $currentLine;
        $phpcsFile->addError($error, $stackPointer, $type, $data);
    }
}