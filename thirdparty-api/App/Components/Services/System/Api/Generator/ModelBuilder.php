<?php

/**
 * This class generates the code of a Model Class and store it
 * into the dedicated folder of the application
 *
 * @package System\Api\Generator
 * @author gc@techbanx.com
 * @version 1.0.0
 */
namespace System\Api\Generator;

use System\Api\Generator\Utils as Utils;
use System\Api\Generator\Snippet as Snippet;

class ModelBuilder
{
    /**
     * Name of the database
     *
     * @var String
     */
    public $databaseName;

    /**
     * Name of the table in the database
     * @var String
     */
    public $name;

    /**
     * Name of the model class
     * @var String
     */
    public $className;

    /**
     * Database Connection Name (LMS)
     * @var String
     */
    public $dbConnectionName;

    /**
     * Namespace to use in the model class
     * @var String
     */
    public $namespace;

    /**
     * Class to extends for model class
     * @var String
     */
    public $extends;

    /**
     * Directory to generate the model file
     * @var String
     */
    public $modelsDir;

    /**
     * Full path for the model file (/path/modelName.php)
     * @var String
     */
    public $modelPath;

    /**
     * Miscellaneous Options
     * @var array
     */
    public $options = Array();

    /**
     * ModelBuilder constructor.
     *
     * @param string $databaseName Name of the database (ex: loan_system_new)
     * @param string $name Name of the table
     * @param string $dbConnectionName Name of the connection to the database
     * @param string $namespace Namespace to use for the collection class
     * @param string $extends Class to extends for collection class
     * @param string $modelsDir Directory to generate the model file
     * @param array  $options We can specify more parameters in this variable
     */
    public function __construct($databaseName, $name, $dbConnectionName, $namespace, $extends,
                                $modelsDir, array $options = []) {
        $this->databaseName     = $databaseName;
        $this->name             = $name;
        $this->dbConnectionName = $dbConnectionName;
        $this->namespace        = $namespace;
        $this->extends          = $extends;
        $this->modelsDir        = DIR['application'] . $modelsDir;
        $this->options          = $options;

        $this->className = isset($this->options['className']) ? $this->options['className']
                         : Utils::camelize($this->name);
        $this->modelPath = $this->modelsDir . $this->className . '.php';

        $this->options['author'] = 'Techbanx ' . '(Via ' . Utils::getInitialAuthUser() . ' Bot)';

        // If the file exists, we delete it
        if (file_exists($this->modelPath)) {
            unlink($this->modelPath);
        }
    }

    /**
     * This method builds the Model. It means generate the code and save it
     * into a file in the specific folder
     *
     * @return bool True if the model has been generated successfully
     */
    public function build() {
        /**********************************************************************
        /******** Step 1 : We generate the documentation of the file and namespace
        /**********************************************************************/

        $fileDoc = $this->generateFileDoc();

        $codeNamespace = Snippet::getNamespace($this->namespace);

        /**********************************************************************
        /******** Step 2 : We generate the code of the class
        /**********************************************************************/

        // A class is just a set of attributes and methods
        $codeAttributes    = $this->generateCodeAttributes();
        $codeGetInitialize = $this->generateCodeInitialize();
        $codeMethod        = $this->generateCodeMethods();

        /**********************************************************************
        /******** Step 3 : Finally, we concat the code generated,
         *                 generate the class and save everything into a file
        /**********************************************************************/

        $codeClass = $codeGetInitialize . $codeAttributes . $codeMethod;

        $contentFile = Snippet::getClass($fileDoc, $codeNamespace, "",
                                         $this->className, $this->extends, $codeClass);

        return Utils::saveFile($this->modelPath, $contentFile);
    }

    /**
     * This method generate the code for the documentation of the model file
     *
     * @return string Code Generated
     */
    public function generateFileDoc() {

        $type         = Utils::getTableType($this->dbConnectionName, $this->name);
        $versionDate  = Utils::getTableVersionDate($this->dbConnectionName, $this->name);
        $nbAttributes = Count(Utils::getTableFields($this->dbConnectionName, $this->name));
        $isSoftDelete = Utils::isTableSoftDelete($this->dbConnectionName, $this->name);
        $dateGenerate = date("Y-m-d H:i:s");

        $infoDoc[] = "This class is mapped with the $type $this->name in the database";
        $infoDoc[] = "Last Alter $type: $versionDate";
        $infoDoc[] = "Number of Attributes: $nbAttributes";
        $infoDoc[] = '';
        if($isSoftDelete) {
            $infoDoc[] = "This class uses a soft delete";
        } else {
            $infoDoc[] = "This class does not use a soft delete";
        }
        $infoDoc[] = '';
        $infoDoc[] = "Auto Generated by WebTools Service: $dateGenerate";

        $fileDoc = Snippet::getFileDoc($this->className, $this->namespace,
                   $this->options['author'],
                   $this->options['version'],
                   $infoDoc);

        return $fileDoc;
    }

    /**
     * This method generate the code for the attributes of a model
     *
     * @return string Code Generated
     */
    public function generateCodeAttributes() {
        $lineCodeAttributes = [];

        $attributes = Utils::getTableFields($this->dbConnectionName, $this->name);

        foreach ($attributes as $attribute) {

            $infoAttributes = $attribute->COLUMN_TYPE . ' $'
                            . $attribute->COLUMN_NAME . ' '
                            . ($attribute->IS_NULLABLE == 'YES' ? 'Nullable ' : '')
                            . ($attribute->IS_VIRTUAL  == 'YES' ? 'Virtual '  : '');

            $lineCodeAttributes[] = Snippet::getAttribute($infoAttributes,
                                                          'public',
                                                          $attribute->COLUMN_NAME);
        }

        $codeAttributes = join('', $lineCodeAttributes);

        return $codeAttributes;
    }

    /**
     * This method generates the code for the initialize() method of a model
     *
     * @return string Code Generated
     */
    public function generateCodeInitialize() {
        // Generate call $this->init()
        $codeGetInitialize[] = Snippet::getThisMethodArgs('init',
                                                          ["'$this->dbConnectionName'",
                                                           "'$this->name'"]);
        // Generate call $this->softDelete() if necessary
        if(Utils::isTableSoftDelete($this->dbConnectionName, $this->name)) {
            $codeGetInitialize[] = Snippet::getThisMethodArgs('softDelete', []);
        }

        // Generate call $this->skipAttributes() if necessary
        if($listAttributes = Utils::getTableVirtualFields($this->dbConnectionName, $this->name)) {
            $comment = "Skips fields/columns on both INSERT/UPDATE operations";
            $codeGetInitialize[] = Snippet::getFunctionComment($comment);
            $codeGetInitialize[] = Snippet::getSkipAttributesMethod($listAttributes);
        }

        // Generate function initialize() Code
        $codeGetInitialize = Snippet::getInitializeLms($codeGetInitialize);

        return $codeGetInitialize;
    }

    /**
     * This method generates the code for all the methods of a model
     *
     * @return string Code Generated
     */
    public function generateCodeMethods() {
        $codeMethods[] = Snippet::emptyLine();
        $codeMethods[] = Snippet::getBeforeValidationOnCreate();
        $codeMethods[] = Snippet::getBeforeValidationOnUpdate();

        $codeMethods = join('', $codeMethods);

        return $codeMethods;
    }

}