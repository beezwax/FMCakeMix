<?php 
/** 
 * FMCakeMix 
 * @author Alex Gibbons alex_g@beezwax.net
 * @date 02/2009
 * 
 * Copyright (c) 2009 Alex Gibbons, Beezwax.net
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.  
 */ 



// =================================================================================
// = FX.php : required base class
// =================================================================================
// FX is a free open-source PHP class for accessing FileMaker using curl and xml
// By: Chris Hansen with Chris Adams, Gjermund Thorsen, and others
// Tested with version: 4.5.1
// Web Site: www.iviking.org
// =================================================================================

App::import('Vendor','FX', array('file' => 'FX.php'));

class DboFMCakeMix extends DataSource { 

    var $description = "FileMaker Data Source"; 

    var $_baseConfig = array ( 
        'host' => 'localhost', 
        'port' => 80,  
    ); 

	/**
	 * FileMaker column definition
	 *
	 * @var array
	 */
	var $columns = array(
		'primary_key' => array('name' => 'NUMBER'),
		'string' => array('name' => 'TEXT'),
		'text' => array('name' => 'TEXT'),
		'integer' => array('name' => 'NUMBER','formatter' => 'intval'),
		'float' => array('name' => 'NUMBER', 'formatter' => 'floatval'),
		'datetime' => array('name' => 'TIMESTAMP', 'format' => 'm/d/Y H:i:s', 'formatter' => 'date'),
		'timestamp' => array('name' => 'TIMESTAMP', 'format' => 'm/d/Y H:i:s', 'formatter' => 'date'),
		'time' => array('name' => 'TIME', 'format' => 'H:i:s', 'formatter' => 'date'),
		'date' => array('name' => 'DATE', 'format' => 'm/d/Y', 'formatter' => 'date'),
		'binary' => array('name' => 'CONTAINER'),
		'boolean' => array('name' => 'NUMBER')
	);
     
    /** 
     * Constructor 
     */ 
    function __construct($config = null) { 
        $this->debug = Configure :: read() > 0; 
        $this->fullDebug = Configure :: read() > 1;
		$this->timeFlag = getMicrotime();
		
        parent :: __construct($config); 
        return $this->connect(); 
    } 
     
    /** 
     * Destructor. Closes connection to the database. 
     */ 
    function __destruct() { 
        $this->close(); 
        parent :: __destruct(); 
    } 

     /** 
     * Connect. Creates connection handler to database 
     */
    function connect() { 
	
		// Debugger::log('fm_dbo:connect ');
        $config = $this->config; 
        $this->connected = false; 

        $this->connection = new FX($config['host'],$config['port'], $config['dataSourceType'], $config['scheme']);
        $this->connection->SetDBPassword($config['password'],$config['login']);
        
		$this->connected = true; //always returns true
        return $this->connected; 
    } 
     
    /** 
     * Close.
     */ 
    function close() { 
        if ($this->fullDebug && Configure :: read() > 1) { 
            $this->showLog(); 
        } 

		
        $this->disconnect(); 
    } 
     
    function disconnect() { 
		$this->connected = false;
        return $this->connected; 
    } 
     
    /** 
     * Checks if it's connected to the database 
     * 
     * @return boolean True if the database is connected, else false 
     */ 
    function isConnected() { 
        return $this->connected; 
    } 
     
    /** 
     * Reconnects to database server with optional new settings 
     * 
     * @param array $config An array defining the new configuration settings 
     * @return boolean True on success, false on failure 
     */ 
    function reconnect($config = null) { 
        $this->disconnect(); 
        if ($config != null) { 
            $this->config = am($this->_baseConfig, $this->config, $config); 
        } 
        return $this->connect(); 
    } 

	/** 
     * Returns properly formatted field name
     * 
     * @param array $config An array defining the new configuration settings 
     * @return boolean True on success, false on failure 
     */ 
    function name($data) { 
	
        return $data; 

    }

	/*
		TODO_ABG: needs to use recursion
		TODO_ABG: needs to handle filemakers ability to put mutliple tables on one layout
		TODO_ABG: should somehow include the ability to specify layout
	*/
    /** 
     * The "R" in CRUD 
     * 
     * @param Model $model 
     * @param array $queryData 
     * @param integer $recursive Number of levels of association 
     * @return unknown 
     */ 
    function read(& $model, $queryData = array (), $recursive = null) { 
		
		$fm_layout = $model->defaultLayout;
		$fm_database = $model->fmDatabaseName;
		$queryLimit = $queryData['limit'] == null ? 'all' : $queryData['limit'];
		$linkedModels = array();
		
		if (!is_null($recursive)) {
			$_recursive = $model->recursive;
			$model->recursive = $recursive;
		}
		
		// set connection data if Count query
		if($queryData['fields'] == 'COUNT') {
			// reset the connection parameters to return only 1 result, improves performance
			$this->connection->SetDBData($fm_database, $fm_layout, 1 );
		} else {
			// set basic connection data
			$this->connection->SetDBData($fm_database, $fm_layout, $queryLimit );
		}
		
		
		/*
			TODO_ABG : this has a junk interpretation of a logical or statement, that isn't nestable
			* it therefore turns the whole query into an or, if an or statement is injected somewhere
			* this is a major limitation of fx.php
		*/
		if(!empty($queryData['conditions'])) {
			$conditions = array(); // a clean set of queries
			$isOr = false;  // a boolean indicating wether this query is logical or
		
			foreach($queryData['conditions'] as $conditionField => $conditionValue) {
				// if a logical or statement has been pased somewhere
				if($conditionField == 'or') {
					$isOr = true;
					if(is_array($conditionValue)) {
						$conditions = array_merge($conditions, $conditionValue);
					}
				} else {
					$conditions[$conditionField] = $conditionValue;
				}
			}
			
			
			foreach($conditions as $conditionField => $conditionValue) {
				$string = $conditionField;
				if(strpos($string,'.')) {
					$stringExp = explode('.', $string);
					unset($stringExp[0]);
					$plainField = implode('.',$stringExp);
				} else {
					$plainField = $string;
				}
				
				// $pattern = '/(\w+)\.(-*\w+)$/i';
				// $replacement = '${2}';
				// $plainField = preg_replace($pattern, $replacement, $string);
				
				$this->connection->AddDBParam($plainField, $conditionValue, 'eq');
				
				//add or operator
				if($isOr){
					$this->connection->SetLogicalOR();
				}
			}
			
		}
		
		
		// // add the params
		// if(!empty($queryData['conditions'])) {
		// 	foreach($queryData['conditions'] as $conditionField => $conditionValue) {
		// 		if($conditionField == 'or') {
		// 			
		// 		}
		// 		$string = $conditionField;
		// 		$pattern = '/(\w+)\.(-*\w+)$/i';
		// 		$replacement = '${2}';
		// 		$plainField = preg_replace($pattern, $replacement, $string);
		// 		$this->connection->AddDBParam($plainField, $conditionValue, 'eq');
		// 	}
		// }
		
		// set sort order
		foreach($queryData['order'] as $orderCondition) {
			if(!empty($orderCondition)){
				foreach($orderCondition as $field => $sortRule) {
					$string = $field;
					$pattern = '/(\w+)\.(-*\w+)$/i';
					$replacement = '${2}';
					$plainField = preg_replace($pattern, $replacement, $string);
					
					$sortRuleFm = $sortRule == 'desc' ? 'descend' : 'ascend';
					$this->connection->AddSortParam($plainField, $sortRuleFm);
				}
			}
		}
		
		// set skip records if there is an offset
		if(!empty($queryData['offset'])) {
			$this->connection->FMSkipRecords($queryData['offset']);
		}
		
		
		// return a found count if requested
		if($queryData['fields'] == 'COUNT') {
			// perform find without returning result data
			$fmResults = $this->connection->FMFind(true, 'basic');
			
			// test result
			if(!$this->handleFXResult($fmResults, $model->name, 'read (count)')) {
				return FALSE;
			}
			
			$countResult = array();
			$countResult[0][0] = array('count' => $fmResults['foundCount']);
			
			// return found count
			return $countResult;
		} else {
			// perform the find in FileMaker
			$fmResults = $this->connection->FMFind();
			
			if(!$this->handleFXResult($fmResults, $model->name, 'read')) {
				return FALSE;
			}
		}
		
		
		$resultsOut = array();
		// format results
		if(!empty($fmResults['data'])) {
			$i = 0;
			foreach($fmResults['data'] as $recmodid => $recordData) {
				$relatedModels = array();
				$recmodid_Ary = explode('.', $recmodid);
				$resultsOut[$i][$model->name]['-recid'] = $recmodid_Ary[0];
				$resultsOut[$i][$model->name]['-modid'] = $recmodid_Ary[1];
				
				/*
					TODO_ABG : the commented out section below is a smart bit of code that 
					* will convert filemaker's related fields into separate models
					* following the cake format, but issues are...
					* 1) the model name it gathers from the table occurence will likely
					*    not conform to the Model name defined in cake
					* 2) it might not be necessary and it may be better to treat this related
					*    data as local to the table and do model recursion separately
				*/
				foreach($recordData as $field => $value) {
					// if $field is not a related entity
					// if(strpos($field, '::') === false) {
						// grab table field data (grabs first repitition)
						$resultsOut[$i][$model->name][$field] = $value[0];
					// } else {
					// 	$relatedModelName = preg_replace("/(\w+)::(\w+)/", "$1", $field);
					// 	// grab related data
					// 	foreach($value as $index => $relatedData) {
					// 		$relatedField = preg_replace("/(\w+)::(\w+)/", "$2", $field);
					// 		$relatedModels[$relatedModelName][$index][$relatedField] = $relatedData;
					// 	}
					// }
				}
				
				// conditionally add a pseudo model consisting of layout value lists
				if (!empty($model->returnValueListAsModel)) {
          $resultsOut[$i][$model->returnValueListAsModel] = 'ddd';
    		}
    		
				$i++;
			}
		}
		
		
		
		
		// ================================
		// = Searching for Related Models =
		// ================================
		if ($model->recursive > 0) {
			
			
			foreach ($model->__associations as $type) {
				foreach ($model->{$type} as $assoc => $assocData) {
					$linkModel =& $model->{$assoc};
					
					
					if (!in_array($type . '/' . $assoc, $linkedModels)) {
						if ($model->useDbConfig == $linkModel->useDbConfig) {
							$db =& $this;
						} else {
							$db =& ConnectionManager::getDataSource($linkModel->useDbConfig);
						}
					} elseif ($model->recursive > 1 && ($type == 'belongsTo' || $type == 'hasOne')) {
						$db =& $this;
					}
					
					if (isset($db)) {
						$stack = array($assoc);
						$db->queryAssociation($model, $linkModel, $type, $assoc, $assocData, $array, true, $resultsOut, $model->recursive - 1, $stack);
						unset($db);
					}
				}
			}
			//$this->__filterResults($resultSet, $model, $filtered);
		}
		
	
		
		if (!is_null($recursive)) {
			$model->recursive = $_recursive;
		}
		
		
		// return data
		return $resultsOut;
		
    } 

	/**
	 * Calculate
	 * currently this only returns a 'count' flag if a count is requested. This will tell
	 * the read function to return a found count rather than results
	 *
	 * @param model $model
	 * @param string $func Lowercase name of SQL function, i.e. 'count' or 'max'
	 * @param array $params Function parameters
	 * @return string flag informing read function to parse results as per special case of $func
	 * @access public
	 */

	function calculate(&$model, $func, $params = array()) {
		$params = (array)$params;
		
		switch (strtolower($func)) {
			case 'count':
				if (!isset($params[0])) {
					$params[0] = '*';
				}
				if (!isset($params[1])) {
					$params[1] = 'count';
				}
				//return 'COUNT(' . $this->name($params[0]) . ') AS ' . $this->name($params[1]);
				return 'COUNT';
			case 'max':
			case 'min':
				if (!isset($params[1])) {
					$params[1] = $params[0];
				}
				return strtoupper($func) . '(' . $this->name($params[0]) . ') AS ' . $this->name($params[1]);
			break;
		}
	}
	
	/*
		TODO_ABG: Should provide error when -recid is not passed.
	*/
	
	/**
	 * The "D" in CRUD 
	 * can only delete from the recid that is internal to filemaker
	 * We do this by using the deleteAll model method, which lets us pass conditions to the driver
	 * delete statement. This method will only work if the conditions array contains a 'recid' field
	 * and value. Also, must pass cascade value of false with the deleteAll method.
	 *
	 * @param Model $model
	 * @param array $conditions
	 * @return boolean Success
	 */
	function delete(&$model, $conditions = null) {
		
		
		$fm_layout = $model->defaultLayout;
		$fm_database = $model->fmDatabaseName;
		
		// set basic connection data
		$this->connection->SetDBData($fm_database, $fm_layout);
		
		if(is_null($conditions)) {
			$this->connection->AddDBParam('-recid', $model->getId(), 'eq');
		} else {
			// must contain a -recid field
			foreach($conditions as $field => $value) {
				$this->connection->AddDBParam($field, $value, 'eq');
			}
		}
		
		// perform deletion
		$return = $this->connection->FMDelete(TRUE);
		
		if(!$this->handleFXResult($return, $model->name, 'delete')) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
	/**
	 * The "C" in CRUD
	 *
	 * @param Model $model
	 * @param array $fields
	 * @param array $values
	 * @return boolean Success
	 */
	function create(&$model, $fields = null, $values = null) {
		$id = null;
		
		
		// if empty then use data in model
		if ($fields == null) {
			unset($fields, $values);
			$fields = array_keys($model->data);
			$values = array_values($model->data);
		}
		$count = count($fields);
		
		// get connection parameters
		$fm_layout = $model->defaultLayout;
		$fm_database = $model->fmDatabaseName;
		
		// set basic connection data
		$this->connection->SetDBData($fm_database, $fm_layout);
		
		// Debugger::log(print_r($fields,true));
		
		// if by chance the recid was passed to this create method we want
		// to make sure we remove it as filemaker will reject the request.
		if(isset($model->fm_recid) && !empty($model->fm_recid)) {
			foreach($fields as $index => $field) {
				if($field == $model->fm_recid) {
					unset($fields[$index]);
					unset($values[$index]);
				}
			}
		}
				
		foreach($fields as $index => $field) {
			$this->connection->AddDBParam($field, $values[$index]);
		}
		
		// perform creation
		
		$return = $this->connection->FMNew();
		
		if(!$this->handleFXResult($return, $model->name, 'new')) {
			return FALSE;
		}
		
		
		if($return['errorCode'] != 0) {
			return false;
		}
		
		
		
		
		// write recid to model id and __lastinsert attributes
		foreach($return['data'] as $recmodid => $returnedModel){
			$recmodid_Ary = explode('.', $recmodid);
			$model->id = $recmodid_Ary[0];
			$model->setInsertID($recmodid_Ary[0]);
		}
		
		$resultsOut = array();
		if(!empty($return['data'])) {
			foreach($return['data'] as $recmodid => $recordData) {
				$recmodid_Ary = explode('.', $recmodid);
				$resultsOut[$model->name]['-recid'] = $recmodid_Ary[0];
				$resultsOut[$model->name]['-modid'] = $recmodid_Ary[1];
				
				foreach($recordData as $field => $value) {
					$resultsOut[$model->name][$field] = $value[0];
				}
			}
		}
		
		$model->data  = $resultsOut; // this returns data on a create
		
		return true;
	}
	
	/*
		TODO_ABG: add support for -modid and transactions
	*/
	
	/**
	 * The "U" in CRUD
	 * This could be collapsed under create, for now it's separate for better debugging
	 * It's important to note that edit requires a FileMaker -recid that should be
	 * passed as a hidden form field
	 *
	 * @param Model $model
	 * @param array $fields
	 * @param array $values
	 * @param mixed $conditions
	 * @return array
	 */
	function update(&$model, $fields = array(), $values = null, $conditions = null) {
		
		
		// get connection parameters
		$fm_layout = $model->defaultLayout;
		$fm_database = $model->fmDatabaseName;
		
		if(!empty($model->id)) {
			
			// set basic connection data
			$this->connection->SetDBData($fm_database, $fm_layout);
			
			// **1 here we remove the primary key field if it's marked as readonly 
			// other fields can be removed by the controller, but cake requires
			// the primary key to be included in the query if it's to consider
			// the action an edit
			foreach($fields as $index => $field) {
				if(isset($model->primaryKeyReadOnly) && $field == $model->primaryKey) {
					unset($fields[$index]);
					unset($values[$index]);
				}
			}
			
			// ensure that a recid is passed
			if(!in_array('-recid',$fields)) {
				array_push($fields, '-recid');
				array_push($values, $model->getId());
			}
			
			// there must be a -recid field passed in here for the edit to work
			// could be passed in hidden form field
			foreach($fields as $index => $field) {
				$this->connection->AddDBParam($field, $values[$index]);
			}

			// perform edit
			$return = $this->connection->FMEdit();
			
			if(!$this->handleFXResult($return, $model->name, 'update')) {
				return FALSE;
			}
			
			
			if($return['errorCode'] != 0) {
				return false;
			} else {
				
				foreach($return['data'] as $recmodid => $returnedModel){
					$recmodid_Ary = explode('.', $recmodid);
					$model->id = $recmodid_Ary[0];
					$model->setInsertID($recmodid_Ary[0]);
				}
				
				return true;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Returns an array of the fields in given table name.
	 *
	 * @param string $model the model to inspect
	 * @return array Fields in table. Keys are name and type
	 */
	function describe(&$model) {
		
		// describe caching
		$cache = $this->__describeFromCache($model);
		if ($cache != null) {
			return $cache;
		}
		
		$fm_layout = $model->defaultLayout;
		$fm_database = $model->fmDatabaseName;
		
		// set basic connection data
		$this->connection->SetDBData($fm_database, $fm_layout);
		
		// get layout info
		$result = $this->connection->FMFindAny(true, 'basic');
		
		// check for error
		if (!$this->handleFXResult($result, $model->name, 'describe')) {
			return FALSE;
		}
		
		$fieldsOut = array();
		
		$fmFieldTypeConversion = array(
			'TEXT' => 'string',
			'DATE' => 'date',
			'TIME' => 'time',
			'TIMESTAMP' => 'timestamp',
			'NUMBER' => 'float',
			'CONTAINER' => 'binary'
		);
		
		
		foreach($result['fields'] as $field) {
			$type = $fmFieldTypeConversion[$field['type']];
			$fieldsOut[$field['name']] = array(
				'type' => $type, 		
				'null' => null, 
				'default' => null, 
				'length' => null, 
				'key' => null
			);
			
		}
		
		$fieldsOut['-recid'] = array(
			'type' => 'integer', 		
			'null' => null, 
			'default' => null, 
			'length' => null, 
			'key' => null
		);
		
		$fieldsOut['-modid'] = array(
			'type' => 'integer', 		
			'null' => null, 
			'default' => null, 
			'length' => null, 
			'key' => null
		);
		
		
		// value list handling
		if (!empty($model->returnValueLists) && $model->returnValueLists === true) {
		  $layoutObject = $this->connection->FMView();

		  foreach($layoutObject['fields'] as $field) {
		    if (!empty($field['valuelist'])) {
		      $fieldsOut[$field['name']]['valuelist'] = $layoutObject['valueLists'][$field['valuelist']];
		    }
	    } 
		}
    
		
		$this->__cacheDescription($this->fullTableName($model, false), $fieldsOut);
		return $fieldsOut;
		
		
	}
	
	/**
	 * __describeFromCache
	 * looks for and potentially returns the cached description of the model
	 * 
	 * @param $model
	 * @return the models cache description or null if none exists
	 */
	function __describeFromCache($model) {
		
		if ($this->cacheSources === false) {
			return null;
		}
		if (isset($this->__descriptions[$model->tablePrefix . $model->table])) {
			return $this->__descriptions[$model->tablePrefix . $model->table];
		}
		$cache = $this->__cacheDescription($model->tablePrefix . $model->table);

		if ($cache !== null) {
			$this->__descriptions[$model->tablePrefix . $model->table] =& $cache;
			return $cache;
		}
		return null;
	}
	
	/**
	 * __cacheDescription
	 * 
	 * @param string $object : name of model
	 * @param mixed $data : the data to be cached
	 * @return mixed : the cached data
	 */
	function __cacheDescription($object, $data = null) {
		if ($this->cacheSources === false) {
			return null;
		}

		if ($data !== null) {
			$this->__descriptions[$object] =& $data;
		}

		$key = ConnectionManager::getSourceName($this) . '_' . $object;
		$cache = Cache::read($key, '_cake_model_');
		

		if (empty($cache)) {
			$cache = $data;
			Cache::write($key, $cache, '_cake_model_');
		}

		return $cache;
	}


    /**
     * GenerateAssociationQuery
     */    
    function generateAssociationQuery(& $model, & $linkModel, $type, $association = null, $assocData = array (), & $queryData, $external = false, & $resultSet) { 
         
        //$this->__scrubQueryData($queryData); 

		//echo 'LIMIT:'.print_r($assocData['limit'],true).'<br />';
         
        switch ($type) { 
            case 'hasOne' : 
                $id = $resultSet[$model->name][$model->primaryKey]; 
                $queryData['conditions'] = trim($assocData['foreignKey']) . '=' . trim($id); 
                //$queryData['targetDn'] = $linkModel->useTable; 
                //$queryData['type'] = 'search'; 
                $queryData['limit'] = 1; 

                return $queryData; 
                 
            case 'belongsTo' : 
				
                $id = $resultSet[$model->name][$assocData['foreignKey']]; 
				$queryData['conditions'] = array(trim($linkModel->primaryKey) => trim($id));
				$queryData['order'] = array();
				$queryData['fields'] = '';
                $queryData['limit'] = 1;//$assocData['limit']; 
				
                return $queryData; 
                 
            case 'hasMany' : 
				
				// THIS ONE IS THE ONLY ONE THAT's BEEN MODIFIED
				/*
					TODO_ABG: take these changes and test for other types of relationships
				*/
				
                $id = $resultSet[$model->name][$model->primaryKey]; 
                $queryData['conditions'] = array(trim($assocData['foreignKey']) => trim($id));
				$queryData['order'] = array();
				$queryData['fields'] = '';
                //$queryData['targetDn'] = $linkModel->useTable; 
                //$queryData['type'] = 'search'; 
                $queryData['limit'] = $assocData['limit']; 

                return $queryData; 

            case 'hasAndBelongsToMany' : 
                return null; 
        } 
        return null; 
    } 

	/**
	 * QueryAssociation
	 * 
	 */
	
	/*
		TODO_ABG : missing real recursion
	*/
    function queryAssociation(& $model, & $linkModel, $type, $association, $assocData, & $queryData, $external = false, & $resultSet, $recursive, $stack) { 
        
		
		 
		foreach($resultSet as $projIndex => $row) {
			$queryData = $this->generateAssociationQuery($model, $linkModel, $type, $association, $assocData, $queryData, $external, $row);
		
			$associatedData = $this->readAssociated($linkModel, $queryData, 0);
			
			foreach($associatedData as $assocIndex => $relatedModel) {
				$modelName = key($relatedModel);
				$resultSet[$projIndex][$modelName][$assocIndex] = $relatedModel[$modelName];
			}
		}
		
		//debug($associatedData);
		
        // if (!isset ($resultSet) || !is_array($resultSet)) { 
        //     if (Configure :: read() > 0) { 
        //         e('<div style = "font: Verdana bold 12px; color: #FF0000">SQL Error in model ' . $model->name . ': '); 
        //         if (isset ($this->error) && $this->error != null) { 
        //             e($this->error); 
        //         } 
        //         e('</div>'); 
        //     } 
        //     return null; 
        // } 
        //  
        // $count = count($resultSet); 
        // for ($i = 0; $i < $count; $i++) { 
        //      
        //     $row = & $resultSet[$i]; 
        //     $queryData = $this->generateAssociationQuery($model, $linkModel, $type, $association, $assocData, $queryData, $external, $row); 
        //     $fetch = $this->_executeQuery($queryData); 
        //     $fetch = ldap_get_entries($this->connection, $fetch); 
        //     $fetch = $this->_ldapFormat($linkModel,$fetch); 
        //      
        //     if (!empty ($fetch) && is_array($fetch)) { 
        //             if ($recursive > 0) { 
        //                 foreach ($linkModel->__associations as $type1) { 
        //                     foreach ($linkModel-> {$type1 } as $assoc1 => $assocData1) { 
        //                         $deepModel = & $linkModel->{$assocData1['className']}; 
        //                         if ($deepModel->alias != $model->name) { 
        //                             $tmpStack = $stack; 
        //                             $tmpStack[] = $assoc1; 
        //                             if ($linkModel->useDbConfig == $deepModel->useDbConfig) { 
        //                                 $db = & $this; 
        //                             } else { 
        //                                 $db = & ConnectionManager :: getDataSource($deepModel->useDbConfig); 
        //                             } 
        //                             $queryData = array(); 
        //                             $db->queryAssociation($linkModel, $deepModel, $type1, $assoc1, $assocData1, $queryData, true, $fetch, $recursive -1, $tmpStack); 
        //                         } 
        //                     } 
        //                 } 
        //             } 
        //         $this->__mergeAssociation($resultSet[$i], $fetch, $association, $type); 
        // 
        //     } else { 
        //         $tempArray[0][$association] = false; 
        //         $this->__mergeAssociation($resultSet[$i], $tempArray, $association, $type); 
        //     } 
        // } 
    } 

	/** 
     * readAssociated
     * very similar to read but for related data
     * unlike read does not make a reference to the passed model
     * 
     * @param Model $model 
     * @param array $queryData 
     * @param integer $recursive Number of levels of association 
     * @return unknown 
     */ 
    function readAssociated($linkedModel, $queryData = array (), $recursive = null) { 
		
		
		$fm_layout = $linkedModel->defaultLayout;
		$fm_database = $linkedModel->fmDatabaseName;
		$queryLimit = $queryData['limit'] == null ? 'all' : $queryData['limit'];
		//$linkedModels = array();
		
		
		// set basic connection data
		$this->connection->SetDBData($fm_database, $fm_layout, $queryLimit );
		
		//debug($queryData);
		
		// add the params
		if(!empty($queryData['conditions'])) {
			
			
			foreach($queryData['conditions'] as $conditionField => $conditionValue) {
				$string = $conditionField;
				$pattern = '/(\w+)\.(-*\w+)$/i';
				$replacement = '${2}';
				$plainField = preg_replace($pattern, $replacement, $string);
				$this->connection->AddDBParam($plainField, $conditionValue, 'eq');
			}
		}
		
		// set sort order
		foreach($queryData['order'] as $orderCondition) {
			if(!empty($orderCondition)){
				foreach($orderCondition as $field => $sortRule) {
					$string = $field;
					$pattern = '/(\w+)\.(-*\w+)$/i';
					$replacement = '${2}';
					$plainField = preg_replace($pattern, $replacement, $string);
					
					$sortRuleFm = $sortRule == 'desc' ? 'descend' : 'ascend';
					$this->connection->AddSortParam($plainField, $sortRuleFm);
				}
			}
		}
		
		// set skip records if there is an offset
		if(!empty($queryData['offset'])) {
			$this->connection->FMSkipRecords($queryData['offset']);
		}
		
		// THIS MAY NOT BE NECESSARY FOR THE READASSOCIATED FUNCTION
		// return a found count if requested
		if($queryData['fields'] == 'COUNT') {
			// perform find without returning result data
			$fmResults = $this->connection->FMFind(true, 'basic');
			
			// check for error
			if(!$this->handleFXResult($fmResults, $linkedModel->name, 'readassociated (count)')) {
				return FALSE;
			}
			
			$countResult = array();
			$countResult[0][0] = array('count' => $fmResults['foundCount']);
			
			// return found count
			return $countResult;
		} else {
			// perform the find in FileMaker
			$fmResults = $this->connection->FMFind();
			
			// check for error
			if(!$this->handleFXResult($fmResults, $linkedModel->name, 'readassociated')) {
				return FALSE;
			}
		}
		
		$resultsOut = array();
		
		// format results
		if(!empty($fmResults['data'])) {
			$i = 0;
			foreach($fmResults['data'] as $recmodid => $recordData) {
				$relatedModels = array();
				$recmodid_Ary = explode('.', $recmodid);
				$resultsOut[$i][$linkedModel->name]['-recid'] = $recmodid_Ary[0];
				$resultsOut[$i][$linkedModel->name]['-modid'] = $recmodid_Ary[1];
				foreach($recordData as $field => $value) {
					// if $field is not a related entity
					if(strpos($field, '::') === false) {
						// grab table field data (grabs first repitition)
						$resultsOut[$i][$linkedModel->name][$field] = $value[0];
					} else {
					}
				}
			$i++;
			}
		} else {
			
		}
		
		return $resultsOut;
		
    }

	/**
	 * Gets full table name including prefix
	 *
	 * @param mixed $model
	 * @param boolean $quote
	 * @return string Full quoted table name
	 */
	function fullTableName($model, $quote = true) {
		if (is_object($model)) {
			$table = $model->tablePrefix . $model->table;
		} elseif (isset($this->config['prefix'])) {
			$table = $this->config['prefix'] . strval($model);
		} else {
			$table = strval($model);
		}
		if ($quote) {
			return $this->name($table);
		}
		return $table;
	}

     
    /** 
     * Returns a formatted error message from previous database operation. 
     * 
     * @return string Error message with error number 
     */ 
    function lastError() { 
        if (FX::isError($this->lastFXError)) { 
            return $this->lastFXError.getCode() . ': ' . $this->lastFXError.getMessage(); 
        } 
        return null; 
    } 

	/**
	 * handleFXResult
	 * 
	 * logs queries, logs errors, and returns false on error
	 * 
	 * @param FX result object or FX error object
	 * @param string : model name
	 * @param string : action name
	 * 
	 * @return false if result is an FX error object
	 */
	function handleFXResult($result, $modelName = 'N/A', $actionName = 'N/A') {
		
		
		//debug($result);
		
		$this->_queriesCnt++;
		
		// if a connection error
		if(FX::isError($result)) {
			
			// log error
			$this->_queriesLog[] = array(
				'model' 	=> $modelName,
				'action' 	=> $actionName,
				'query' 	=> '',
				'error'		=> $result->toString(),
				'numRows'	=> '',
				'took'		=> round((getMicrotime() - $this->timeFlag) * 1000, 0)
			);
			if (count($this->_queriesLog) > $this->_queriesLogMax) {
				array_pop($this->_queriesLog);
			}
			
			$this->timeFlag = getMicrotime();
			return FALSE;
		
		// if a filemaker error other than no records found
		} elseif ($result['errorCode'] != 0 && $result['errorCode'] != 401)	{
		
			// log error
			$this->_queriesLog[] = array(
				'model' 	=> $modelName,
				'action' 	=> $actionName,
				'query' 	=> substr($result['URL'],strrpos($result['URL'], '?')),
				'error'		=> $result['errorCode'],
				'numRows'	=> '',
				'took'		=> round((getMicrotime() - $this->timeFlag) * 1000, 0)
			);
			if (count($this->_queriesLog) > $this->_queriesLogMax) {
				array_pop($this->_queriesLog);
			}
			
			$this->timeFlag = getMicrotime();
			return FALSE;
		} else {
			
			// log query
			$this->_queriesLog[] = array(
				'model' 	=> $modelName,
				'action' 	=> $actionName,
				'query' 	=> substr($result['URL'],strrpos($result['URL'], '?')),
				'error'		=> $result['errorCode'],
				'numRows'	=> isset($result['data']) ? count($result['data']) : $result['foundCount'],
				'took'		=> round((getMicrotime() - $this->timeFlag) * 1000, 0)
			);
			
			$this->timeFlag = getMicrotime();
			return TRUE;
		}
	}
	
	/**
	 * include value lists in data result
	 */
  function includeValueListsInDataResult () {
    
  }
	

    /** 
     * Returns number of rows in previous resultset. If no previous resultset exists, 
     * this returns false. 
     * NOT USED
     * 
     * @return int Number of rows in resultset 
     */ 
    function lastNumRows() { 
        return null; 
    } 
     
     
    /** 
     * NOT USED
     */ 
    function execute($query) { 
        return null; 
    } 
     
    /** 
     * NOT USED 
     */ 
    function fetchAll($query, $cache = true) { 
        return array(); 
    } 
     
    // Logs -------------------------------------------------------------- 
    /** 
     * logQuery
     */ 
    function logQuery($query) {
	}
     
    /** 
     * Outputs the contents of the queries log.
     * 
     * @param boolean $sorted 
     */ 
    function showLog() {
		
		$log = $this->_queriesLog;
		
		$totalTime = 0;
		foreach($log as $entry) {
			$totalTime += $entry['took'];
		}
		
		

		if ($this->_queriesCnt > 1) {
			$text = 'queries';
		} else {
			$text = 'query';
		}

		if (PHP_SAPI != 'cli') {
			print ("<table class=\"cake-sql-log\" id=\"cakeSqlLog_" . preg_replace('/[^A-Za-z0-9_]/', '_', uniqid(time(), true)) . "\" summary=\"Cake SQL Log\" cellspacing=\"0\" border = \"0\">\n<caption>({$this->configKeyName}) {$this->_queriesCnt} {$text} took {$totalTime} ms</caption>\n");
			print ("<thead>\n<tr><th>Nr</th><th>Model</th><th>Action</th><th>Query</th><th>Error</th><th>Num. rows</th><th>Took (ms)</th></tr>\n</thead>\n<tbody>\n");
			
			foreach ($log as $k => $i) {
				print ("<tr><td>" . ($k + 1) . "</td><td>{$i['model']}</td><td>{$i['action']}</td><td>" . h($i['query']) . "</td><td>{$i['error']}</td><td style = \"text-align: right\">{$i['numRows']}</td><td style = \"text-align: right\">{$i['took']}</td></tr>\n");
			}
			print ("</tbody></table>\n");
			
			//echo '<pre>' . print_r($log, true) . '</pre>';
		} else {
			foreach ($log as $k => $i) {
				print (($k + 1) . ". {$i['query']} {$i['error']}\n");
			}
		}
	}

    /** 
     * Output information about a query
     * NOT USED
     * 
     * @param string $query Query to show information on. 
     */ 
    function showQuery($query) { 
        
    } 
     

     

} 
?>