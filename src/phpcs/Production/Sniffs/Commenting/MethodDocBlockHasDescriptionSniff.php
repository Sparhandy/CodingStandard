<?php
namespace Sparhandy\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use Sparhandy\Sniffs\Abstracts\MethodSniff;

/**
 * Checks if the methods docblock contains a description.
 *
 * @author Alexander Christmann <alexander.christmann@sh.de>
 * @author Oliver Klee <github@oliverklee.de>
 * @author Sebastian Knott <sebastian.knott@sh.de>
 */
class MethodDocBlockHasDescriptionSniff extends MethodSniff
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function process(File $sniffedFile, $index)
    {
        if ($this->hasMethodDocBlock($sniffedFile, $index)
            && $this->needsMethodDocBlockDescription($sniffedFile, $index)
            && !$this->hasMethodDocBlockDescription($sniffedFile, $index)
        )
        {
            $this->addWarning($sniffedFile, $index, 'There is no description at the beginning of this docblock.');
        }
    }

    /**
     * Checks if the methods docblock contains a description.
     *
     * @param File $sniffedFile file to be checked
     * @param int  $index position of current token in token list
     *
     * @return bool
     */
    private function hasMethodDocBlockDescription(File $sniffedFile, $index)
    {
        $indexOfOpeningDocBlock = $sniffedFile->findPrevious([T_DOC_COMMENT_OPEN_TAG], $index);
        $indexOfClosingDocBlock = $sniffedFile->findPrevious([T_DOC_COMMENT_CLOSE_TAG], $index);

        $hasDescription = false;
        for ($i = $indexOfOpeningDocBlock + 1; $i < $indexOfClosingDocBlock; $i++)
        {
            if (!$this->isCommentWhitespaceToken($sniffedFile, $i) && !$this->isCommentStartToken($sniffedFile, $i))
            {
                $hasDescription = $this->isCommentTextToken($sniffedFile, $i);
                break;
            }
        }

        return $hasDescription;
    }

    /**
     * Checks if the method annotation is in need of a description.
     *
     * @param File $sniffedFile file to be checked
     * @param int  $index position of current token in token list
     *
     * @return bool
     */
    private function needsMethodDocBlockDescription(File $sniffedFile, $index)
    {
        $methodName      = $sniffedFile->getDeclarationName($index);
        $isSpecialMethod = $this->methodIsAccessor($methodName);
        $isDataProvider  = $this->methodIsDataProvider($methodName);

        return !$isSpecialMethod && !$isDataProvider && !$this->isTestMethod($sniffedFile, $index);
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