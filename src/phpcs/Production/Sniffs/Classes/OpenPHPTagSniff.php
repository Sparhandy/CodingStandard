<?php
namespace PhilippWitzmann\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Checks if the opening php tag is used the way we want it to.
 *
 * @author Andreas Mirl <andreas.mirl@sh.de>
 * @author Sebastian Knott <sebastian@sebastianknott.de>
 */
class OpenPHPTagSniff implements Sniff
{
    /**
     * {@inheritdoc}
     *
     * @return string[]
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
     * @param File $phpcsFile
     * @param int  $stackPointer
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPointer)
    {
        $tokens  = $phpcsFile->getTokens();
        $openTag = $tokens[$stackPointer];

        if ($openTag['content'] === '<?' || $openTag['content'] === '<?=')
        {
            $error = 'Short PHP opening tag used; expected "<?php" but found "%s"';
            $data  = [$openTag['content']];
            $phpcsFile->addError($error, $stackPointer, 'Production.OpenPHPTag.Found', $data);
            $phpcsFile->recordMetric($stackPointer, 'PHP short open tag used', 'yes');
        }
        else
        {
            $phpcsFile->recordMetric($stackPointer, 'PHP short open tag used', 'no');
        }

        if ($openTag['code'] === T_OPEN_TAG_WITH_ECHO)
        {
            $nextVar = $tokens[$phpcsFile->findNext(Tokens::$emptyTokens, ($stackPointer + 1), null, true)];
            $error   = 'Short PHP opening tag used with echo; expected "<?php echo %s ..." but found "%s %s ..."';
            $data    = [
                $nextVar['content'],
                $openTag['content'],
                $nextVar['content'],
            ];
            $phpcsFile->addError($error, $stackPointer, 'Production.OpenPHPTag.EchoFound', $data);
        }
    }
}