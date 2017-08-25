<?php
/**
 * Checks if the opening php tag is used the way we want it to.
 *
 * @author Andreas Mirl <andreas.mirl@sh.de>
 */
class Production_Sniffs_Classes_OpenPHPTagSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        return [
            T_OPEN_TAG,
            T_OPEN_TAG_WITH_ECHO,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPointer)
    {
        $tokens  = $phpcsFile->getTokens();
        $openTag = $tokens[$stackPointer];

        if ($openTag['content'] === '<?' || $openTag['content'] === '<?=')
        {
            $error = 'Short PHP opening tag used; expected "<?php" but found "%s"';
            $data  = [$openTag['content']];
            $phpcsFile->addError($error, $stackPointer, 'Found', $data);
            $phpcsFile->recordMetric($stackPointer, 'PHP short open tag used', 'yes');
        }
        else
        {
            $phpcsFile->recordMetric($stackPointer, 'PHP short open tag used', 'no');
        }

        if ($openTag['code'] === T_OPEN_TAG_WITH_ECHO)
        {
            $nextVar = $tokens[$phpcsFile->findNext(PHP_CodeSniffer_Tokens::$emptyTokens, ($stackPointer + 1), null, true)];
            $error   = 'Short PHP opening tag used with echo; expected "<?php echo %s ..." but found "%s %s ..."';
            $data    = [
                $nextVar['content'],
                $openTag['content'],
                $nextVar['content'],
            ];
            $phpcsFile->addError($error, $stackPointer, 'EchoFound', $data);
        }
    }
}