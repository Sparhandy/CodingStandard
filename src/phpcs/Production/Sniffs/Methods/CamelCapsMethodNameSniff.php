<?php
namespace Sparhandy\Sniffs\Methods;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;
use PHP_CodeSniffer\Util\Common;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Ensures method names are defined using camel case.
 *
 * @author Greg Sherwood <gsherwood@squiz.net>
 * @author Jens von der Heydt <jens.heydt@ppw.de>
 * @author Alexander Christmann <alexander.christmann@sh.de>
 * @author Sebastian Knott <sebastian.knott@sh.de>
 */
class CamelCapsMethodNameSniff extends AbstractScopeSniff
{
    /**
     * Constructs a PSR1_Sniffs_Methods_CamelCapsMethodNameSniff.
     */
    public function __construct()
    {
        parent::__construct([T_CLASS, T_INTERFACE, T_TRAIT], [T_FUNCTION], true);
    }

    /**
     * Processes the tokens within the scope.
     *
     * @param File $phpcsFile The file being processed.
     * @param int  $stackPointer The position where this token was found
     * @param int  $currScope The position of the current scope.
     *
     * @return void
     */
    protected function processTokenWithinScope(File $phpcsFile, $stackPointer, $currScope)
    {
        $methodName = $phpcsFile->getDeclarationName($stackPointer);
        if ($methodName === null)
        {
            // Ignore closures.
            return;
        }

        // Ignore magic methods.
        $magicPart = strtolower(substr($methodName, 2));
        if (isset($this->magicMethods[$magicPart]) === true
            || isset($this->methodsDoubleUnderscore[$magicPart]) === true
        )
        {
            return;
        }

        $testName = ltrim($methodName, '_');

        if (Common::isCamelCaps($testName, false, true, false) === false)
        {
            $className = $phpcsFile->getDeclarationName($currScope);
            if ($this->isUnitTest($phpcsFile, $stackPointer) === false)
            {
                $error     = 'Method name "%s" is not in camel caps format';
                $errorData = [$className . '::' . $methodName];
                $phpcsFile->addError($error, $stackPointer, 'Production.CamelCapsMethodName.NotCamelCaps', $errorData);
                $phpcsFile->recordMetric($stackPointer, 'CamelCase method name', 'no');
            }
        }
        else
        {
            $phpcsFile->recordMetric($stackPointer, 'CamelCase method name', 'yes');
        }
    }

    /**
     * Checks if the method is marked as a unit test.
     *
     * @param File $phpcsFile
     * @param int  $stackPointer
     *
     * @return bool
     */
    private function isUnitTest(File $phpcsFile, $stackPointer)
    {
        $tokens     = $phpcsFile->getTokens();
        $find       = Tokens::$methodPrefixes;
        $find[]     = T_WHITESPACE;
        $commentEnd = $phpcsFile->findPrevious($find, ($stackPointer - 1), null, true);
        if ($tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG
            && $tokens[$commentEnd]['code'] !== T_COMMENT
        )
        {
            // Missing function doc comment
            return false;
        }
        if ($tokens[$commentEnd]['code'] === T_COMMENT)
        {
            // You must use "/**" style comments for a function comment
            return false;
        }
        $commentStart = $tokens[$commentEnd]['comment_opener'];
        foreach ($tokens[$commentStart]['comment_tags'] as $tag)
        {
            if ($tokens[$tag]['content'] === '@test')
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Processes a token that is found outside the scope that this test is
     * listening to.
     *
     * @param File $phpcsFile The file where this token was found.
     * @param int  $stackPtr The position in the stack where this
     *                                               token was found.
     *
     * @return void
     */
    protected function processTokenOutsideScope(File $phpcsFile, $stackPtr)
    {
    }
}