<?php
/**
 * Prüft auf vollständig qualifizierte Namespace-Namen in der "@var, @param, @return und @throws" Annotation.
 *
 * @author Christian Klatt <christian.klatt@sh.de>
 * @author Andreas Mirl <andreas.mirl@sh.de>
 */
class Production_Sniffs_Classes_UseNamespacePathSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var string[]
     */
    public $supportedTokenizers = ['PHP'];

    /**
     * @var string[]
     */
    private $usePaths = [];

    /**
     * @var string[]
     */
    private $wantedAnnotations = [
        '@var',
        '@param',
        '@throws',
        '@return',
    ];

    /**
     * @var PHP_CodeSniffer_File
     */
    private $file = null;

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return int[]
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
     * Processes this sniff when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPointer The position of the current token in the stack passed in $tokens
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPointer)
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
     * Entfernt den Variablenbezeichner in den Kommentarstrings.
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
     * Sammelt alle Namespaces in der Use-Deklaration zum Vergleich.
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
     * Sucht nach voll-qualifizierten-Namespaces in den DocBlock-Kommentaren. Bei Erfolg werden diese als Warnung markiert.
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
        if (preg_match('/.*\\\\+.*/', $compareContext, $matches) !== 0)
        {
            if (!in_array($commentContent, $this->usePaths, true))
            {
                $type  = 'No class import found.';
                $data  = [$tokens[$stackPointer + 2]['content']];
                $error = 'Missing use statement for this type.';
                $this->file->addWarning($error, $stackPointer, $type, $data);
            }

            $type  = 'Full qualified namespace found.';
            $data  = [$tokens[$stackPointer + 2]['content']];
            $error = 'Full qualified namespace in ' . $commentContext . ' annotation.';
            $this->file->addWarning($error, $stackPointer, $type, $data);
        }
    }
}