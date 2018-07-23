<?php
namespace PhilippWitzmann\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PhilippWitzmann\Sniffs\Abstracts\MethodSniff;

/**
 * Checks for the existence of a methods docblock as well as the @return annotation inside the docblock.
 *
 * @author Alexander Christmann <alexander.christmann@sh.de>
 * @author Oliver Klee <github@oliverklee.de>
 * @author Dimitri Kontsevoi <dimitri.kontsevoi@sh.de>
 * @author Sebastian Knott <sebastian@sebastianknott.de>
 */
class MethodHasDocBlockSniff extends MethodSniff
{
    /** @var string[] */
    private $methodNamesWithoutNecessaryDocBlock = [
        'setUp',
        'tearDown',
        'setUpTest',
        'tearDownTest',
        '__construct',
    ];

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
        if ($this->methodNeedsDocBlock($sniffedFile, $index) && !$this->hasMethodDocBlock($sniffedFile, $index))
        {
            $this->addWarning($sniffedFile, $index, 'Methods must have a docblock.');
        }
    }

    /**
     * Checks if the method declaration is in need of a docblock.
     *
     * @param File $sniffedFile file to be checked
     * @param int  $index       position of current token in token list
     *
     * @return bool
     */
    private function methodNeedsDocBlock(File $sniffedFile, $index)
    {
        $methodName          = $sniffedFile->getDeclarationName($index);
        $isSpecialMethod     = $this->methodIsAccessor($methodName);
        $isConstructor       = $this->methodIsConstructor($methodName);
        $doesNotNeedDocBlock = in_array($methodName, $this->methodNamesWithoutNecessaryDocBlock, true);

        return !$isSpecialMethod
               && !$isConstructor
               && !$doesNotNeedDocBlock;
    }

    /**
     * Processes a token that is found within the scope that this test is
     * listening to.
     *
     * @param File $phpcsFile                        The file where this token was found.
     * @param int  $stackPtr                         The position in the stack where this
     *                                               token was found.
     * @param int  $currScope                        The position in the tokens array that
     *                                               opened the scope that this test is
     *                                               listening for.
     *
     * @return void
     */
    protected function processTokenWithinScope(File $phpcsFile, $stackPtr, $currScope)
    {
    }
}