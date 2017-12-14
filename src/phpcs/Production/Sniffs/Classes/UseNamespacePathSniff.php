<?php
namespace Sparhandy\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks for fully qualified namespaces in @var, @param, @return and @throws annotations.
 *
 * @author Christian Klatt <christian.klatt@sh.de>
 * @author Andreas Mirl <andreas.mirl@sh.de>
 * @author Sebastian Knott <sebastian@sebastianknott.de>
 */
class UseNamespacePathSniff implements Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var string[]
     */
    public $supportedTokenizers = ['PHP'];

    /** @var string[] */
    private $usePaths = [];

    /** @var string[] */
    private $wantedAnnotations = [
        '@var',
        '@param',
        '@throws',
        '@return',
    ];

    /** @var File */
    private $file = null;

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public function register()
    {
        return [
            T_DOC_COMMENT_TAG,
            T_DOC_COMMENT_STRING,
            T_USE,
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
        $this->file      = $phpcsFile;
        $tokens          = $phpcsFile->getTokens();
        $type            = $tokens[$stackPointer]['type'];
        $commentContext  = $tokens[$stackPointer]['content'];
        $isAtStartOfLine = $tokens[$stackPointer]['column'] === 1;

        if (($type === 'T_USE') && $isAtStartOfLine)
        {
            $this->collectUsedNamespace($tokens, $stackPointer);
        }
        elseif ($type === 'T_DOC_COMMENT_TAG' && in_array($commentContext, $this->wantedAnnotations, true))
        {
            $this->findFullyQualifiedNameSpaceInDocBlock($tokens, $stackPointer, $commentContext);
        }
    }

    /**
     * Removes variable names in comments.
     *
     * @param string $commentContent
     *
     * @return string
     */
    private function deleteVariableName($commentContent)
    {
        $splitCommentContent = explode(' ', $commentContent);
        $commentContent      = $splitCommentContent[0];

        return $commentContent;
    }

    /**
     * Collects all namespaces in use statements.
     *
     * @param string[][] $tokens
     * @param int        $stackPointer
     *
     * @return void
     */
    private function collectUsedNamespace(array $tokens, $stackPointer)
    {
        $namespace = '';
        $next      = 2;
        do
        {
            $type = $tokens[$stackPointer + $next]['type'];
            if ($type === 'T_STRING' || $type === 'T_NS_SEPARATOR')
            {
                $namespace .= $tokens[$stackPointer + $next]['content'];
            }
            $next++;
        } while ($tokens[$stackPointer]['line'] === $tokens[$stackPointer + $next]['line']);

        if ($namespace === '')
        {
            $type  = 'Use without namespace found.';
            $data  = [$tokens[$stackPointer]['content']];
            $error = 'Use without qualified namespace found.';
            $this->file->addWarning($error, $stackPointer, $type, $data);
        }
        else
        {
            $this->usePaths[] = trim($namespace);
        }
    }

    /**
     * Looks for fully qualified namespaces in docblock comments. If there is a match, it will be marked as warning.
     *
     * @param string[][] $tokens
     * @param int        $stackPointer
     * @param string     $commentContext
     *
     * @return void
     */
    private function findFullyQualifiedNameSpaceInDocBlock(array $tokens, $stackPointer, $commentContext)
    {
        $compareContext = $tokens[$stackPointer + 2]['content'];
        $commentContent = substr($tokens[$stackPointer + 2]['content'], 1);
        $commentContent = $this->deleteVariableName($commentContent);
        if (preg_match('/.*\\\\+.*/', $compareContext) !== 0)
        {
            if (!in_array($commentContent, $this->usePaths, true))
            {
                $type  = 'Production.UseNamespacePath.NoClassImportFound';
                $data  = [$tokens[$stackPointer + 2]['content']];
                $error = 'Missing use statement for this type.';
                $this->file->addWarning($error, $stackPointer, $type, $data);
            }

            $type  = 'Production.UseNamespacePath.FullQualifiedNamespaceFound';
            $data  = [$tokens[$stackPointer + 2]['content']];
            $error = 'Full qualified namespace in ' . $commentContext . ' annotation.';
            $this->file->addWarning($error, $stackPointer, $type, $data);
        }
    }
}