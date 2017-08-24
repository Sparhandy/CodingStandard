<?php
/**
 * Checks for the non-existence of an empty line between a method and its docblock.
 *
 * @author Oliver Klee <github@oliverklee.de>
 * @author Dimitri Kontsevoi <dimitri.kontsevoi@sh.de>
 */
class Production_Sniffs_Commenting_MethodDocBlockSpacingSniff extends Production_Sniffs_Abstract_MethodSniff
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $sniffedFile, $index)
    {
        if (!$this->hasMethodDocBlock($sniffedFile, $index))
        {
            return;
        }

        $numberOfLineFeeds = $this->numberOfLineFeedsBetweenDocBlockAndDeclaration($sniffedFile, $index);
        if ($numberOfLineFeeds > 1)
        {
            $this->addWarning($sniffedFile, $index, 'No empty lines between a method and its docblock.');
        }
        elseif ($numberOfLineFeeds === 0)
        {
            $this->addWarning($sniffedFile, $index, 'The method and its docblock can\'t be in the same line.');
        }
    }

    /**
     * Counts the linefeeds between a method and its docblock.
     *
     * @param PHP_CodeSniffer_File $sniffedFile
     * @param int                  $indexOfFunctionToken
     *
     * @return int
     */
    protected function numberOfLineFeedsBetweenDocBlockAndDeclaration(PHP_CodeSniffer_File $sniffedFile, $indexOfFunctionToken)
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
}