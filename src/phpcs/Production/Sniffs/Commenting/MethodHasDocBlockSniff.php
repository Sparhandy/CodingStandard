<?php
/**
 * Checks for the existence of a methods docblock as well as the @return annotation inside the docblock.
 *
 * @author Alexander Christmann <alexander.christmann@sh.de>
 * @author Oliver Klee <github@oliverklee.de>
 * @author Dimitri Kontsevoi <dimitri.kontsevoi@sh.de>
 */
class Production_Sniffs_Commenting_MethodHasDocBlockSniff extends Production_Sniffs_Abstract_MethodSniff
{
    /** @var string[] */
    private $methodNamesWithoutNecessaryDocBlock = ['setUp', 'tearDown', 'setUpTest', 'tearDownTest'];

    /**
     * {@inheritdoc}
     *
     * @return void
     *
     * @throws PHP_CodeSniffer_Exception If the specified token is not of type
     *                                   T_FUNCTION, T_CLASS, T_ANON_CLASS,
     *                                   T_TRAIT or T_INTERFACE.
     */
    public function process(PHP_CodeSniffer_File $sniffedFile, $index)
    {
        if ($this->methodNeedsDocBlock($sniffedFile, $index) && !$this->hasMethodDocBlock($sniffedFile, $index))
        {
            $this->addWarning($sniffedFile, $index, 'Methods must have a docblock.');
        }
    }

    /**
     * Checks if the method declaration is in need of a docblock.
     *
     * @param PHP_CodeSniffer_File $sniffedFile file to be checked
     * @param int                  $index position of current token in token list
     *
     * @return bool
     *
     * @throws PHP_CodeSniffer_Exception If the specified token is not of type
     *                                   T_FUNCTION, T_CLASS, T_ANON_CLASS,
     *                                   T_TRAIT or T_INTERFACE.
     */
    private function methodNeedsDocBlock(PHP_CodeSniffer_File $sniffedFile, $index)
    {
        $methodName = $sniffedFile->getDeclarationName($index);

        return !in_array($methodName, $this->methodNamesWithoutNecessaryDocBlock, true);
    }
}