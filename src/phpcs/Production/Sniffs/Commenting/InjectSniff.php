<?php
/**
 * Prüft alle vorkommen von @Inject bzw. @Injectable auf korrekte Anwendung.
 *
 * @author Christian Klatt <christian.klatt@sh.de>
 * @author Andreas Mirl <andreas.mirl@sh.de>
 */
class Production_Sniffs_Commenting_InjectSniff implements PHP_CodeSniffer_Sniff
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
        return [
            T_DOC_COMMENT_TAG,
            T_DOC_COMMENT_STRING,
            T_DOC_COMMENT_WHITESPACE,
        ];
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
        if (preg_match('/@ *inject/i', $content, $matches) !== 0)
        {
            $injectContent     = $tokens[$stackPointer]['content']
                . $tokens[$stackPointer + 1]['content']
                . $tokens[$stackPointer + 2]['content'];
            $injectMatches     = [];
            $injectPattern     = '@Inject(\(\{"[a-zA-Z]+((\.|\\\\|_)[a-zA-Z]+)*"(, "[a-zA-Z]+((\.|\\\\|_)[a-zA-Z]+)*")*\}\))?';
            $injectablePattern = '@Injectable\([a-zA-Z]+="[a-zA-Z]+"(, ([a-zA-Z]+="[a-zA-Z]+"|[a-zA-Z]+=(true|false)))*\)';
            $completePattern   = '/(' . $injectPattern . ')|(' . $injectablePattern . ')/';
            preg_match($completePattern, $injectContent, $injectMatches);
            if (empty($injectMatches))
            {
                $type  = 'Injection found.';
                $data  = [$injectContent];
                $error = 'Inject[able] annotation has wrong format.';
                $phpcsFile->addWarning($error, $stackPointer, $type, $data);
            }
        }
    }
}