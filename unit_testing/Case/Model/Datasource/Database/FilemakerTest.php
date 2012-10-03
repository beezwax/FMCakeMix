<?php
/**
 * DboFilemakerTest file
 */
App::uses('Model', 'Model');
App::uses('AppModel', 'Model');
App::uses('Filemaker', 'Model/Datasource/Database');
App::uses('CakeSchema', 'Model');

/**
 * TestArticle class
 */
class TestArticle extends CakeTestModel {
	
	public $defaultLayout = 'Article';
	public $useDbConfig = 'test';
	public $fmDatabaseName = 'DriverUnitTesting';
	public $returnValueLists = true;
}

/**
 * TestRelationsArticle class
 */
class TestRelationsArticle extends CakeTestModel {
	
	public $defaultLayout = 'Article';
	public $useDbConfig = 'test';
	public $fmDatabaseName = 'DriverUnitTesting';
	public $primaryKey = 'id';
	
	public $hasMany = array(
		'TestComment' => array(
			'foreignKey' => '_fk_article_id'
		)
	);
	
	public $belongsTo = array(
		'TestUser' => array(
			'foreignKey' => '_fk_user_id'
		)
	);
}

/**
 * TestComment class
 */
class TestComment extends CakeTestModel {
	
	public $defaultLayout = 'Comments';
	public $useDbConfig = 'test';
	public $fmDatabaseName = 'DriverUnitTesting';
}

/**
 * TestUser class
 */
class TestUser extends CakeTestModel {
	
	public $defaultLayout = 'Users';
	public $useDbConfig = 'test';
	public $fmDatabaseName = 'DriverUnitTesting';
}

/**
 * DboFilemakerTest class
 */
class FilemakerTest extends CakeTestCase {
/**
 * autoFixtures property
 *
 * @var bool false
 */
	public $autoFixtures = false;

/**
 * fixtures property
 *
 * @var array
 */
	public $fixtures = array(
	);

/**
 * Sets up a Dbo class instance for testing
 *
 */
	public function setUp() {
		$this->Dbo = ConnectionManager::getDataSource('test');
		if (!($this->Dbo instanceof Filemaker)) {
			$this->markTestSkipped('FMCakeMix is not available.');
		}
		$this->_debug = Configure::read('debug');
		Configure::write('debug', 1);
	}

/**
 * Sets up a Dbo class instance for testing
 *
 */
	public function tearDown() {
		ClassRegistry::flush();
		Configure::write('debug', $this->_debug);
	}

/**
 * testColumnTypeFetching method
 *
 * @return void
 */
	public function testColumnTypeFetching() {
		$model =& new TestArticle();
		$this->assertEqual($model->getColumnType('TestArticle.created'), 'timestamp');
		$this->assertEqual($model->getColumnType('TestArticle.Body'), 'string');
		$this->assertEqual($model->getColumnType('TestArticle.id'), 'float');
		$this->assertEqual($model->getColumnType('TestArticle.modified'), 'timestamp');
		$this->assertEqual($model->getColumnType('TestArticle.Title'), 'string');
	}

/**
 * testCreateRecord method
 *
 * @return void
 */
	public function testCreateRecord() {
		$model =& new TestArticle();
		$_data = array(
			'TestArticle' => array(
				'Title' => 'UT Title',
				'Body' => 'UT Body'
			)
		);
		$model->create();
		$saveResult = $model->save($_data);
		
		$this->assertInternalType('array', $saveResult);
		$this->assertEqual($saveResult['TestArticle']['Title'], 'UT Title');
		$this->assertEqual($saveResult['TestArticle']['Body'], 'UT Body');
		$this->assertEqual($model->primaryKey, 'id');
		$this->assertArrayHasKey('id', $model->schema());
		$this->assertArrayHasKey('-recid', $model->schema());
		$this->assertArrayHasKey('-modid', $model->schema());
	}

/**
 * testCreateRecordWithFMRecID method
 *
 * @return void
 */
	public function testCreateRecordWithFMRecID() {
		$model =& new TestArticle();
		$_data = array(
			'TestArticle' => array(
				'Title' => 'UT CRWFMRID Title',
				'Body' => 'UT CRWFMRID Body',
				'-recid' => '',
			)
		);
		$model->create();
		$model->fm_recid = '-recid';
		$saveResult = $model->save($_data);
		
		$this->assertInternalType('array', $saveResult);
		$this->assertEqual($saveResult['TestArticle']['Title'], 'UT CRWFMRID Title');
		$this->assertEqual($saveResult['TestArticle']['Body'], 'UT CRWFMRID Body');
		$this->assertEqual($model->primaryKey, 'id');
		$this->assertArrayHasKey('id', $model->schema());
		$this->assertArrayHasKey('-recid', $model->schema());
		$this->assertArrayHasKey('-modid', $model->schema());
	}

/**
 * testCreateFindRecord method
 *
 * @return void
 */
	public function testCreateFindRecord() {
		$model =& new TestArticle();
		$_data = array(
			'TestArticle' => array(
				'Title' => 'UT CFR Title',
				'Body' => 'UT CFR Body'
			)
		);
		$model->create();
		$saveResult = $model->save($_data);

		$primaryKeyID = $model->id;

		$result = $model->find('all', array(
			'conditions' => array(
				'id' => $primaryKeyID
			),
			'recursive' => 0
		));

		$this->assertEqual(count($result), 1);
		$this->assertEqual($result[0]['TestArticle']['id'], $primaryKeyID);
		$this->assertEqual($result[0]['TestArticle']['Title'], 'UT CFR Title');
		$this->assertEqual($result[0]['TestArticle']['Body'], 'UT CFR Body');
		$this->assertEqual($model->primaryKey, 'id');
		$this->assertArrayHasKey('id', $model->schema());
		$this->assertArrayHasKey('-recid', $model->schema());
		$this->assertArrayHasKey('-modid', $model->schema());
	}

/**
 * testCreateFindRecordWithNoFindCriteria method
 *
 * @return void
 */
	public function testCreateFindRecordWithNoFindCriteria() {
		$model =& new TestArticle();
		$_data = array(
			'TestArticle' => array(
				'Title' => 'UT CFRWNFC Title',
				'Body' => 'UT CFRWNFC Body'
			)
		);
		$model->create();
		$saveResult = $model->save($_data);

		$primaryKeyID = $model->id;

		$result = $model->find('all', array(
			'conditions' => array(),
			'recursive' => 0,
		));

		$this->assertInternalType('array', $result);
		$this->assertGreaterThan(0, count($result[0]));
		$this->assertEqual($result[0]['TestArticle']['id'], $primaryKeyID);
		$this->assertEqual($result[0]['TestArticle']['Title'], 'UT CFRWNFC Title');
		$this->assertArrayHasKey('id', $model->schema());
		$this->assertArrayHasKey('-recid', $model->schema());
	}

/**
 * testCreateFindDeleteRecordAll method
 *
 * @return void
 */
	public function testCreateFindDeleteRecordAll() {
		$model =& new TestArticle();
		$_data = array(
			'TestArticle' => array(
				'Title' => 'UT CFR Title',
				'Body' => 'UT CFR Body'
			)
		);
		$model->create();
		$saveResult = $model->save($_data);

		$primaryKeyID = $model->id;

		$findResult = $model->find('first', array(
				'conditions' => array(
				'TestArticle.id' => $primaryKeyID
			),
			'recursive' => 0
		));

		$result = $model->delete();
		$this->assertTrue($result);
	}

/**
 * testCreateDelRecord method
 *
 * @return void
 */  
	public function testCreateDelRecord() {
		$model =& new TestArticle();
		$_data = array(
			'TestArticle' => array(
				'Title' => 'UT CD Title',
				'Body' => 'UT CD Body'
			)
		);
		$model->create();
		$saveResult = $model->save($_data);

		$result = $model->delete();

		$this->assertTrue($result);
	}

/**
 * testCreateWFieldList method
 *
 * @return void
 */
//	public function testCreateWFieldList() {
//		$model =& new TestArticle();
//		$_data = array(
//			'TestArticle' => array(
//				'Title' => 'UT CWFL Title',
//				'Body' => 'UT CWFL Body'
//			)
//		);
//		$model->create();
//		$saveResult = $model->save($_data, array(
//			'fieldList' => array('Title')
//		));
//
//		$this->assertTrue($saveResult);
//
//		$findResult = $model->find('first', array(
//				'conditions' => array(
//				'id' => $model->id
//			)
//		));
//
//		$this->assertEqual($saveResult['TestArticle']['Title'], 'UT CWFL Title');
//		$this->assertNotEqual($saveResult['TestArticle']['Body'], 'UT CWFL Body');
//
//		//echo '<pre>'.print_r($result,true).'</pre>';
//
//	}

/**
 * testCreateSaveField method
 *
 * @return void
 */
	public function testCreateSaveField() {
		$model =& new TestArticle();
		$_data = array(
			'TestArticle' => array(
				'Title' => 'UT CSF Title',
				'Body' => 'UT CSF Body'
			)
		);
		$model->create();
		$saveResult = $model->save($_data);

		$this->assertInternalType('array', $saveResult);
		$this->assertEqual($saveResult['TestArticle']['Title'], 'UT CSF Title');

		$saveFieldResult = $model->saveField('Title', 'UT CSF Title Updated');
		$this->assertInternalType('array', $saveFieldResult);
		$this->assertEqual($saveFieldResult['TestArticle']['Title'], 'UT CSF Title Updated');

		$findResult = $model->find('first', array(
			'conditions' => array(
				'TestArticle.-recid' => $model->field('-recid')
			)
		));

		$this->assertInternalType('array', $findResult);
		$this->assertEqual($findResult['TestArticle']['Title'], 'UT CSF Title Updated');
		$this->assertEqual($findResult['TestArticle']['Body'], 'UT CSF Body');
	}

/**
 * testCreateSaveRecord method
 *
 * @return void
 */
	public function testCreateSaveRecord() {
		$model =& new TestArticle();
		$_data = array(
			'TestArticle' => array(
				'Title' => 'UT CSR Title',
				'Body' => 'UT CSR Body'
			)
		);
		$model->create();
		$saveResult = $model->save($_data);

		$this->assertInternalType('array', $saveResult);
		$this->assertEqual($saveResult['TestArticle']['Title'], 'UT CSR Title');

		$_data = array(
			'TestArticle' => array(
				'id' => $saveResult['TestArticle']['id'],
				'Title' => 'UT CSR Title Updated',
				'Body' => 'UT CSR Body Updated'
			)
		);
		$model->create();
		$model->id = $saveResult['TestArticle']['id'];
		$saveResult = $model->save($_data);
		
		$this->assertInternalType('array', $saveResult);
		$this->assertEqual($saveResult['TestArticle']['Title'], 'UT CSR Title Updated');
		$this->assertEqual($saveResult['TestArticle']['Body'], 'UT CSR Body Updated');

		$findResult = $model->find('first', array(
			'conditions' => array(
				'TestArticle.id' => $model->field('id')
			)
		));

		$this->assertInternalType('array', $findResult);
		$this->assertEqual($findResult['TestArticle']['Title'], 'UT CSR Title Updated');
		$this->assertEqual($findResult['TestArticle']['Body'], 'UT CSR Body Updated');
	}

/**
 * testCreateSaveRecordWithPrimaryKeyReadOnly method
 *
 * @return void
 */
	public function testCreateSaveRecordWithPrimaryKeyReadOnly() {
		$model =& new TestArticle();
		$_data = array(
			'TestArticle' => array(
				'Title' => 'UT CSRWPKRO Title',
				'Body' => 'UT CSRWPKRO Body'
			)
		);
		$model->create();
		$saveResult = $model->save($_data);

		$this->assertInternalType('array', $saveResult);
		$this->assertEqual($saveResult['TestArticle']['Title'], 'UT CSRWPKRO Title');

		$_data = array(
			'TestArticle' => array(
				'id' => $saveResult['TestArticle']['id'],
				'Title' => 'UT CSRWPKRO Title Updated',
				'Body' => 'UT CSRWPKRO Body Updated'
			)
		);
		$model->create();
		$model->id = $saveResult['TestArticle']['id'];
		$model->primaryKeyReadOnly = true;
		$saveResult = $model->save($_data);
		
		$this->assertInternalType('array', $saveResult);
		$this->assertEqual($saveResult['TestArticle']['Title'], 'UT CSRWPKRO Title Updated');
		$this->assertEqual($saveResult['TestArticle']['Body'], 'UT CSRWPKRO Body Updated');

		$findResult = $model->find('first', array(
			'conditions' => array(
				'TestArticle.-recid' => $model->field('-recid')
			)
		));

		$this->assertInternalType('array', $findResult);
		$this->assertEqual($findResult['TestArticle']['Title'], 'UT CSRWPKRO Title Updated');
		$this->assertEqual($findResult['TestArticle']['Body'], 'UT CSRWPKRO Body Updated');
	}

/**
 * testSaveAll method
 *
 * @return void
 */
	public function testSaveAll() {
		$model =& new TestArticle();

		$_key = substr(uniqid(rand(), true),0,6);

		$_data = array(
			'TestArticle' => array(
				array(
					'Title' => 'UT SA Title ' . $_key,
					'Body' => 'UT SA Body'
				), 
				array(
					'Title' => 'UT SA Title TWO ' . $_key,
					'Body' => 'UT SA Body TWO'
				)
			)
		);
		$model->create();
		$saveResult = $model->saveAll($_data['TestArticle'], array(
			'atomic' => FALSE
		));

		$this->assertInternalType('array', $saveResult);

		$findResult = $model->find('all', array(
			'conditions' => array(
				'TestArticle.Title' => $_key
			),
			'recursive' => 0
		));

		$this->assertEqual(count($findResult), 2);
		$this->assertEqual($findResult[0]['TestArticle']['Title'], 'UT SA Title ' . $_key);
		$this->assertEqual($findResult[0]['TestArticle']['Body'], 'UT SA Body');
		$this->assertEqual($findResult[1]['TestArticle']['Title'], 'UT SA Title TWO ' . $_key);
		$this->assertEqual($findResult[1]['TestArticle']['Body'], 'UT SA Body TWO');
	}

/**
 * testCreateUpdate method
 *
 * @return void
 */
	public function testCreateUpdate() {
		$model =& new TestArticle();
		$_data = array(
			'TestArticle' => array(
				'Title' => 'UT CU Title' . "\r\n" . 'UT CU Title Line 2' . "\n" . 'UT CU Title Line 3' . "\r" . 'UT CU Title Line 4',
				'Body' => array('UT CU Body', 'UT CU Body 2')
			)
		);
		$model->create();
		$saveResult = $model->save($_data);

		$this->assertEqual($saveResult['TestArticle']['Title'], 'UT CU Title' . "\n" . 'UT CU Title Line 2' . "\n" . 'UT CU Title Line 3' . "\n" . 'UT CU Title Line 4');
		$this->assertEqual($saveResult['TestArticle']['Body'], 'UT CU Body' . "\n" . 'UT CU Body 2');

		$primaryKeyID = $model->id;

		$_data = array(
			'TestArticle' => array(
				'id' => $primaryKeyID,
				'Title' => 'UT CU Title Updated',
				'Body' => array('UT CU Body Updated', 'UT CU Body Updated2')
			)
		);

		$this->assertInternalType('array', $model->save($_data));

		$findResult = $model->find('first', array(
			'conditions' => array(
				'id' => $model->id
			)
		));

		$this->assertEqual($saveResult['TestArticle']['-recid'], $findResult['TestArticle']['-recid']);
		$this->assertEqual($findResult['TestArticle']['id'], $primaryKeyID);
		$this->assertEqual($findResult['TestArticle']['Title'], 'UT CU Title Updated');
		$this->assertEqual($findResult['TestArticle']['Body'], 'UT CU Body Updated' . "\n" . 'UT CU Body Updated2');
	}

/**
 * testCreateHasManyFind method
 *
 * @return void
 */
	public function testCreateHasManyFind() {
		$articleModel =& new TestRelationsArticle();
		$_data = array(
			'TestRelationsArticle' => array(
				'Title' => 'UT CHMF Title',
				'Body' => 'UT CHMF Body'
			)
		);
		$articleModel->create();
		$saveResultArticle = $articleModel->save($_data);

		$this->assertInternalType('array', $saveResultArticle);

		$comment =& new TestComment();
		$_data = array(
			'TestComment' => array(
				array(
					'_fk_article_id' => $saveResultArticle['TestRelationsArticle']['id'],
					'body' => 'UT CHFM Comment Body'
				), 
				array(
					'_fk_article_id' => $saveResultArticle['TestRelationsArticle']['id'],
					'body' => 'UT CHFM Comment Body TWO'
				)
			)
		);

		$comment->create();
		$saveResultComment = $comment->saveAll($_data['TestComment'], array(
			'atomic' => false
		));

		$this->assertInternalType('array', $saveResultComment);

		$findResult = $articleModel->find('first', array(
			'conditions' => array(
				'TestRelationsArticle.id' => $articleModel->getId()
			)
		));

		$this->assertInternalType('array', $findResult);
		$this->assertEqual($findResult['TestRelationsArticle']['Title'], 'UT CHMF Title');
		$this->assertEqual(count($findResult['TestComment']), 2);
		$this->assertEqual($findResult['TestRelationsArticle']['id'], $articleModel->getId());
		$this->assertEqual($findResult['TestRelationsArticle']['id'], $findResult['TestComment'][0]['_fk_article_id']);
		$this->assertEqual($findResult['TestRelationsArticle']['id'], $findResult['TestComment'][1]['_fk_article_id']);
		$this->assertEqual($findResult['TestComment'][0]['body'], 'UT CHFM Comment Body');
		$this->assertEqual($findResult['TestComment'][1]['body'], 'UT CHFM Comment Body TWO');
	}

/**
 * testCreateHasManyFindWithOutRecursive method
 *
 * @return void
 */
	public function testCreateHasManyFindWithOutRecursive() {
		$articleModel =& new TestRelationsArticle();
		$_data = array(
			'TestRelationsArticle' => array(
				'Title' => 'UT CHMF Title',
				'Body' => 'UT CHMF Body'
			)
		);
		$articleModel->create();
		$saveResultArticle = $articleModel->save($_data);

		$this->assertInternalType('array', $saveResultArticle);

		$comment =& new TestComment();
		$_data = array(
			'TestComment' => array(
				array(
					'_fk_article_id' => $saveResultArticle['TestRelationsArticle']['id'],
					'body' => 'UT CHFM Comment Body'
				), 
				array(
					'_fk_article_id' => $saveResultArticle['TestRelationsArticle']['id'],
					'body' => 'UT CHFM Comment Body TWO'
				)
			)
		);

		$comment->create();
		$saveResultComment = $comment->saveAll($_data['TestComment'], array(
			'atomic' => false
		));

		$this->assertInternalType('array', $saveResultComment);

		$findResult = $articleModel->find('first', array(
			'conditions' => array(
				'TestRelationsArticle.id' => $articleModel->getId()
			),
			'recursive' => 0,
		));

		$this->assertInternalType('array', $findResult);
		$this->assertEqual($findResult['TestRelationsArticle']['Title'], 'UT CHMF Title');
		$this->assertArrayNotHasKey('TestComment', $findResult);
	}

/**
 * testCreateBelongsToFind method
 *
 * @return void
 */
	public function testCreateBelongsToFind() {
		$userModel =& new TestUser();
		$_data = array(
			'TestUser' => array(
				'name_first' => 'UT CBTF First Name',
				'name_last' => 'UT CBTF Last Name'
			)
		);
		$userModel->create();
		$saveResultUser = $userModel->save($_data);

		$this->assertInternalType('array', $saveResultUser);

		$articleModel =& new TestRelationsArticle();
		$_data = array(
			'TestRelationsArticle' => array(
				'_fk_user_id' => $saveResultUser['TestUser']['id'],
				'Title' => 'UT CBTF Title',
				'Body' => 'UT CBTF Body'
			)
		);
		$articleModel->create();
		$saveResultArticle = $articleModel->save($_data);

		$this->assertInternalType('array', $saveResultArticle);

		$findResult = $articleModel->find('first', array(
			'conditions' => array(
				'TestRelationsArticle.id' => $articleModel->getId() 
			)
		));

		$this->assertInternalType('array', $findResult);
		$this->assertEqual($findResult['TestRelationsArticle']['Title'], 'UT CBTF Title');
		$this->assertEqual(count($findResult['TestUser']), 1);
		$this->assertEqual($findResult['TestRelationsArticle']['id'], $articleModel->getId());
		$this->assertEqual($findResult['TestUser'][0]['id'], $findResult['TestRelationsArticle']['_fk_user_id']);
		$this->assertEqual($findResult['TestUser'][0]['id'], $findResult['TestRelationsArticle']['_fk_user_id']);
		$this->assertEqual($findResult['TestUser'][0]['name_first'], 'UT CBTF First Name');
		$this->assertEqual($findResult['TestUser'][0]['name_last'], 'UT CBTF Last Name');
	}

/**
 * testOrFind method
 *
 * @return void
 */
	public function testOrFind() {
		$model =& new TestArticle();

		$_data = array(
			'TestArticle' => array(
				array(
					'Title' => 'UT testorfind A',
					'Body' => 'maddy'
				), 
				array(
					'Title' => 'UT testorfind B'
				)
			)
		);
		$model->create();
		$saveResult = $model->saveAll($_data['TestArticle'], array(
			'atomic' => FALSE
		));

		$this->assertInternalType('array', $saveResult);

		$findResult = $model->find('all', array(
			'conditions' => array(
				'TestArticle.Body' => $_data['TestArticle'][0]['Body'],
				'or' => true,
				'TestArticle.Title' => $_data['TestArticle'][1]['Title']
				)
		));

		$this->assertInternalType('array', $findResult);
		$this->assertEqual(count($findResult), 2);
		$this->assertEqual($findResult[0]['TestArticle']['Body'], $_data['TestArticle'][0]['Body']);
		$this->assertEqual($findResult[1]['TestArticle']['Title'], $_data['TestArticle'][1]['Title']); 
	}

/**
 * testCreateFindSortRecordDescend method
 *
 * @return void
 */
	public function testCreateFindSortRecordDescend() {
		$model =& new TestArticle();

		$_data = array(
			'TestArticle' => array(
				array(
					'Title' => 'UT testsort A',
				), 
				array(
					'Title' => 'UT testsort B'
				)
			)
		);
		$model->create();
		$saveResult = $model->saveAll($_data['TestArticle'], array(
			'atomic' => FALSE
		));

		$this->assertInternalType('array', $saveResult);

		$findResult = $model->find('all', array(
			'conditions' => array(
					'TestArticle.Title' => 'UT testsort',
					'TestArticle.Title.op' => 'cn',
				),
			'order' => array('TestArticle.Title' => 'desc'),
		));

		$this->assertInternalType('array', $findResult);
		$this->assertEqual(count($findResult), 2);
		$this->assertEqual($findResult[0]['TestArticle']['Title'], $_data['TestArticle'][1]['Title']);
		$this->assertEqual($findResult[1]['TestArticle']['Title'], $_data['TestArticle'][0]['Title']); 
	}

/**
 * testCreateFindSkipRecord method
 *
 * @return void
 */
	public function testCreateFindSkipRecord() {
		$model =& new TestArticle();

		$_data = array(
			'TestArticle' => array(
				array(
					'Title' => 'UT testskip A',
				), 
				array(
					'Title' => 'UT testskip B',
				)
			)
		);
		$model->create();
		$saveResult = $model->saveAll($_data['TestArticle'], array(
			'atomic' => FALSE
		));

		$this->assertInternalType('array', $saveResult);

		$findResult = $model->find('all', array(
			'conditions' => array(
					'TestArticle.Title' => 'UT testskip',
					'TestArticle.Title.op' => 'cn',
				),
			'offset' => 1,
		));

		$this->assertInternalType('array', $findResult);
		$this->assertEqual(count($findResult), 1);
		$this->assertEqual($findResult[0]['TestArticle']['Title'], $_data['TestArticle'][1]['Title']);
	}

/**
 * testCreateFindCountRecord method
 *
 * @return void
 */
	public function testCreateFindCountRecord() {
		$model =& new TestArticle();

		$_data = array(
			'TestArticle' => array(
				array(
					'Title' => 'UT testcount A',
					'Body' => '100',
				), 
				array(
					'Title' => 'UT testcount B',
					'Body' => '10',
				),
				array(
					'Title' => 'UT testcount C',
					'Body' => '1000',
				)
			)
		);
		$model->create();
		$saveResult = $model->saveAll($_data['TestArticle'], array(
			'atomic' => FALSE
		));

		$this->assertInternalType('array', $saveResult);

		$countResult = $model->find('count', array(
			'conditions' => array(
					'TestArticle.Title' => 'UT testcount',
					'TestArticle.Title.op' => 'cn',
					'TestArticle.Body' => '100',
					'TestArticle.Body.op' => 'lte',
				),
		));

		$this->assertInternalType('integer', $countResult);
		$this->assertEqual($countResult, 2);
	}

/*
 * testCreateRecordError method
 *
 * @return void
 */
	public function testCreateRecordError() {
		$model =& new TestArticle();
		$_data = array(
			'TestArticle' => array(
				'Title' => 'UT Title',
				'Body' => 'UT Body',
				'-recid' => '0',
			)
		);
		$model->create();

		$saveResult = $model->save($_data);
		
		$this->assertFalse($saveResult);
	}

/**
 * testCreateFindRecordError method
 *
 * @return void
 */
	public function testCreateFindRecordError() {
		$model =& new TestArticle();

		$_data = array(
			'TestArticle' => array(
				array(
					'Title' => 'UT testfinderror',
				), 
			)
		);
		$model->create();
		$saveResult = $model->saveAll($_data['TestArticle'], array(
			'atomic' => FALSE
		));

		$this->assertInternalType('array', $saveResult);

		$model->defaultLayout = 'notExistingLayout';
		$findResult = $model->find('all', array(
			'conditions' => array(
					'TestArticle.Title' => 'UT testfinderror',
				),
		));

		$this->assertFalse($findResult);
	}

/**
 * testCreateFindCountRecordError method
 *
 * @return void
 */
	public function testCreateFindCountRecordError() {
		$model =& new TestArticle();

		$_data = array(
			'TestArticle' => array(
				array(
					'Title' => 'UT testfindcounterror',
				), 
			)
		);
		$model->create();
		$saveResult = $model->saveAll($_data['TestArticle'], array(
			'atomic' => FALSE
		));

		$this->assertInternalType('array', $saveResult);

		$model->defaultLayout = 'notExistingLayout';
		$countResult = $model->find('count', array(
			'conditions' => array(
					'TestArticle.Title' => 'UT testfindcounterror',
				),
		));

		$this->assertFalse($countResult);
	}

/**
 * testCreateSaveRecordError method
 *
 * @return void
 */
	public function testCreateSaveRecordError() {
		$model =& new TestArticle();
		$_data = array(
			'TestArticle' => array(
				'Title' => 'UT CSRE Title',
				'Body' => 'UT CSRE Body'
			)
		);
		$model->create();
		$createResult = $model->save($_data);

		$this->assertInternalType('array', $createResult);
		$this->assertEqual($createResult['TestArticle']['Title'], 'UT CSRE Title');

		$_data = array(
			'TestArticle' => array(
				'id' => $createResult['TestArticle']['id'],
				'-recid' => 0,
				'Title' => 'UT CSRE Title Updated',
				'Body' => 'UT CSRE Body Updated'
			)
		);
		$model->create();
		$model->id = $createResult['TestArticle']['id'];
		$saveResult = $model->save($_data);
		
		$this->assertFalse($saveResult);

		$findResult = $model->find('first', array(
			'conditions' => array(
				'TestArticle.id' => $createResult['TestArticle']['id']
			)
		));

		$this->assertInternalType('array', $findResult);
		$this->assertEqual($findResult['TestArticle']['Title'], 'UT CSRE Title');
		$this->assertEqual($findResult['TestArticle']['Body'], 'UT CSRE Body');
	}

/**
 * testCreateDeleteRecordError method
 *
 * @return void
 */  
	public function testCreateDeleteRecordError() {
		$model =& new TestArticle();
		$_data = array(
			'TestArticle' => array(
				'Title' => 'UT CDRE Title',
				'Body' => 'UT CDRE Body'
			)
		);
		$model->create();
		$saveResult = $model->save($_data);

		$result = $model->deleteAll(array('-recid' => -1), false);

		$this->assertFalse($result);
	}

/**
 * testScriptExecution method
 *
 * @return void
 */
	public function testScriptExecution() {
		$model =& new TestArticle();
		$_data = array(
			'TestArticle' => array(
				'Title' => 'UT script Title'
			)
		);
		$model->create();
		$saveResult = $model->save($_data);

		$primaryKeyID = $model->id;

		$result = $model->find('all', array(
			'conditions' => array(
				'id' => $primaryKeyID,
				'-script' => 'set article body',
				'-script.param' => 'lorem ipsum'
			),
			'recursive' => 0
		));

		$this->assertEqual(count($result), 1);
		$this->assertEqual($result[0]['TestArticle']['Title'], $_data['TestArticle']['Title']);
		$this->assertEqual($result[0]['TestArticle']['Body'], 'lorem ipsum');
	}


/**
 * testValueList method
 *
 * @return void
 */
	public function testValueList() {
		$model =& new TestArticle();
		$_data = array(
			'TestArticle' => array(
				'Title' => 'UT test value lists'
			)
		);
		$model->create();
		$saveResult = $model->save($_data);

		$primaryKeyID = $model->id;

		$result = $model->read();

		$schema = $model->schema();

		$this->assertEqual(count($schema['Title']['valuelist']), 3);
		$this->assertEqual(count($schema['Body']['valuelist']), 3);
	}

/**
 * testBegin method
 *
 * @return void
 */
	public function testBegin() {
		$this->assertFalse(ConnectionManager::getDataSource('test')->begin());
	}

/**
 * testRollback method
 *
 * @return void
 */
	public function testRollback() {
		$this->assertFalse(ConnectionManager::getDataSource('test')->rollback());
	}

/**
 * testCommit method
 *
 * @return void
 */
	public function testCommit() {
		$this->assertFalse(ConnectionManager::getDataSource('test')->commit());
	}

/**
 * testLastNumRows method
 *
 * @return void
 */
	public function testLastNumRows() {
		$this->assertNull(ConnectionManager::getDataSource('test')->lastNumRows());
	}

/**
 * testExecute method
 *
 * @return void
 */
	public function testExecute() {
		$this->assertFalse(ConnectionManager::getDataSource('test')->execute('NOT USED'));
	}

/**
 * testFetchAll method
 *
 * @return void
 */
	public function testFetchAll() {
		$this->assertFalse(ConnectionManager::getDataSource('test')->fetchAll('NOT USED'));
	}

/**
 * testLogQuery method
 *
 * @return void
 */
	public function testLogQuery() {
		$this->assertNull(ConnectionManager::getDataSource('test')->logQuery('NOT USED'));
	}

/**
 * testShowLog method
 *
 * @return void
 */
	public function testShowLog() {
		$this->assertFalse(ConnectionManager::getDataSource('test')->showLog());
	}

/**
 * endTest method
 *
 * wrap up script
 * @return void
 */
	public function endTest() {
		// delete articles
		$model =& new TestArticle();
		$result = $model->find('all', array(
			'conditions' => array(
				'Title' => 'UT'
			),
			'recursive' => 0
		));
		foreach($result as $record) {
			$model->deleteAll(array('-recid' => $record['TestArticle']['-recid']), false);
		}

		// delete users
		$model =& new TestUser();
		$result = $model->find('all', array(
			'conditions' => array(
				'name_first' => 'UT'
			),
			'recursive' => 0
		));
		foreach($result as $record) {
			$model->deleteAll(array('-recid' => $record['TestUser']['-recid']), false);
		}

		// delete comments
		$model =& new TestComment();
		$result = $model->find('all', array(
			'conditions' => array(
				'body' => 'UT'
			 ),
			'recursive' => 0
		));
		foreach($result as $record) {
			$model->deleteAll(array('-recid' => $record['TestComment']['-recid']), false);
		}
	}
}