<?php
namespace Sparhandy\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks for missing default values of class properties.
 *
 * @author Andreas Mirl <andreas.mirl@sh.de>
 * @author Sebastian Knott <sebastian.knott@sh.de>
 */
class MemberDefaultPresentSniff implements Sniff
{
    /** @var string[] */
    private $validValueTypes = [
        'T_CONSTANT_ENCAPSED_STRING',
        'T_ARRAY',
        'T_FALSE',
        'T_TRUE',
        'T_DNUMBER',
        'T_LNUMBER',
    ];

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public function register()
    {
        return [
            T_PUBLIC,
            T_PRIVATE,
            T_PROTECTED,
            T_STATIC,
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

        $memberCandidate    = $tokens[$stackPointer + 2];
        $semicolonCandidate = $tokens[$stackPointer + 3];
        $equalsCandidate    = $tokens[$stackPointer + 4];
        $valueCandidate     = $tokens[$stackPointer + 6];

        $isMemberVariable            = $memberCandidate['type'] === 'T_VARIABLE';
        $memberVariableWithSemicolon = $semicolonCandidate['type'] === 'T_SEMICOLON';
        $memberVariableWithEquals    = $equalsCandidate['type'] === 'T_EQUALS';
        $isValidValueType            = in_array($valueCandidate['type'], $this->validValueTypes, true);

        if ($isMemberVariable && $memberVariableWithSemicolon && !$memberVariableWithEquals && !$isValidValueType)
        {
            $type  = 'Production.MemberDefaultPresent.MembervariableWithoutDefaultValue';
            $data  = $memberCandidate['content'];
            $error = 'Membervariable ' . $memberCandidate['content'] . ' without default value';
            $phpcsFile->addWarning($error, $stackPointer, $type, $data);
        }
    }
}