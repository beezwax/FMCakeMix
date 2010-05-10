<?php

class TestArticle extends CakeTestModel {
	
	var $defaultLayout = 'Article';
	var $useDbConfig = 'test';
	var $fmDatabaseName = 'DriverUnitTesting';
	var $name = 'TestArticle';
  var $returnValueLists = true;
}

class TestRelationsArticle extends CakeTestModel {
	
	var $defaultLayout = 'Article';
	var $useDbConfig = 'test';
	var $fmDatabaseName = 'DriverUnitTesting';
	var $name = 'TestRelationsArticle';
	var $primaryKey = 'id';
	
	var $hasMany = array(
		'TestComment' => array(
			'foreignKey' => '_fk_article_id'
		)
	);
	
	var $belongsTo = array(
		'TestUser' => array(
			//'className' => 'TestUser',
			'foreignKey' => '_fk_user_id'
		)
	);
}

class TestComment extends CakeTestModel {
	
	var $defaultLayout = 'Comments';
	var $useDbConfig = 'test';
	var $fmDatabaseName = 'DriverUnitTesting';
	var $name = 'TestComment';
}

class TestUser extends CakeTestModel {
	
	var $defaultLayout = 'Users';
	var $useDbConfig = 'test';
	var $fmDatabaseName = 'DriverUnitTesting';
	var $name = 'TestUser';
}


?>