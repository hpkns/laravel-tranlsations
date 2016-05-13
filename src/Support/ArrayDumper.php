<?php

namespace Hpkns\Translations\Support;

class ArrayDumper
{
    /**
     * Dump an array to a file.
     *
     * @param  array  $array
     * @param  string $path
     * @return void
     */
    public function dumpToFile(array $array, $path)
    {
        file_put_contents(
            $path,
            $this->prettify($array)
        );
    }

    public function prettify(array $array)
    {
        $code = "<?php\n\nreturn " . var_export($array, true) . ';';
        $tokens = token_get_all($code);

        $replacements = $this->getReplacements($tokens);

        $offsetChange = 0;
        foreach ($replacements as $replacement) {
            $code = substr_replace(
                $code,
                $replacement['string'],
                $replacement['start'] + $offsetChange,
                $replacement['end'] - $replacement['start']
            );
            $offsetChange += strlen($replacement['string']) - ($replacement['end'] - $replacement['start']);
        }

        return $code;
    }

    public function getReplacements($tokens)
    {
        $replacements = [];
        $offset = 0;

        foreach ($tokens as $i => $token) {
            // Keep track of our position.
            $offset += strlen(is_array($token) ? $token[1] : $token);

            if (is_array($token) && $token[0] === T_ARRAY) {

                // T_ARRAY could either mean the "array(...)" syntax we're looking for
                // or a type hinting statement ("function(array $foo) { ... }")
                // Look for a subsequent opening bracket ("(") to be sure we're actually
                // looking at an "array(...)" statement
                $isArraySyntax = false;
                $subOffset = $offset;
                for ($j = $i + 1; $j < count($tokens); ++$j) {
                    $subOffset += strlen(is_array($tokens[$j]) ? $tokens[$j][1] : $tokens[$j]);
                    if (is_string($tokens[$j]) && $tokens[$j] == '(') {
                        $isArraySyntax = true;
                        break;
                    } elseif (!is_array($tokens[$j]) || $tokens[$j][0] !== T_WHITESPACE) {
                        $isArraySyntax = false;
                        break;
                    }
                }

                if (! $isArraySyntax) {
                    continue;
                }

                // Replace "array" and the opening bracket (including preceeding whitespace) with "["
                $replacements[] = [
                    'start' => $offset - strlen($tokens[$i][1]),
                    'end' => $subOffset,
                    'string' => '[',
                ];
                // Look for matching closing bracket (")")
                $subOffset = $offset;
                $openBracketsCount = 0;
                for ($j = $i + 1; $j < count($tokens); ++$j) {
                    $subOffset += strlen(is_array($tokens[$j]) ? $tokens[$j][1] : $tokens[$j]);
                    if (is_string($tokens[$j]) && $tokens[$j] == '(') {
                        ++$openBracketsCount;
                    } elseif (is_string($tokens[$j]) && $tokens[$j] == ')') {
                        --$openBracketsCount;
                        if ($openBracketsCount == 0) {
                            // Replace ")" with "]"
                            $replacements[] = array(
                                'start' => $subOffset - 1,
                                'end' => $subOffset,
                                'string' => ']',
                            );
                            break;
                        }
                    }
                }
            }
        }

        return $replacements;
    }
}
