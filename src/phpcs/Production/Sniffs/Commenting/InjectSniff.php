<?php
namespace Sparhandy\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks the correct usage of @Inject and @Injectable annotations.
 *
 * @author Christian Klatt <christian.klatt@sh.de>
 * @author Andreas Mirl <andreas.mirl@sh.de>
 * @author Sebastian Knott <sebastian@sebastianknott.de>
 */
class InjectSniff implements Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var string[]
     */
    public $supportedTokenizers = ['PHP'];

    /**
     * {@inheritdoc}
     *
     * @return string[]
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
     * {@inheritdoc}
     *
     * @param File $phpcsFile
     * @param int  $stackPointer
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPointer)
    {
        $tokens  = $phpcsFile->getTokens();
        $content = $tokens[$stackPointer]['content'];
        if (preg_match('/@ *inject/i', $content) !== 0)
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
                $type  = 'Production.Inject.InjectionFound';
                $data  = [$injectContent];
                $error = 'Inject[able] annotation has wrong format.';
                $phpcsFile->addWarning($error, $stackPointer, $type, $data);
            }
        }
    }
}