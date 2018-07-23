<?php
namespace PhilippWitzmann\Sniffs\Commenting;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\Commenting\FunctionCommentSniff as PHP_CS_FunctionCommentSniff;
use PHP_CodeSniffer\Util\Common;
use PHP_CodeSniffer\Util\Tokens;

/**
 * @author                    Safak Ozpinar <safak@gamegos.com>
 * @author                    Philipp Witzmann <philipp.witzmann@sh.de>
 * @codingStandardsIgnoreFile duplicated vendor code. Removed requiring parameter, throws comment
 * @SuppressWarnings(PHPMD) duplicated vendor code. Removed requiring parameter, throws comment
 */
class FunctionCommentSniff extends PHP_CS_FunctionCommentSniff
{
    /**
     * The current PHP version.
     *
     * @var integer
     */
    private $phpVersion = null;

    /**
     * An array of variable types for param/var we will check.
     *
     * @var string[]
     */
    public static $allowedTypes = [
        'array',
        'bool',
        'float',
        'int',
        'mixed',
        'object',
        'string',
        'resource',
        'callable',
    ];

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $find   = Tokens::$methodPrefixes;
        $find[] = T_WHITESPACE;

        $methodName = $phpcsFile->getDeclarationName($stackPtr);
        $isSpecialMethod = preg_match('/^__construct/m', $methodName) === 1
                           ||preg_match('/^__destruct/m', $methodName) === 1
                           ||preg_match('/^(get|set|inject|test)[A-Z]/', $methodName) === 1;

        if ($isSpecialMethod)
        {
            return;
        }

        $commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);
        if ($tokens[$commentEnd]['code'] === T_COMMENT)
        {
            // Inline comments might just be closing comments for
            // control structures or functions instead of function comments
            // using the wrong comment type. If there is other code on the line,
            // assume they relate to that code.
            $prev = $phpcsFile->findPrevious($find, ($commentEnd - 1), null, true);
            if ($prev !== false && $tokens[$prev]['line'] === $tokens[$commentEnd]['line'])
            {
                $commentEnd = $prev;
            }
        }

        if ($tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG
            && $tokens[$commentEnd]['code'] !== T_COMMENT
        )
        {
            $phpcsFile->addError('Missing function doc comment', $stackPtr, 'Production.Commenting.FunctionComment.Missing');
            $phpcsFile->recordMetric($stackPtr, 'Function has doc comment', 'no');

            return;
        }
        else
        {
            $phpcsFile->recordMetric($stackPtr, 'Function has doc comment', 'yes');
        }

        if ($tokens[$commentEnd]['code'] === T_COMMENT)
        {
            $phpcsFile->addError('You must use "/**" style comments for a function comment', $stackPtr, 'Production.Commenting.FunctionComment.WrongStyle');

            return;
        }

        if ($tokens[$commentEnd]['line'] !== ($tokens[$stackPtr]['line'] - 1))
        {
            $error = 'There must be no blank lines after the function comment';
            $phpcsFile->addError($error, $commentEnd, 'Production.Commenting.FunctionComment.SpacingAfter');
        }

        $commentStart = $tokens[$commentEnd]['comment_opener'];
        foreach ($tokens[$commentStart]['comment_tags'] as $tag)
        {
            if ($tokens[$tag]['content'] === '@see')
            {
                // Make sure the tag isn't empty.
                $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
                if ($string === false || $tokens[$string]['line'] !== $tokens[$tag]['line'])
                {
                    $error = 'Content missing for @see tag in function comment';
                    $phpcsFile->addError($error, $tag, 'Production.Commenting.FunctionComment.EmptySees');
                }
            }
        }

        $this->processReturn($phpcsFile, $stackPtr, $commentStart);
        $this->processThrows($phpcsFile, $stackPtr, $commentStart);
        $this->processParams($phpcsFile, $stackPtr, $commentStart);

    }//end process()

    /**
     * Process the return comment of this function comment.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile     The file being scanned.
     * @param int                         $stackPtr      The position of the current token
     *                                                   in the stack passed in $tokens.
     * @param int                         $commentStart  The position in the stack where the comment started.
     *
     * @return void
     */
    protected function processReturn(File $phpcsFile, $stackPtr, $commentStart)
    {
        $tokens = $phpcsFile->getTokens();

        // Skip constructor and destructor.
        $methodName      = $phpcsFile->getDeclarationName($stackPtr);
        $isSpecialMethod =
            ($methodName === '__construct' || $methodName === '__destruct' || $methodName === 'injectDependencies');

        $return = null;
        foreach ($tokens[$commentStart]['comment_tags'] as $tag)
        {
            if ($tokens[$tag]['content'] === '@return')
            {
                if ($return !== null)
                {
                    $error = 'Only 1 @return tag is allowed in a function comment';
                    $phpcsFile->addError($error, $tag, 'Production.Commenting.FunctionComment.DuplicateReturn');

                    return;
                }

                $return = $tag;
            }
        }

        if ($isSpecialMethod === true)
        {
            return;
        }

        if ($return !== null)
        {
            $content = $tokens[($return + 2)]['content'];
            if (empty($content) === true || $tokens[($return + 2)]['code'] !== T_DOC_COMMENT_STRING)
            {
                $error = 'Return type missing for @return tag in function comment';
                $phpcsFile->addError($error, $return, 'Production.Commenting.FunctionComment.MissingReturnType');
            }
            else
            {
                // Support both a return type and a description.
                $split =
                    preg_match('`^((?:\|?(?:array\([^\)]*\)|[\\\\a-z0-9\[\]]+))*)( .*)?`i', $content, $returnParts);
                if (isset($returnParts[1]) === false)
                {
                    return;
                }

                $returnType = $returnParts[1];

                // Check return type (can be multiple, separated by '|').
                $typeNames      = explode('|', $returnType);
                $suggestedNames = [];
                foreach ($typeNames as $i => $typeName)
                {
                    $suggestedName = Common::suggestType($typeName);
                    if (in_array($suggestedName, $suggestedNames) === false)
                    {
                        $suggestedNames[] = $suggestedName;
                    }
                }

                $suggestedType = implode('|', $suggestedNames);
                if ($returnType !== $suggestedType)
                {
                    $error = 'Expected "%s" but found "%s" for function return type';
                    $data  = [
                        $suggestedType,
                        $returnType,
                    ];
                    $fix   = $phpcsFile->addFixableError($error, $return, 'InvalidReturn', $data);
                    if ($fix === true)
                    {
                        $replacement = $suggestedType;
                        if (empty($returnParts[2]) === false)
                        {
                            $replacement .= $returnParts[2];
                        }

                        $phpcsFile->fixer->replaceToken(($return + 2), $replacement);
                        unset($replacement);
                    }
                }

                // If the return type is void, make sure there is
                // no return statement in the function.
                if ($returnType === 'void')
                {
                    if (isset($tokens[$stackPtr]['scope_closer']) === true)
                    {
                        $endToken = $tokens[$stackPtr]['scope_closer'];
                        for ($returnToken = $stackPtr; $returnToken < $endToken; $returnToken++)
                        {
                            if ($tokens[$returnToken]['code'] === T_CLOSURE
                                || $tokens[$returnToken]['code'] === T_ANON_CLASS
                            )
                            {
                                $returnToken = $tokens[$returnToken]['scope_closer'];
                                continue;
                            }

                            if ($tokens[$returnToken]['code'] === T_RETURN
                                || $tokens[$returnToken]['code'] === T_YIELD
                                || $tokens[$returnToken]['code'] === T_YIELD_FROM
                            )
                            {
                                break;
                            }
                        }

                        if ($returnToken !== $endToken)
                        {
                            // If the function is not returning anything, just
                            // exiting, then there is no problem.
                            $semicolon = $phpcsFile->findNext(T_WHITESPACE, ($returnToken + 1), null, true);
                            if ($tokens[$semicolon]['code'] !== T_SEMICOLON)
                            {
                                $error = 'Function return type is void, but function contains return statement';
                                $phpcsFile->addError($error, $return, 'Production.Commenting.FunctionComment.InvalidReturnVoid');
                            }
                        }
                    }//end if
                }
                elseif ($returnType !== 'mixed' && in_array('void', $typeNames, true) === false)
                {
                    // If return type is not void, there needs to be a return statement
                    // somewhere in the function that returns something.
                    if (isset($tokens[$stackPtr]['scope_closer']) === true)
                    {
                        $endToken = $tokens[$stackPtr]['scope_closer'];
                        for ($returnToken = $stackPtr; $returnToken < $endToken; $returnToken++)
                        {
                            if ($tokens[$returnToken]['code'] === T_CLOSURE
                                || $tokens[$returnToken]['code'] === T_ANON_CLASS
                            )
                            {
                                $returnToken = $tokens[$returnToken]['scope_closer'];
                                continue;
                            }

                            if ($tokens[$returnToken]['code'] === T_RETURN
                                || $tokens[$returnToken]['code'] === T_YIELD
                                || $tokens[$returnToken]['code'] === T_YIELD_FROM
                            )
                            {
                                break;
                            }
                        }

                        if ($returnToken === $endToken)
                        {
                            $error = 'Function return type is not void, but function has no return statement';
                            $phpcsFile->addError($error, $return, 'Production.Commenting.FunctionComment.InvalidNoReturn');
                        }
                        else
                        {
                            $semicolon = $phpcsFile->findNext(T_WHITESPACE, ($returnToken + 1), null, true);
                            if ($tokens[$semicolon]['code'] === T_SEMICOLON)
                            {
                                $error = 'Function return type is not void, but function is returning void here';
                                $phpcsFile->addError($error, $returnToken, 'Production.Commenting.FunctionComment.InvalidReturnNotVoid');
                            }
                        }
                    }//end if
                }//end if
            }//end if
        }
        else
        {
            $openCurlyBracketPosition = $phpcsFile->findPrevious([T_OPEN_CURLY_BRACKET], $commentStart);
            $tColonPosition           = $phpcsFile->findNext([T_COLON], $openCurlyBracketPosition);

            if (!$tColonPosition)
            {
                $error = 'Missing @return tag in function comment';
                $phpcsFile->addError($error, $tokens[$commentStart]['comment_closer'],
                                     'Production.Commenting.FunctionComment.MissingReturn');
            }
        }//end if

    }//end processReturn()

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     * @param int  $commentStart
     * {@inheritdoc}
     */
    public function processParams(File $phpcsFile, $stackPtr, $commentStart)
    {
        Common::$allowedTypes = array_unique(
            array_merge(
                Common::$allowedTypes,
                [
                    'int',
                    'bool',
                ]
            )
        );

        if ($this->phpVersion === null)
        {
            $this->phpVersion = Config::getConfigData('php_version');
            if ($this->phpVersion === null)
            {
                $this->phpVersion = PHP_VERSION_ID;
            }
        }

        $tokens = $phpcsFile->getTokens();

        $params  = [];
        $maxType = 0;
        $maxVar  = 0;
        foreach ($tokens[$commentStart]['comment_tags'] as $pos => $tag)
        {
            if ($tokens[$tag]['content'] !== '@param')
            {
                continue;
            }

            $type         = '';
            $typeSpace    = 0;
            $var          = '';
            $varSpace     = 0;
            $comment      = '';
            $commentLines = [];
            if ($tokens[($tag + 2)]['code'] === T_DOC_COMMENT_STRING)
            {
                $matches = [];
                preg_match('/([^$&.]+)(?:((?:\.\.\.)?(?:\$|&)[^\s]+)(?:(\s+)(.*))?)?/', $tokens[($tag +
                                                                                                 2)]['content'], $matches);

                if (empty($matches) === false)
                {
                    $typeLen   = strlen($matches[1]);
                    $type      = trim($matches[1]);
                    $typeSpace = ($typeLen - strlen($type));
                    $typeLen   = strlen($type);
                    if ($typeLen > $maxType)
                    {
                        $maxType = $typeLen;
                    }
                }

                if (isset($matches[2]) === true)
                {
                    $var    = $matches[2];
                    $varLen = strlen($var);
                    if ($varLen > $maxVar)
                    {
                        $maxVar = $varLen;
                    }
                }
                else
                {
                    $error = 'Missing parameter name';
                    $phpcsFile->addError($error, $tag, 'Production.Commenting.FunctionComment.MissingParamName');
                }//end if
            }
            else
            {
                $error = 'Missing parameter type';
                $phpcsFile->addError($error, $tag, 'Production.Commenting.FunctionComment.MissingParamType');
            }//end if

            $params[] = [
                'tag'          => $tag,
                'type'         => $type,
                'var'          => $var,
                'comment'      => $comment,
                'commentLines' => $commentLines,
                'type_space'   => $typeSpace,
                'var_space'    => $varSpace,
            ];
        }//end foreach

        $realParams  = $phpcsFile->getMethodParameters($stackPtr);
        $foundParams = [];

        // We want to use ... for all variable length arguments, so added
        // this prefix to the variable name so comparisons are easier.
        foreach ($realParams as $pos => $param)
        {
            if ($param['variable_length'] === true)
            {
                $realParams[$pos]['name'] = '...' . $realParams[$pos]['name'];
            }
        }

        foreach ($params as $pos => $param)
        {
            // If the type is empty, the whole line is empty.
            if ($param['type'] === '')
            {
                continue;
            }

            // Check the param type value.
            $typeNames          = explode('|', $param['type']);
            $suggestedTypeNames = [];

            foreach ($typeNames as $typeName)
            {
                $suggestedName        = self::suggestType($typeName);
                $suggestedTypeNames[] = $suggestedName;

                if (count($typeNames) > 1)
                {
                    continue;
                }

                // Check type hint for array and custom type.
                $suggestedTypeHint = '';
                if (strpos($suggestedName, 'array') !== false || substr($suggestedName, -2) === '[]')
                {
                    $suggestedTypeHint = 'array';
                }
                elseif (strpos($suggestedName, 'callable') !== false)
                {
                    $suggestedTypeHint = 'callable';
                }
                elseif (strpos($suggestedName, 'callback') !== false)
                {
                    $suggestedTypeHint = 'callable';
                }
                elseif (in_array($suggestedName, self::$allowedTypes, true) === false)
                {
                    $suggestedTypeHint = $suggestedName;
                }

                if ($this->phpVersion >= 70000)
                {
                    if ($suggestedName === 'string')
                    {
                        $suggestedTypeHint = 'string';
                    }
                    elseif ($suggestedName === 'int' || $suggestedName === 'integer')
                    {
                        $suggestedTypeHint = 'int';
                    }
                    elseif ($suggestedName === 'float')
                    {
                        $suggestedTypeHint = 'float';
                    }
                    elseif ($suggestedName === 'bool' || $suggestedName === 'boolean')
                    {
                        $suggestedTypeHint = 'bool';
                    }
                }

                if ($suggestedTypeHint !== '' && isset($realParams[$pos]) === true)
                {
                    $typeHint = $realParams[$pos]['type_hint'];
                    if ($typeHint === '')
                    {
                        $error = 'Type hint "%s" missing for %s';
                        $data  = [
                            $suggestedTypeHint,
                            $param['var'],
                        ];

                        $errorCode = 'TypeHintMissing';
                        if ($suggestedTypeHint === 'string'
                            || $suggestedTypeHint === 'int'
                            || $suggestedTypeHint === 'float'
                            || $suggestedTypeHint === 'bool'
                        )
                        {
                            $errorCode = 'Scalar' . $errorCode;
                        }

                        $phpcsFile->addError($error, $stackPtr, $errorCode, $data);
                    }
                    elseif ($typeHint !== substr($suggestedTypeHint, strlen($typeHint) * -1))
                    {
                        $error = 'Expected type hint "%s"; found "%s" for %s';
                        $data  = [
                            $suggestedTypeHint,
                            $typeHint,
                            $param['var'],
                        ];
                        $phpcsFile->addError($error, $stackPtr, 'IncorrectTypeHint', $data);
                    }//end if
                }
                elseif ($suggestedTypeHint === '' && isset($realParams[$pos]) === true)
                {
                    $typeHint = $realParams[$pos]['type_hint'];
                    if ($typeHint !== '')
                    {
                        $error = 'Unknown type hint "%s" found for %s';
                        $data  = [
                            $typeHint,
                            $param['var'],
                        ];
                        $phpcsFile->addError($error, $stackPtr, 'InvalidTypeHint', $data);
                    }
                }//end if
            }//end foreach

            $suggestedType = implode($suggestedTypeNames, '|');
            if ($param['type'] !== $suggestedType)
            {
                $error = 'Expected "%s" but found "%s" for parameter type';
                $data  = [
                    $suggestedType,
                    $param['type'],
                ];

                $fix =
                    $phpcsFile->addFixableError($error, $param['tag'], 'Production.Commenting.FunctionComment.IncorrectParamVarName', $data);
                if ($fix === true)
                {
                    $phpcsFile->fixer->beginChangeset();

                    $content = $suggestedType;
                    $content .= str_repeat(' ', $param['type_space']);
                    $content .= $param['var'];
                    $content .= str_repeat(' ', $param['var_space']);
                    if (isset($param['commentLines'][0]) === true)
                    {
                        $content .= $param['commentLines'][0]['comment'];
                    }

                    $phpcsFile->fixer->replaceToken(($param['tag'] + 2), $content);

                    // Fix up the indent of additional comment lines.
                    foreach ($param['commentLines'] as $lineNum => $line)
                    {
                        if ($lineNum === 0
                            || $param['commentLines'][$lineNum]['indent'] === 0
                        )
                        {
                            continue;
                        }

                        $diff      = (strlen($param['type']) - strlen($suggestedType));
                        $newIndent = ($param['commentLines'][$lineNum]['indent'] - $diff);
                        $phpcsFile->fixer->replaceToken(
                            ($param['commentLines'][$lineNum]['token'] - 1),
                            str_repeat(' ', $newIndent)
                        );
                    }

                    $phpcsFile->fixer->endChangeset();
                }//end if
            }//end if

            if ($param['var'] === '')
            {
                continue;
            }

            $foundParams[] = $param['var'];

            // Check number of spaces after the type.
            $this->checkSpacingAfterParamType($phpcsFile, $param, $maxType);

            // Make sure the param name is correct.
            if (isset($realParams[$pos]) === true)
            {
                $realName = $realParams[$pos]['name'];
                if ($realName !== $param['var'])
                {
                    $code = 'Production.Commenting.FunctionComment.ParamNameNoMatch';
                    $data = [
                        $param['var'],
                        $realName,
                    ];

                    $error = 'Doc comment for parameter %s does not match ';
                    if (strtolower($param['var']) === strtolower($realName))
                    {
                        $error .= 'case of ';
                        $code  = 'ParamNameNoCaseMatch';
                    }

                    $error .= 'actual variable name %s';

                    $phpcsFile->addError($error, $param['tag'], $code, $data);
                }
            }
            elseif (substr($param['var'], -4) !== ',...')
            {
                // We must have an extra parameter comment.
                $error = 'Superfluous parameter comment';
                $phpcsFile->addError($error, $param['tag'], 'Production.Commenting.FunctionComment.ExtraParamComment');
            }//end if

            if ($param['comment'] === '')
            {
                continue;
            }

            // Check number of spaces after the var name.
            $this->checkSpacingAfterParamName($phpcsFile, $param, $maxVar);

            // Param comments must start with a capital letter and end with the full stop.
            if (preg_match('/^(\p{Ll}|\P{L})/u', $param['comment']) === 1)
            {
                $error = 'Parameter comment must start with a capital letter';
                $phpcsFile->addError($error, $param['tag'], 'Production.Commenting.FunctionComment.ParamCommentNotCapital');
            }

            $lastChar = substr($param['comment'], -1);
            if ($lastChar !== '.')
            {
                $error = 'Parameter comment must end with a full stop';
                $phpcsFile->addError($error, $param['tag'], 'Production.Commenting.FunctionComment.ParamCommentFullStop');
            }
        }//end foreach

        $realNames = [];
        foreach ($realParams as $realParam)
        {
            $realNames[] = $realParam['name'];
        }

        // Report missing comments.
        $diff = array_diff($realNames, $foundParams);
        foreach ($diff as $neededParam)
        {
            $error = 'Doc comment for parameter "%s" missing';
            $data  = [$neededParam];
            $phpcsFile->addError($error, $commentStart, 'Production.Commenting.FunctionComment.MissingParamTag', $data);
        }
    }

    /**
     * Process any throw tags that this function comment has.
     *
     * @param File $phpcsFile                           The file being scanned.
     * @param int  $stackPtr                            The position of the current token
     *                                                  in the stack passed in $tokens.
     * @param int  $commentStart                        The position in the stack where the comment started.
     *
     * @return void
     */
    protected function processThrows(File $phpcsFile, $stackPtr, $commentStart)
    {
        $tokens = $phpcsFile->getTokens();

        $throws = [];
        foreach ($tokens[$commentStart]['comment_tags'] as $pos => $tag)
        {
            if ($tokens[$tag]['content'] !== '@throws')
            {
                continue;
            }

            $exception = null;
            $comment   = null;
            if ($tokens[($tag + 2)]['code'] === T_DOC_COMMENT_STRING)
            {
                $matches = [];
                preg_match('/([^\s]+)(?:\s+(.*))?/', $tokens[($tag + 2)]['content'], $matches);
                $exception = $matches[1];
                if (isset($matches[2]) === true && trim($matches[2]) !== '')
                {
                    $comment = $matches[2];
                }
            }

            if ($exception === null)
            {
                $error = 'Exception type and comment missing for @throws tag in function comment';
                $phpcsFile->addError($error, $tag, 'Production.Commenting.FunctionComment.InvalidThrows');
            }
            else
            {
                // Any strings until the next tag belong to this comment.
                if (isset($tokens[$commentStart]['comment_tags'][($pos + 1)]) === true)
                {
                    $end = $tokens[$commentStart]['comment_tags'][($pos + 1)];
                }
                else
                {
                    $end = $tokens[$commentStart]['comment_closer'];
                }

                for ($i = ($tag + 3); $i < $end; $i++)
                {
                    if ($tokens[$i]['code'] === T_DOC_COMMENT_STRING)
                    {
                        $comment .= ' ' . $tokens[$i]['content'];
                    }
                }
            }//end if
        }//end foreach

    }//end processThrows()

    /**
     * Returns a valid variable type for param/var tag.
     *
     * If type is not one of the standard type, it must be a custom type.
     * Returns the correct type name suggestion if type name is invalid.
     *
     * @param string $varType The variable type to process.
     *
     * @return string
     */
    public static function suggestType($varType)
    {
        if ($varType === '')
        {
            return '';
        }

        if (in_array($varType, self::$allowedTypes) === true)
        {
            return $varType;
        }
        else
        {
            $lowerVarType = strtolower($varType);
            switch ($lowerVarType)
            {
                case 'bool':
                    // fall-through
                case 'boolean':
                    return 'bool';
                case 'double':
                    // fall-through
                case 'real':
                    // fall-through
                case 'float':
                    return 'float';
                case 'int':
                    // fall-through
                case 'integer':
                    return 'int';
                case 'array()':
                    // fall-through
                case 'array':
                    return 'array';
            }//end switch

            if (strpos($lowerVarType, 'array(') !== false)
            {
                // Valid array declaration:
                // array, array(type), array(type1 => type2).
                $matches = [];
                $pattern = '/^array\(\s*([^\s^=^>]*)(\s*=>\s*(.*))?\s*\)/i';
                if (preg_match($pattern, $varType, $matches) !== 0)
                {
                    $type1 = '';
                    if (isset($matches[1]) === true)
                    {
                        $type1 = $matches[1];
                    }

                    $type2 = '';
                    if (isset($matches[3]) === true)
                    {
                        $type2 = $matches[3];
                    }

                    $type1 = self::suggestType($type1);
                    $type2 = self::suggestType($type2);
                    if ($type2 !== '')
                    {
                        $type2 = ' => ' . $type2;
                    }

                    return "array($type1$type2)";
                }
                else
                {
                    return 'array';
                }//end if
            }
            else
            {
                if (in_array($lowerVarType, self::$allowedTypes) === true)
                {
                    // A valid type, but not lower cased.
                    return $lowerVarType;
                }
                else
                {
                    // Must be a custom type name.
                    return $varType;
                }
            }//end if
        }//end if

    }//end suggestType()
}