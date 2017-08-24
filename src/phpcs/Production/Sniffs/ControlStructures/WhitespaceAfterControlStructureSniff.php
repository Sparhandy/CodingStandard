<?php
/**
 * Checks if there is a whitespace after the control structure.
 *
 * @author Julian Hübner <julian.huebner@sh.de>
 * @author Andreas Mirl <andreas.mirl@sh.de>
 */
class Production_Sniffs_ControlStructures_WhitespaceAfterControlStructureSniff implements PHP_CodeSniffer_Sniff
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
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPointer)
    {
        $tokens = $phpcsFile->getTokens();
        if ($tokens[$stackPointer + 1]['type'] === self::WANTED_TOKEN)
        {
            return;
        }
        $type  = 'No whitespace after control structure allowed.';
        $data  = [$tokens[$stackPointer]['content']];
        $error = 'There must be a whitespace after ' . $tokens[$stackPointer]['content'] . '.';
        $phpcsFile->addWarning($error, $stackPointer, $type, $data);
    }
}