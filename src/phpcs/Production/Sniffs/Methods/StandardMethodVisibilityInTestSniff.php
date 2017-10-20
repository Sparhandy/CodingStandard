<?php
namespace Sparhandy\Sniffs\Methods;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks if the test methods setUp and tearDown are declared as protected.
 *
 * @author Julian Hübner <julian.huebner@sh.de>
 * @author Andreas Mirl <andreas.mirl@sh.de>
 * @author Sebastian Knott <sebastian.knott@sh.de>
 */
class StandardMethodVisibilityInTestSniff implements Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var string[]
     */
    public $supportedTokenizers = ['PHP'];

    /** @var string[] */
    private $wantedMethods = [
        'tearDown',
        'setUp',
    ];

    /** @var string */
    const REQUIRED_MODIFIER = 'T_PROTECTED';

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return int[]
     */
    public function register()
    {
        return [
            T_FUNCTION,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPointer)
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[$stackPointer]['type'] !== 'T_FUNCTION')
        {
            return;
        }

        if (in_array($tokens[$stackPointer + 2]['content'], $this->wantedMethods, true))
        {
            if ($tokens[$stackPointer - 2]['type'] !== self::REQUIRED_MODIFIER)
            {
                $type  = 'MethodVisibilityIsNotProteced';
                $data  = [$tokens[$stackPointer]['content']];
                $error = '"Please make the ' . $tokens[$stackPointer + 2]['content'] . ' method protected.';
                $phpcsFile->addWarning($error, $stackPointer, $type, $data);
            }
        }
    }
}