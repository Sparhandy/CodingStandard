<?php
namespace Sparhandy\Sniffs\Methods;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks if the 'strict' parameter is set to true in in_array calls.
 *
 * @author Christian Klatt <christian.klatt@sh.de>
 * @author Andreas Mirl <andreas.mirl@sh.de>
 * @author Oliver Klee <github@oliverklee.de>
 * @author Sebastian Knott <sebastian.knott@sh.de>
 */
class StrictInArraySniff implements Sniff
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
        return [T_STRING];
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
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
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
            $type  = 'Production.StrictInArray.non-strictArray';
            $data  = [$inArrayContext];
            $error = 'in_array needs to have the third parameter set to true.';
            $phpcsFile->addWarning($error, $stackPointer, $type, $data);
        }
    }
}