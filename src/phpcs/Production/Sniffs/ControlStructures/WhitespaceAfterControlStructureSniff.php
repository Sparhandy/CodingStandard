<?php
/**
 * Prüfen, ob eine Leerstelle nach einer Kontrollstuktur existiert.
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
     * Returns an array of tokens this test wants to listen for.
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
     * Processes this sniff when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPointer The position of the current token in the stack passed in $tokens
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
        $type  = 'Kein Whitespace nach Kontrollstruktor';
        $data  = [$tokens[$stackPointer]['content']];
        $error = 'Nach ' . $tokens[$stackPointer]['content'] . ' muss ein Leerzeichen stehen.';
        $phpcsFile->addWarning($error, $stackPointer, $type, $data);
    }
}