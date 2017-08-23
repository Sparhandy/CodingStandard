<?php
/**
 * Prüft auf das Vorhandensein von DocBlocks in Methoden sowie auf die @return-Annotation.
 *
 * @author Alexander Christmann <alexander.christmann@sh.de>
 * @author Oliver Klee <github@oliverklee.de>
 * @author Dimitri Kontsevoi <dimitri.kontsevoi@sh.de>
 */
class Production_Sniffs_Commenting_MethodHasDocBlockSniff extends Production_Sniffs_Abstract_MethodSniff
{
    /**
     * @var string[]
     */
    private $methodNamesWithoutNecessaryDocBlock = ['setUp', 'tearDown', 'setUpTest', 'tearDownTest'];

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
        if ($this->methodNeedsDocBlock($sniffedFile, $index) && !$this->hasMethodDocBlock($sniffedFile, $index))
        {
            $this->addWarning($sniffedFile, $index, 'Die Methode hat keinen DocBlock. Bitte dokumentieren.');
        }
    }

    /**
     * Prüft, ob die Methode an $index einen DocBlock benötigt.
     *
     * @param PHP_CodeSniffer_File $sniffedFile durchsuchte Datei
     * @param int                  $index Position des aktuellen Tokens in der Tokens-Liste
     *
     * @return bool
     */
    private function methodNeedsDocBlock(PHP_CodeSniffer_File $sniffedFile, $index)
    {
        $methodName = $sniffedFile->getDeclarationName($index);

        return !in_array($methodName, $this->methodNamesWithoutNecessaryDocBlock, true);
    }
}