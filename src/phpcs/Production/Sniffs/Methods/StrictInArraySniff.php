<?php
/**
 * Prüft alle Vorkommen von 'in_array' auf Typsicherheit.
 *
 * @author Christian Klatt <christian.klatt@sh.de>
 * @author Andreas Mirl <andreas.mirl@sh.de>
 * @author Oliver Klee <github@oliverklee.de>
 */
class Production_Sniffs_Methods_StrictInArraySniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var string[]
     */
    public $supportedTokenizers = ['PHP'];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return int[]
     */
    public function register()
    {
        return [T_STRING];
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPointer The position of the current token in the stack passed in $tokens
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPointer)
    {
        $tokens  = $phpcsFile->getTokens();
        $content = $tokens[$stackPointer]['content'];
        if ($tokens[$stackPointer]['type'] !== 'T_STRING' || stripos($content, 'in_array') === false)
        {
            return;
        }

        $end                 = $tokens[$stackPointer + 1]['parenthesis_closer'];
        $length              = $end - $stackPointer + 1;
        $inArrayContext      = $phpcsFile->getTokensAsString($stackPointer, $length);
        $inArrayContextMatch = [];
        preg_match('/in_array\\(\\s*.+,\\s*.+,\\s*true\\s*\\)/sim', $inArrayContext, $inArrayContextMatch);
        if (empty($inArrayContextMatch))
        {
            $type  = 'Non-strict in_array found.';
            $data  = [$inArrayContext];
            $error = 'in_array needs to have the third parameter set to true.';
            $phpcsFile->addWarning($error, $stackPointer, $type, $data);
        }
    }
}