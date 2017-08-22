<?php
/**
 * Abstrakte Oberklasse für alle Sniffs, die etwas mit Methoden zu tun haben.
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
     * Prüft, ob das Methoden-Token an $index einen DocBlock hat.
     *
     * @param PHP_CodeSniffer_File $sniffedFile durchsuchte Datei
     * @param int                  $indexOfFunctionToken Position des aktuellen Tokens in der Tokens-Liste
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
     * Prüft, ob das Token an $index ein Whitespace ist.
     *
     * @param PHP_CodeSniffer_File $sniffedFile durchsuchte Datei
     * @param int                  $index Position des aktuellen Tokens in der Tokens-Liste
     *
     * @return bool
     */
    protected function isWhitespaceToken(PHP_CodeSniffer_File $sniffedFile, $index)
    {
        return $this->isTokenOfType($sniffedFile, $index, 'T_WHITESPACE');
    }

    /**
     * Prüft, ob das Token an $index ein Linefeed ist.
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
     * Prüft, ob das Token an $index Whitespace innerhalb eines Kommentars ist.
     *
     * @param PHP_CodeSniffer_File $sniffedFile durchsuchte Datei
     * @param int                  $index Position des aktuellen Tokens in der Tokens-Liste
     *
     * @return bool
     */
    protected function isCommentWhitespaceToken(PHP_CodeSniffer_File $sniffedFile, $index)
    {
        return $this->isTokenOfType($sniffedFile, $index, 'T_DOC_COMMENT_WHITESPACE');
    }

    /**
     * Prüft, ob das Token an $index der Anfang eines Kommentars ist.
     *
     * @param PHP_CodeSniffer_File $sniffedFile durchsuchte Datei
     * @param int                  $index Position des aktuellen Tokens in der Tokens-Liste
     *
     * @return bool
     */
    protected function isCommentStartToken(PHP_CodeSniffer_File $sniffedFile, $index)
    {
        return $this->isTokenOfType($sniffedFile, $index, 'T_DOC_COMMENT_STAR');
    }

    /**
     * Prüft, ob das Token an $index Text innerhalb eines Kommentars ist.
     *
     * @param PHP_CodeSniffer_File $sniffedFile durchsuchte Datei
     * @param int                  $index Position des aktuellen Tokens in der Tokens-Liste
     *
     * @return bool
     */
    protected function isCommentTextToken(PHP_CodeSniffer_File $sniffedFile, $index)
    {
        return $this->isTokenOfType($sniffedFile, $index, 'T_DOC_COMMENT_STRING');
    }

    /**
     * Prüft, ob das Token an $index eine test-Annotation ist.
     *
     * @param PHP_CodeSniffer_File $sniffedFile durchsuchte Datei
     * @param int                  $index Position des aktuellen Tokens in der Tokens-Liste
     *
     * @return bool
     */
    protected function isTestTagToken(PHP_CodeSniffer_File $sniffedFile, $index)
    {
        $tokens = $sniffedFile->getTokens();

        return $this->isTokenOfType($sniffedFile, $index, 'T_DOC_COMMENT_TAG') && $tokens[$index]['content'] === '@test';
    }

    /**
     * Prüft, ob das Token an $index von Typ $type ist.
     *
     * @param PHP_CodeSniffer_File $sniffedFile durchsuchte Datei
     * @param int                  $index Position des aktuellen Tokens in der Tokens-Liste
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
     * Prüft, ob das Token an $index ein Methode-Modifikator ist (public, protected, private, abstract, static).
     *
     * @param PHP_CodeSniffer_File $sniffedFile durchsuchte Datei
     * @param int                  $index Position des aktuellen Tokens in der Tokens-Liste
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
     * Prüft, ob die Methode an $index als Test markiert ist.
     *
     * @param PHP_CodeSniffer_File $sniffedFile durchsuchte Datei
     * @param int                  $index Position des aktuellen Tokens in der Tokens-Liste
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
     * Prüft, ob die Methode ein DataProvider ist.
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
     * Prüft, ob die Methode ein Accessor (Getter, Setter oder Injector) ist.
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
     * Erzeugt eine Warnung.
     *
     * @param PHP_CodeSniffer_File $sniffedFile durchsuchte Datei
     * @param int                  $index Position des aktuellen Tokens in der Tokens-Liste
     * @param string               $errorMessage
     *
     * @return void
     */
    protected function addWarning(PHP_CodeSniffer_File $sniffedFile, $index, $errorMessage)
    {
        $sniffedFile->addWarning($errorMessage, $index, 'MethodDocBlock');
    }
}