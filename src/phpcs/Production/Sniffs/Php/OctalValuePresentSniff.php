<?php
/**
 * Prüft auf das Vorhandensein von Oktalzahlen in Arrays, zuweisungen und Übergabeparametern.
 *
 * @author Christian Klatt <christian.klatt@sh.de>
 * @author Thorsten Müller <thorsten.mueller@sh.de>
  */
class Production_Sniffs_Php_OctalValuePresentSniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return string[]
     */
    public function register()
    {
        return [
            T_LNUMBER,
        ];
    }

    /**
     * Durchlaufe diesen Prozess, wenn eines der registrierten Tokens auftritt.
     *
     * @param PHP_CodeSniffer_File $phpcsFile    Die durchsuchte Datei.
     * @param int                  $stackPointer Die Position des aktuellen Tokens im $tokens-Stack.
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