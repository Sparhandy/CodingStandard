<?php
namespace Sparhandy\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks for whitespaces after control structures.
 *
 * @author Julian Hübner <julian.huebner@sh.de>
 * @author Andreas Mirl <andreas.mirl@sh.de>
 * @author Sebastian Knott <sebastian@sebastianknott.de>
 */
class WhitespaceAfterControlStructureSniff implements Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var string[]
     */
    public $supportedTokenizers = ['PHP'];

    /** @var string */
    const WANTED_TOKEN = 'T_WHITESPACE';

    /**
     * {@inheritdoc}
     *
     * @return int[]
     */
    public function register()
    {
        return [
            T_WHILE,
            T_FOR,
            T_FOREACH,
            T_IF,
            T_ELSE,
            T_ELSEIF,
            T_DO,
            T_TRY,
            T_CATCH,
            T_SWITCH,
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
        if ($tokens[$stackPointer + 1]['type'] === self::WANTED_TOKEN)
        {
            return;
        }
        $type  = 'Production.WhitespaceAfterControlStructure.NoWhitespaceAfterControlStructure';
        $data  = [$tokens[$stackPointer]['content']];
        $error = 'There must be a whitespace after ' . $tokens[$stackPointer]['content'] . '.';
        $phpcsFile->addWarning($error, $stackPointer, $type, $data);
    }
}