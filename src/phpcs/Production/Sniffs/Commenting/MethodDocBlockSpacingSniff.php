<?php
namespace Sparhandy\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use Sparhandy\Sniffs\Abstracts\MethodSniff;

/**
 * Checks for the non-existence of an empty line between a method and its docblock.
 *
 * @author Oliver Klee <github@oliverklee.de>
 * @author Dimitri Kontsevoi <dimitri.kontsevoi@sh.de>
 * @author Sebastian Knott <sebastian@sebastianknott.de>
 */
class MethodDocBlockSpacingSniff extends MethodSniff
{
    /**
     * {@inheritdoc}
     *
     * @param File $sniffedFile
     * @param int  $index
     *
     * @return void
     */
    public function process(File $sniffedFile, $index)
    {
        if (!$this->hasMethodDocBlock($sniffedFile, $index))
        {
            return;
        }

        $numberOfLineFeeds = $this->numberOfLineFeedsBetweenDocBlockAndDeclaration($sniffedFile, $index);
        if ($numberOfLineFeeds > 1)
        {
            $this->addWarning($sniffedFile, $index, 'No empty lines between a method declaration and its docblock.');
        }
        elseif ($numberOfLineFeeds === 0)
        {
            $this->addWarning($sniffedFile, $index, 'The method and its docblock can\'t be in the same line.');
        }
    }

    /**
     * Counts the linefeeds between a method declaration and its docblock.
     *
     * @param File $sniffedFile file to be checked
     * @param int  $indexOfFunctionToken position of current token in token list
     *
     * @return int
     */
    protected function numberOfLineFeedsBetweenDocBlockAndDeclaration(File $sniffedFile, $indexOfFunctionToken)
    {
        $indexOfClosingDocBlock = $sniffedFile->findPrevious([T_DOC_COMMENT_CLOSE_TAG], $indexOfFunctionToken);
        if ($indexOfClosingDocBlock === false)
        {
            return false;
        }

        $numberOfLineFeeds = 0;
        for ($i = $indexOfClosingDocBlock; $i < $indexOfFunctionToken; $i++)
        {
            if ($this->isLinefeedToken($sniffedFile, $i))
            {
                $numberOfLineFeeds++;
            }
        }

        return $numberOfLineFeeds;
    }

    /**
     * Processes a token that is found within the scope that this test is
     * listening to.
     *
     * @param File $phpcsFile The file where this token was found.
     * @param int  $stackPtr The position in the stack where this
     *                                               token was found.
     * @param int  $currScope The position in the tokens array that
     *                                               opened the scope that this test is
     *                                               listening for.
     *
     * @return void
     */
    protected function processTokenWithinScope(File $phpcsFile, $stackPtr, $currScope)
    {
    }
}