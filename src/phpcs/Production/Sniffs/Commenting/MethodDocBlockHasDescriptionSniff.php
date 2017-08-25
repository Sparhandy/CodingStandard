<?php
/**
 * Checks if the methods docblock contains a description.
 *
 * @author Alexander Christmann <alexander.christmann@sh.de>
 * @author Oliver Klee <github@oliverklee.de>
 */
class Production_Sniffs_Commenting_MethodDocBlockHasDescriptionSniff extends Production_Sniffs_Abstract_MethodSniff
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $sniffedFile, $index)
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
     * @param PHP_CodeSniffer_File $sniffedFile file to be checked
     * @param int                  $index position of current token in token list
     *
     * @return bool
     */
    private function hasMethodDocBlockDescription(PHP_CodeSniffer_File $sniffedFile, $index)
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
     * @param PHP_CodeSniffer_File $sniffedFile file to be checked
     * @param int                  $index position of current token in token list
     *
     * @return bool
     */
    private function needsMethodDocBlockDescription(PHP_CodeSniffer_File $sniffedFile, $index)
    {
        $methodName      = $sniffedFile->getDeclarationName($index);
        $isSpecialMethod = $this->methodIsAccessor($methodName);
        $isDataProvider  = $this->methodIsDataProvider($methodName);

        return !$isSpecialMethod && !$isDataProvider && !$this->isTestMethod($sniffedFile, $index);
    }
}