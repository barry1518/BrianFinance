<?php

/**
 * This class is used to generate code with different template functions
 *
 * @package System\Api\Generator
 * @author gc@techbanx.com
 * @version 1.0.0
 */

namespace System\Api\Generator;

class Snippet
{

    public static function getClass($fileDoc, $namespace, $classDoc, $className, $extends, $content)
    {
        $template = <<<EOD
<?php
%s%s%sclass %s extends %s {
%s
}
EOD;
        return sprintf($template, $fileDoc, $namespace, $classDoc, $className, $extends, $content).PHP_EOL;
    }


    public static function getClassDoc($className, $package)
    {
        $template = "/**
 * Class %s
 * @package %s
 */
";
        return sprintf($template, $className, $package);
    }


    public static function getNamespace($namespace)
    {
        $template = "namespace %s;";

        return PHP_EOL . sprintf($template, $namespace) . PHP_EOL . PHP_EOL;
    }


    public static function getFileDoc($fileName, $package, $author, $version, array $pieces = [])
    {
        $template = "/**
 * %s.php
 * %s
 * @package %s
 * @author %s
 * @version %s
 */
";
        if(count($pieces)) {
            $comments = rtrim(join(PHP_EOL . ' * ', $pieces)) . PHP_EOL . ' * ';
        } else {
            $comments = '';
        }

        return sprintf($template, $fileName, $comments, $package, $author, $version);
    }


    public static function getAttribute($infos, $visibility, $fieldName)
    {
        $template = <<<EOD
    /**
     * @var %s
     */
    %s \$%s;
EOD;

        return PHP_EOL.sprintf($template, $infos, $visibility, $fieldName).PHP_EOL;
    }


    public static function getInitializeLms(array $pieces)
    {
        $template = <<<EOD
    /**
     * initialize the model
     */
    public function initialize()
    {
%s
    }
EOD;
        return PHP_EOL.sprintf($template, rtrim(join('', $pieces))).PHP_EOL;
    }


    public static function getInitializeCollection(array $pieces)
    {
        $template = <<<EOD
    /**
     * initialize the collection
     */
    public function initialize()
    {
%s
    }
EOD;
        return PHP_EOL.sprintf($template, rtrim(join('', $pieces))).PHP_EOL;
    }


    public static function getBeforeValidationOnCreate()
    {
        $template = <<<EOD
    public function beforeValidationOnCreate()
    {
        \$auth = \$this->getDI()->getAuth();

        //
    }
EOD;
        return PHP_EOL.sprintf($template).PHP_EOL;
    }


    public static function getBeforeValidationOnUpdate()
    {
        $template = <<<EOD
    public function beforeValidationOnUpdate()
    {
        \$auth = \$this->getDI()->getAuth();
        
        //
    }
EOD;
        return PHP_EOL.sprintf($template).PHP_EOL;
    }


    public static function getSkipAttributesMethod($attributes)
    {
        $lineAttributes = "";

        $template = "        
        \$this->skipAttributes([%s
        ]);" . PHP_EOL;

        foreach ($attributes as $attribute) {
            $lineAttributes .= "\n\t\t\t'$attribute',";
        }

        return sprintf($template, rtrim($lineAttributes, ','));
    }


    public static function getUse($class)
    {
        $template = 'use %s;';

        return sprintf($template, $class);
    }


    public static function getUseAs($class, $alias)
    {
        $template = 'use %s as %s;';

        return sprintf($template, $class, $alias);
    }


    public static function getThisMethod($method, $params)
    {
        $template = "        \$this->%s(%s);" . PHP_EOL;

        return sprintf($template, $method, '"' . $params . '"');
    }


    /**
     * This method generates the call of a method
     * Example : $this->methodName($args)
     *
     */
    public static function getThisMethodArgs($methodName, array $args)
    {
        $template = "        \$this->%s(%s);" . PHP_EOL;

        $methodArgs = rtrim(join(", ", $args),", ");
        return sprintf($template, $methodName, $methodArgs);
    }


    public static function getSimpleComment($comment)
    {
        $template = "// %s";

        return sprintf($template, $comment);
    }


    public static function getFunctionComment($comment)
    {
        $template = "        // %s";

        return sprintf($template, $comment);
    }

    public static function emptyLine()
    {
        return PHP_EOL;
    }

}
