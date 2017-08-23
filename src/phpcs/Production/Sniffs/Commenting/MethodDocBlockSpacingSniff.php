<?php
/**
 * Prüft, dass keine Leerzeilen zwischen dem Methoden-DocBlock und der Deklaration stehen.
 *
 * @author Oliver Klee <github@oliverklee.de>
 * @author Dimitri Kontsevoi <dimitri.kontsevoi@sh.de>
 */
class Production_Sniffs_Commenting_MethodDocBlockSpacingSniff extends Production_Sniffs_Abstract_MethodSniff
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
        if (!$this->hasMethodDocBlock($sniffedFile, $index))
        {
            return;
        }

        $numberOfLineFeeds = $this->numberOfLineFeedsBetweenDocBlockAndDeclaration($sniffedFile, $index);
        if ($numberOfLineFeeds > 1)
        {
            $this->addWarning($sniffedFile, $index, 'Bitte keine Leerzeilen zwischen DocBlock und Deklaration.');
        }
        elseif ($numberOfLineFeeds === 0)
        {
            $this->addWarning($sniffedFile, $index, 'Bitte die Deklaration in eine separate Zeile schreiben.');
        }
    }

    /**
     * Zählt die Linefeeds zwischen Methoden-DocBlock und Deklaration.
     *
     * @param PHP_CodeSniffer_File $sniffedFile durchsuchte Datei
     * @param int                  $indexOfFunctionToken Position des aktuellen Tokens in der Tokens-Liste
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