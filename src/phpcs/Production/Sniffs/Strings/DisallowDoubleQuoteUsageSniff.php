<?php
namespace Sparhandy\Sniffs\Strings;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Prevent usage of double quotes in favour of single quotes combined with CR, LF, CRLF etc constants.
 *
 * @author Jens von der Heydt <jens.heydt@ppw.de>
 * @author Sebastian Knott <sebastian@sebastianknott.de>
 */
class DisallowDoubleQuoteUsageSniff implements Sniff
{
    /** @var string[] */
    private $specialChars = ['\n', '\r', '\f', '\t'];

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public function register()
    {
        return [
            T_CONSTANT_ENCAPSED_STRING,
            T_DOUBLE_QUOTED_STRING,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @param File $phpcsFile
     * @param int  $stackPointer
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPointer)
    {
        $tokens = $phpcsFile->getTokens();
        // We are only interested in the first token in a multi-line string.
        if ($tokens[$stackPointer]['code'] === $tokens[$stackPointer - 1]['code'])
        {
            return;
        }

        $workingString     = $tokens[$stackPointer]['content'];
        $nextTokenPosition = $stackPointer + 1;
        while ($tokens[$nextTokenPosition]['code'] === $tokens[$stackPointer]['code'])
        {
            $workingString .= $tokens[$nextTokenPosition]['content'];
            $nextTokenPosition++;
        }

        // Check if it's a double quoted string.
        // Also make sure it's not a part of a string started in a previous line.
        // If it is, then we have already checked it.
        if (strpos($workingString, '"') === false || $workingString[0] !== '"')
        {
            return;
        }

        // The use of variables in double quoted strings is not allowed.
        if ($tokens[$stackPointer]['code'] === T_DOUBLE_QUOTED_STRING)
        {
            $this->handleDoubleQuotedString($phpcsFile, $stackPointer, $workingString);
        }

        $this->handleSpecialChars($phpcsFile, $stackPointer, $workingString);
    }

    /**
     * This method adds errors if double quoted strings contain variables.
     *
     * @param File   $phpcsFile
     * @param int    $stackPointer
     * @param string $workingString
     *
     * @return void
     */
    protected function handleDoubleQuotedString(File $phpcsFile, $stackPointer, $workingString)
    {
        $stringTokens = token_get_all('<?php ' . $workingString);
        foreach ($stringTokens as $token)
        {
            if (is_array($token) && $token[0] === T_VARIABLE)
            {
                $error = 'Variable "%s" not allowed in double quoted string; use concatenation and single quotes instead';
                $data  = [$token[1]];
                $phpcsFile->addError($error, $stackPointer, 'Prduction.DisallowDoubleQuoteUsage.ContainsVar', $data);
            }
        }
    }

    /**
     * This method adds errors if double quoted strings contain control characters like \n.
     *
     * @param File   $phpcsFile
     * @param int    $stackPointer
     * @param string $workingString
     *
     * @return void
     */
    protected function handleSpecialChars(File $phpcsFile, $stackPointer, $workingString)
    {
        foreach ($this->specialChars as $testChar)
        {
            if (strpos($workingString, $testChar) !== false)
            {
                $error    = 'Please use constants (LF, CR, CRLF, ...) using concatenation instead of "%s" in double quotes';
                $errorMsg = sprintf($error, $testChar);
                $phpcsFile->addError($errorMsg, $stackPointer, 'Prduction.DisallowDoubleQuoteUsage.ContainsVar');
            }
        }
    }
}