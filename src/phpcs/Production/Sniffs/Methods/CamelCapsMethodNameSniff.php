<?php
/**
 * Ensures method names are defined using camel case.
 *
 * @author Greg Sherwood <gsherwood@squiz.net>
 * @author Jens von der Heydt <jens.vonderHeydt@sh.de>
 * @author Alexander Christmann <alexander.christmann@sh.de>
 */
class Production_Sniffs_Methods_CamelCapsMethodNameSniff extends PHP_CodeSniffer_Standards_AbstractScopeSniff
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
     * @param PHP_CodeSniffer_File $phpcsFile The file being processed.
     * @param int                  $stackPointer The position where this token was found
     * @param int                  $currScope The position of the current scope.
     *
     * @return void
     */
    protected function processTokenWithinScope(PHP_CodeSniffer_File $phpcsFile, $stackPointer, $currScope)
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

        if (PHP_CodeSniffer::isCamelCaps($testName, false, true, false) === false)
        {
            $className = $phpcsFile->getDeclarationName($currScope);
            if ($this->isUnitTest($phpcsFile, $stackPointer, $currScope) === false)
            {
                $error     = 'Method name "%s" is not in camel caps format';
                $errorData = [$className . '::' . $methodName];
                $phpcsFile->addError($error, $stackPointer, 'NotCamelCaps', $errorData);
                $phpcsFile->recordMetric($stackPointer, 'CamelCase method name', 'no');
            }
        }
        else
        {
            $phpcsFile->recordMetric($stackPointer, 'CamelCase method name', 'yes');
        }
    }

    /**
     * Prüft, ob die gesniffte Methode ein Unit-Test ist.
     *
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int                  $stackPointer
     *
     * @return bool
     */
    private function isUnitTest(PHP_CodeSniffer_File $phpcsFile, $stackPointer)
    {
        $tokens     = $phpcsFile->getTokens();
        $find       = PHP_CodeSniffer_Tokens::$methodPrefixes;
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
}