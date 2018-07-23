<?php
namespace PhilippWitzmann\Sniffs\Abstracts;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Abstract class to be used by sniffs relating to methods.
 *
 * @author Alexander Christmann <alexander.christmann@sh.de>
 * @author Oliver Klee <github@oliverklee.de>
 * @author Sebastian Knott <sebastian@sebastianknott.de>
 */
abstract class MethodSniff implements Sniff
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
     * @param File $sniffedFile file to be checked
     * @param int  $indexOfFunctionToken position of current token in token list
     *
     * @return bool
     */
    protected function hasMethodDocBlock(File $sniffedFile, $indexOfFunctionToken)
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
     * Checks if the token at position of $index corresponds to a whitespace character.
     *
     * @param File $sniffedFile file to be checked
     * @param int  $index position of current token in token list
     *
     * @return bool
     */
    protected function isWhitespaceToken(File $sniffedFile, $index)
    {
        return $this->isTokenOfType($sniffedFile, $index, 'T_WHITESPACE');
    }

    /**
     * Checks if the token at position of $index corresponds to a linefeed.
     *
     * @param File $sniffedFile file to be checked
     * @param int  $index position of current token in token list
     *
     * @return bool
     */
    protected function isLinefeedToken(File $sniffedFile, $index)
    {
        if (!$this->isWhitespaceToken($sniffedFile, $index))
        {
            return false;
        }

        $tokens = $sniffedFile->getTokens();

        return strpos($tokens[$index]['content'], chr(10), 0) !== false;
    }

    /**
     * Checks if the token at position of $index corresponds to a whitespace inside of a comment.
     *
     * @param File $sniffedFile file to be checked
     * @param int  $index position of current token in token list
     *
     * @return bool
     */
    protected function isCommentWhitespaceToken(File $sniffedFile, $index)
    {
        return $this->isTokenOfType($sniffedFile, $index, 'T_DOC_COMMENT_WHITESPACE');
    }

    /**
     * Checks if the token at position of $index corresponds to the start of a comment.
     *
     * @param File $sniffedFile file to be checked
     * @param int  $index position of current token in token list
     *
     * @return bool
     */
    protected function isCommentStartToken(File $sniffedFile, $index)
    {
        return $this->isTokenOfType($sniffedFile, $index, 'T_DOC_COMMENT_STAR');
    }

    /**
     * Checks if the token at position of $index inside a comment corresponds to a text.
     *
     * @param File $sniffedFile file to be checked
     * @param int  $index position of current token in token list
     *
     * @return bool
     */
    protected function isCommentTextToken(File $sniffedFile, $index)
    {
        return $this->isTokenOfType($sniffedFile, $index, 'T_DOC_COMMENT_STRING');
    }

    /**
     * Checks if the token at position of $index corresponds to a test annotation.
     *
     * @param File $sniffedFile file to be checked
     * @param int  $index position of current token in token list
     *
     * @return bool
     */
    protected function isTestTagToken(File $sniffedFile, $index)
    {
        $tokens = $sniffedFile->getTokens();

        return $this->isTokenOfType($sniffedFile, $index, 'T_DOC_COMMENT_TAG') && $tokens[$index]['content'] === '@test';
    }

    /**
     * Checks if the token at position of $index corresponds to an instance of $type.
     *
     * @param File   $sniffedFile file to be checked
     * @param int    $index position of current token in token list
     * @param string $type
     *
     * @return bool
     */
    private function isTokenOfType(File $sniffedFile, $index, $type)
    {
        $tokens = $sniffedFile->getTokens();

        return $tokens[$index]['type'] === $type;
    }

    /**
     * Checks if the token at position of $index corresponds to a method modifier.
     *
     * @param File $sniffedFile file to be checked
     * @param int  $index position of current token in token list
     *
     * @return bool
     */
    protected function isMethodModifierToken(File $sniffedFile, $index)
    {
        $tokens = $sniffedFile->getTokens();
        $type   = $tokens[$index]['type'];

        return in_array($type, $this->modifierTokenTypes, true);
    }

    /**
     * Checks if the token in this method is marked as a test.
     *
     * @param File $sniffedFile file to be checked
     * @param int  $index position of current token in token list
     *
     * @return bool
     */
    protected function isTestMethod(File $sniffedFile, $index)
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
     * Checks if the token in this method is the constructor.
     *
     * @param string $methodName
     *
     * @return bool
     */
    protected function methodIsConstructor($methodName)
    {
        return preg_match('/^__construct/m', $methodName) === 1;
    }

    /**
     * Adds a warning.
     *
     * @param File   $sniffedFile file to be checked
     * @param int    $index position of current token in token list
     * @param string $errorMessage
     *
     * @return void
     */
    protected function addWarning(File $sniffedFile, $index, $errorMessage)
    {
        $sniffedFile->addWarning($errorMessage, $index, 'Production.MethodDocBlock.Invalid');
    }
}