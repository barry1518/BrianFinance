<?php
/**
 * System service
 *
 * @package System
 * @author Techbanx
 * @version 1.0.0
 */
namespace System;

use Techbanx\{
	Service,
	ServiceInterface
};

use System\Api\Generator\{
	ModelBuilder,
	Utils
};

class System extends Service implements ServiceInterface
{

    /*** START INTERFACE *************************************************
     * Mandatory method from Service Interface
     *********************************************************************/
    public function init(): array{
        //this function is automatically call when you make a doAction
        //so if you have some stuffs to initialize ............

        return [
            'hello' => [
                'methods'   => ['GET'],
                'get'       => [],
                'acl'       => ['public']
            ],
			'version' => [
				'methods'   => ['GET'],
				'get'       => [],
				'acl'       => ['public']
			],
			'phpInfo' => [
				'methods'   => ['GET'],
				'get'       => [],
				'acl'       => ['public']
			],
			'clearCache' => [
				'methods'   => ['GET'],
				'get'       => [],
				'acl'       => ['public']
			],
			'whois' => [
				'methods'   => ['GET'],
				'get'       => [],
				'acl'       => ['public']
			],
			'config' => [
				'methods'   => ['GET'],
				'get'       => [],
				'acl'       => ['public']
			],
			'routing' => [
				'methods'   => ['GET'],
				'get'       => [],
				'acl'       => ['public']
			],
			'services' => [
				'methods'   => ['GET'],
				'get'       => [],
				'acl'       => ['public']
			],
			'generateModel' => [
				'methods'   => ['POST'],
				'post'      => 'GenerateModel',
				'acl'       => ['public']
			],
			'generateAllBruteForce' => [
				'methods'   => ['GET'],
				'get'       => ['confirm'=>'alpha'],
				'acl'       => ['public']
			],
			'cleanFolderGenerated' => [
				'methods'   => ['GET'],
				'get'       => [],
				'acl'       => ['public']
			]
        ];
    }
    /*** END INTERFACE ***************************************************/
	/**
	 * This function return hello world
	 */
    public function hello(){
        $this->returnJson(200,'Hello World!');
    }

	/**
	 * This function return the version of the Zephir framework
	 */
	public function version(){
		$this->returnJson(200,'Framework version '.$this->core->getVersion());
	}

	/**
	 * This function return the php info
	 */
	public function phpInfo() {
		$r['php'] = phpversion();
		$r['modules'] = get_loaded_extensions();
		$this->returnJson(200, $r);
	}

	/**
	 * This function clears the Cache of the framework, useful to reinstall
	 */
	public function clearCache(){
		$this->core->flush();
		$this->returnJson(200,'Framework cache flushed!');
	}

	/**
	 * This function return the whois
	 */
	public function whois() {
		$this->returnJson(200, $this->core->whois);
	}

	/**
	 * This function return the configuration parameters
	 */
	public function config() {
		$this->returnJson(200, $this->core->getConfig());
	}

	/**
	 * This function return the routing table
	 */
	public function routing() {
		$this->returnJson(200, $this->core->getRouting());
	}

	/**
	 * This function return the services installed
	 */
	public function services() {
		$this->returnJson(200, $this->core->getServices());
	}
	/**
	 * This function generates a Model
	 * In the case where the argument table_name is equal to 'All'
	 * It generated all the models of the database
	 *
	 * @param string $database Name of the database in the application
	 * @param string $table_name Name of the table in the database
	 * @return string Message for the user: Number of models created
	 */
	public function generateModel($database = "", $table_name = ""){
		if($this->isPost()) {
			$database     = $this->uri->params['database'];
			$table_name   = $this->uri->params['table_name'];
		}

		$modelConfig  = Utils::getDatabaseConfig('Model', $database);
		$databaseName = Utils::getDatabaseName('Model', $database);
		$cpt = 0;

		if($table_name == 'All') {
			$tablesList = Utils::getDatabaseTables($database);
			foreach($tablesList as $table) {
				$model = new ModelBuilder($databaseName,
					$table->name,
					$modelConfig->dbConnectionName,
					$modelConfig->namespace,
					$modelConfig->extends,
					$modelConfig->pathGenerated,
					['version' => $modelConfig->version]);

				$cpt += $model->build();
			}
		} else {
			$model = new ModelBuilder($databaseName,
				$table_name,
				$modelConfig->dbConnectionName,
				$modelConfig->namespace,
				$modelConfig->extends,
				$modelConfig->pathGenerated,
				['version' => $modelConfig->version]);

			$cpt += $model->build();
		}

		$msg = "Db $database: $cpt Model(s) '$table_name' generated successfully!";
		if($this->uri->http === 'post') {
			$this->returnJson(200, $msg);
		} else {
			return $msg;
		}
	}

	/**
	 * This function generates all the models and collections from all the databases
	 * found in the Storage/Db config file
	 *
	 */
	public function generateAllBruteForce() {
		if($this->uri->params['confirm'] === 'ChuckNorris') {
			$dbList = Utils::getDatabasesList();
			$msg = [];
			foreach ($dbList as $db) {
				$msg [] = $this->{"generate" . $db['type']}($db['name'], 'All');
			}
			$this->returnJson(200, ['list' => $msg]);
		} else {
			$this->returnJson(401, 'Epic Fail (.com)');
		}
	}

	/**
	 * This function cleans all the folders where the models and collections
	 * will be generated. It means destroy all the files from the last
	 * generation of models/collections
	 *
	 */
	public function cleanFolderGenerated() {
		$folders = Utils::getStoragePathGeneratedList();
		$cpt = 0;
		foreach ($folders as $folder) {
			$cpt += Utils::cleanFolder($folder, true);
		}
		$this->returnJson(200, "$cpt folder(s) cleaned!");
	}
}