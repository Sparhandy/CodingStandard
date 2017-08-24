<?php
/**
 * Abstract class for sniffs relating to methods.
 *
 * @author Alexander Christmann <alexander.christmann@sh.de>
 * @author Oliver Klee <github@oliverklee.de>
 */
abstract class Production_Sniffs_Abstract_MethodSniff implements PHP_CodeSniffer_Sniff
{
    /** @var string[] */
    protected $modifierTokenTypes = ['T_PRIVATE', 'T_PROTECTED', 'T_PUBLIC', 'T_ABSTRACT', 'T_STATIC', 'T_FINAL'];

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return int[]
     */
    public function register()
    {
        return [T_FUNCTION];
    }

    /**
     * Checks for the existence of a method docblock.
     *
     * @param PHP_CodeSniffer_File $sniffedFile
     * @param int                  $indexOfFunctionToken
     *
     * @return bool
     */
    protected function hasMethodDocBlock(PHP_CodeSniffer_File $sniffedFile, $indexOfFunctionToken)
    {
        $positionOfClosingDocBlock = $sniffedFile->findPrevious([T_DOC_COMMENT_CLOSE_TAG], $indexOfFunctionToken);
        if ($positionOfClosingDocBlock === false)
        {
            return false;
        }
        $positionAfterClosingDocBlock = $positionOfClosingDocBlock + 1;

        $hasMethodDocBlock = true;
        for ($i = $positionAfterClosingDocBlock; $i < $indexOfFunctionToken; $i++)
        {
            if (!$this->isWhitespaceToken($sniffedFile, $i) && !$this->isMethodModifierToken($sniffedFile, $i))
            {
                $hasMethodDocBlock = false;
                break;
            }
        }

        return $hasMethodDocBlock;
    }

    /**
     * Checks if the token is a whitespace.
     *
     * @param PHP_CodeSniffer_File $sniffedFile
     * @param int                  $index
     *
     * @return bool
     */
    protected function isWhitespaceToken(PHP_CodeSniffer_File $sniffedFile, $index)
    {
        return $this->isTokenOfType($sniffedFile, $index, 'T_WHITESPACE');
    }

    /**
     * Checks if the token is a linefeed.
     *
     * @param PHP_CodeSniffer_File $sniffedFile
     * @param int                  $index
     *
     * @return bool
     */
    protected function isLinefeedToken(PHP_CodeSniffer_File $sniffedFile, $index)
    {
        if (!$this->isWhitespaceToken($sniffedFile, $index))
        {
            return false;
        }

        $tokens = $sniffedFile->getTokens();

        return strpos($tokens[$index]['content'], chr(10), 0) !== false;
    }

    /**
     * Checks if the token inside a comment is a whitespace.
     *
     * @param PHP_CodeSniffer_File $sniffedFile
     * @param int                  $index
     *
     * @return bool
     */
    protected function isCommentWhitespaceToken(PHP_CodeSniffer_File $sniffedFile, $index)
    {
        return $this->isTokenOfType($sniffedFile, $index, 'T_DOC_COMMENT_WHITESPACE');
    }

    /**
     * Checks if the token is the start of a comment.
     *
     * @param PHP_CodeSniffer_File $sniffedFile
     * @param int                  $index
     *
     * @return bool
     */
    protected function isCommentStartToken(PHP_CodeSniffer_File $sniffedFile, $index)
    {
        return $this->isTokenOfType($sniffedFile, $index, 'T_DOC_COMMENT_STAR');
    }

    /**
     * Checks if the token inside a comment is text.
     *
     * @param PHP_CodeSniffer_File $sniffedFile
     * @param int                  $index
     *
     * @return bool
     */
    protected function isCommentTextToken(PHP_CodeSniffer_File $sniffedFile, $index)
    {
        return $this->isTokenOfType($sniffedFile, $index, 'T_DOC_COMMENT_STRING');
    }

    /**
     * Checks if the token is a test annotation.
     *
     * @param PHP_CodeSniffer_File $sniffedFile
     * @param int                  $index
     *
     * @return bool
     */
    protected function isTestTagToken(PHP_CodeSniffer_File $sniffedFile, $index)
    {
        $tokens = $sniffedFile->getTokens();

        return $this->isTokenOfType($sniffedFile, $index, 'T_DOC_COMMENT_TAG') && $tokens[$index]['content'] === '@test';
    }

    /**
     * Checks if the token is an instance of $type.
     *
     * @param PHP_CodeSniffer_File $sniffedFile
     * @param int                  $index
     * @param string               $type
     *
     * @return bool
     */
    private function isTokenOfType(PHP_CodeSniffer_File $sniffedFile, $index, $type)
    {
        $tokens = $sniffedFile->getTokens();

        return $tokens[$index]['type'] === $type;
    }

    /**
     * Checks if the token describes the method visibility or if it is declared as abstract, static or final.
     *
     * @param PHP_CodeSniffer_File $sniffedFile
     * @param int                  $index
     *
     * @return bool
     */
    protected function isMethodModifierToken(PHP_CodeSniffer_File $sniffedFile, $index)
    {
        $tokens = $sniffedFile->getTokens();
        $type   = $tokens[$index]['type'];

        return in_array($type, $this->modifierTokenTypes, true);
    }

    /**
     * Checks if the token in this method is marked as a test.
     *
     * @param PHP_CodeSniffer_File $sniffedFile
     * @param int                  $index
     *
     * @return bool
     */
    protected function isTestMethod(PHP_CodeSniffer_File $sniffedFile, $index)
    {
        $indexOfOpeningDocBlock = $sniffedFile->findPrevious([T_DOC_COMMENT_OPEN_TAG], $index);
        $indexOfClosingDocBlock = $sniffedFile->findPrevious([T_DOC_COMMENT_CLOSE_TAG], $index);

        $isTest = false;
        for ($i = $indexOfOpeningDocBlock + 1; $i < $indexOfClosingDocBlock; $i++)
        {
            if ($this->isTestTagToken($sniffedFile, $i))
            {
                $isTest = true;
                break;
            }
        }

        return $isTest;
    }

    /**
     * Checks if the token in this method is a data provider.
     *
     * @param string $methodName
     *
     * @return bool
     */
    protected function methodIsDataProvider($methodName)
    {
        return preg_match('/DataProvider$/', $methodName) === 1;
    }

    /**
     * Checks if the token in this method is an accessor (getter / setter / injector).
     *
     * @param string $methodName
     *
     * @return bool
     */
    protected function methodIsAccessor($methodName)
    {
        return preg_match('/^(get|set|inject)[A-Z]/', $methodName) === 1;
    }

    /**
     * Adds a warning.
     *
     * @param PHP_CodeSniffer_File $sniffedFile
     * @param int                  $index
     * @param string               $errorMessage
     *
     * @return void
     */
    protected function addWarning(PHP_CodeSniffer_File $sniffedFile, $index, $errorMessage)
    {
        $sniffedFile->addWarning($errorMessage, $index, 'MethodDocBlock');
    }
}