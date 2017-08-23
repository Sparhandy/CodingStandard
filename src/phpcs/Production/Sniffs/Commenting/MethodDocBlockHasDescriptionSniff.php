<?php
/**
 * Prüft, dass ein Methodenkommentar eine Beschreibung hat.
 *
 * @author Alexander Christmann <alexander.christmann@sh.de>
 * @author Oliver Klee <github@oliverklee.de>
 */
class Production_Sniffs_Commenting_MethodDocBlockHasDescriptionSniff extends Production_Sniffs_Abstract_MethodSniff
{
    /**
     * Snifft anhand des gefundenen Tokens.
     *
     * @param PHP_CodeSniffer_File $sniffedFile durchsuchte Datei
     * @param int                  $index Position des aktuellen Tokens in der Tokens-Liste
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
            $this->addWarning($sniffedFile, $index, 'Der DocBlock der Methode hat keine Description am Anfang.');
        }
    }

    /**
     * Prüft, ob der DocBlock der Methode an $index eine Beschreibung hat.
     *
     * @param PHP_CodeSniffer_File $sniffedFile durchsuchte Datei
     * @param int                  $index Position des aktuellen Tokens in der Tokens-Liste
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
     * Prüft, ob die Methode an $index eine Beschreibung im DocBlock braucht.
     *
     * @param PHP_CodeSniffer_File $sniffedFile durchsuchte Datei
     * @param int                  $index Position des aktuellen Tokens in der Tokens-Liste
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