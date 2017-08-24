<?php

/**
 * Checks for missing member defaults.
 *
 * @author Andreas Mirl <andreas.mirl@sh.de>
 */
class Production_Sniffs_Classes_MemberDefaultPresentSniff implements PHP_CodeSniffer_Sniff
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
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPointer)
    {
        $tokens = $phpcsFile->getTokens();

        $memberCandidate = $tokens[$stackPointer + 2];
        $semicolonCandidate = $tokens[$stackPointer + 3];
        $equalsCandidate = $tokens[$stackPointer + 4];
        $valueCandidate = $tokens[$stackPointer + 6];

        $isMemberVariable = $memberCandidate['type'] === 'T_VARIABLE';
        $memberVariableWithSemicolon = $semicolonCandidate['type'] === 'T_SEMICOLON';
        $memberVariableWithEquals = $equalsCandidate['type'] === 'T_EQUALS';
        $isValidValueType = in_array($valueCandidate['type'], $this->validValueTypes, true);

        if ($isMemberVariable && $memberVariableWithSemicolon && !$memberVariableWithEquals && !$isValidValueType)
        {
            $type = 'Member variable without default value';
            $data = $memberCandidate['content'];
            $error = 'Member variable ' . $memberCandidate['content'] . ' without default value';
            $phpcsFile->addWarning($error, $stackPointer, $type, $data);
        }
    }
}