<?php
/**
 * Checks if there are no linefeeds after or before opening or closing curly braces.
 *
 * @author Andreas Borisov <andreas.borisov@sh.de>
 * @author Andreas Mirl <andreas.mirl@sh.de>
 * @author Oliver Klee <github@oliverklee.de>
 */
class Production_Sniffs_Classes_EmptyLinesParenthesisSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var string[]
     */
    public $supportedTokenizers = ['PHP'];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [
            T_OPEN_CURLY_BRACKET,
            T_CLOSE_CURLY_BRACKET,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @throws UnexpectedValueException
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPointer)
    {
        $tokens  = $phpcsFile->getTokens();
        $message = '';

        switch ($tokens[$stackPointer]['type'])
        {
            case 'T_OPEN_CURLY_BRACKET':
                if ($this->hasTwoConsecutiveLinefeeds($tokens, $stackPointer + 1))
                {
                    $message = 'Empty Line found after opening curly brace in line: ' . $tokens[$stackPointer]['line'];
                }
                break;
            case 'T_CLOSE_CURLY_BRACKET':
                if ($this->hasTwoConsecutiveLinefeeds($tokens, $stackPointer - 2))
                {
                    $message = 'Empty Line found before closing curly brace in line: ' . $tokens[$stackPointer]['line'];
                }
                break;
            default:
                throw new UnexpectedValueException('Unexpected token type.', 1442416456);
        }

        if ($message !== '')
        {
            $type = 'Empty Line found';
            $data = $tokens[$stackPointer]['content'];
            $phpcsFile->addWarning($message, $stackPointer, $type, $data);
        }
    }

    /**
     * Checks for two consecutive linefeeds at the $stackPointer's position.
     *
     * @param string[] $tokens
     * @param int      $stackPointer
     *
     * @return bool
     */
    private function hasTwoConsecutiveLinefeeds(array $tokens, $stackPointer)
    {
        if ($stackPointer < 0 || !isset($tokens[$stackPointer + 1]))
        {
            return false;
        }

        $linefeed = chr(10);

        return ($tokens[$stackPointer]['content'] === $linefeed) && ($tokens[$stackPointer + 1]['content'] === $linefeed);
    }
}