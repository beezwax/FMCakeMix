<?php

App::import('Core', array('AppModel', 'Model'));

// test models
require_once dirname(__FILE__) . DS . 'models.php';


/**
 * Short description for class.
 *
 * @package       cake.tests
 * @subpackage    cake.tests.cases.libs.model
 */
class ModelTest extends CakeTestCase {
  
/**
 * autoFixtures property
 *
 * @var bool false
 * @access public
 */
  var $autoFixtures = false;
/**
 * fixtures property
 *
 * @var array
 * @access public
 */
  var $fixtures = array(
  );
/**
 * start method
 *
 * @access public
 * @return void
 */
  function start() {
    parent::start();
    $this->debug = Configure::read('debug');
    Configure::write('debug', 2);
  }
/**
 * end method
 *
 * @access public
 * @return void
 */
  function end() {
    parent::end();
    Configure::write('debug', $this->debug);
  }
  
/**
 * testColumnTypeFetching method
 *
 * @access public
 * @return void
 */
 function testColumnTypeFetching() {
   
   $model =& new TestArticle();
    // echo '<pre>'.print_r($model,true).'</pre>';
   $this->assertEqual($model->getColumnType('TestArticle.created'), 'timestamp');
   $this->assertEqual($model->getColumnType('TestArticle.Body'), 'string');
   $this->assertEqual($model->getColumnType('TestArticle.id'), 'float');
   $this->assertEqual($model->getColumnType('TestArticle.modified'), 'timestamp');
   $this->assertEqual($model->getColumnType('TestArticle.Title'), 'string');
 }


/**
 * testCreateRecord method
 *
 * @access public
 * @return void
 */
  function testCreateRecord() {
    
    $model =& new TestArticle();
    $_data = array(
      'TestArticle' => array(
        'Title' => 'UT Title',
        'Body' => 'UT Body'
      )
    );
    $model->create();
    $saveResult = $model->save($_data);
    $this->assertTrue($saveResult);
  }
   
/**
 * testCreateFindRecord method
 *
 * @access public
 * @return void
 */  
  function testCreateFindRecord() {

    $model =& new TestArticle();
    $_data = array(
      'TestArticle' => array(
        'Title' => 'UT CFR Title',
        'Body' => 'UT CFR Body'
      )
    );
    $model->create();
    $saveResult = $model->save($_data);
    
    $_recid = $model->id;
    
    $result = $model->find('all', array(
      'conditions' => array(
        '-recid' => $_recid
      ),
      'recursive' => 0
    ));
    
    $this->assertEqual(count($result), 1);
    $this->assertEqual($result[0]['TestArticle']['Title'], 'UT CFR Title');
    $this->assertEqual($result[0]['TestArticle']['Body'], 'UT CFR Body');
  }
  
  
   
/**
 * testCreateFindDeleteRecordAll method
 *
 * @access public
 * @return void
 */  
  function testCreateFindDeleteRecordAll() {

    $model =& new TestArticle();
    $_data = array(
      'TestArticle' => array(
        'Title' => 'UT CFR Title',
        'Body' => 'UT CFR Body'
      )
    );
    $model->create();
    $saveResult = $model->save($_data);
    
    $_recid = $model->id;
    
    $findResult = $model->find('first', array(
      'conditions' => array(
        'TestArticle.-recid' => $_recid
      ),
      'recursive' => 0
    ));
    
    $result = $model->delete();
    
    $this->assertTrue($result);
  }
   
/**
 * testCreateDelRecord method
 *
 * @access public
 * @return void
 */  
  function testCreateDelRecord() {
    
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
 * @access public
 * @return void
 */  
// function testCreateWFieldList() {
// 
//  $model =& new TestArticle();
//  $_data = array(
//    'TestArticle' => array(
//      'Title' => 'UT CWFL Title',
//      'Body' => 'UT CWFL Body'
//    )
//  );
//  $model->create();
//  $saveResult = $model->save($_data, array(
//    'fieldList' => array('Title')
//  ));
//  
//  $this->assertTrue($saveResult);
//  
//  $findResult = $model->find('first', array(
//    'conditions' => array(
//      '-recid' => $model->id
//    )
//  ));
// 
//  $this->assertEqual($saveResult['TestArticle']['Title'], 'UT CWFL Title');
//  $this->assertNotEqual($saveResult['TestArticle']['Body'], 'UT CWFL Body');
// 
//  //echo '<pre>'.print_r($result,true).'</pre>';
// 
// }
  
/**
 * testCreateSaveField method
 *
 * @access public
 * @return void
 */  
  function testCreateSaveField() {
    
    $model =& new TestArticle();
    $_data = array(
      'TestArticle' => array(
        'Title' => 'UT CSF Title',
        'Body' => 'UT CSF Body'
      )
    );
    $model->create();
    $saveResult = $model->save($_data);
    
    $this->assertTrue($saveResult);
    
    $this->assertTrue($model->saveField('Title','UT CSF Title Updated'));
    
    $findResult = $model->find('first', array(
      'conditions' => array(
        'TestArticle.-recid' => $model->getId()
      )
    ));
    
    $this->assertTrue($findResult);
    
    $this->assertEqual($findResult['TestArticle']['Title'], 'UT CSF Title Updated');
    $this->assertEqual($findResult['TestArticle']['Body'], 'UT CSF Body');
  }
   
/**
 * testSaveAll method
 *
 * @access public
 * @return void
 */  
  function testSaveAll() {
  
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
  
    $this->assertTrue($saveResult);
  
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
 * @access public
 * @return void
 */  
  function testCreateUpdate() {
  
    $model =& new TestArticle();
    $_data = array(
      'TestArticle' => array(
        'Title' => 'UT CU Title',
        'Body' => 'UT CU Body'
      )
    );
    $model->create();
    $saveResult = $model->save($_data);
  
    $_recid = $model->id;
  
    $_data = array(
      'TestArticle' => array(
        '-recid' => $_recid,
        'Title' => 'UT CU Title Updated',
        'Body' => 'UT CU Body Updated'
      )
    );
  
  
    $this->assertTrue($model->save($_data));
  
    $findResult = $model->find('first', array(
      'conditions' => array(
        '-recid' => $model->id
      )
    ));
  
  
    $this->assertEqual($findResult['TestArticle']['Title'], 'UT CU Title Updated');
    $this->assertEqual($findResult['TestArticle']['Body'], 'UT CU Body Updated');
  }
   
/**
 * testCreateHasManyFind method
 *
 * @access public
 * @return void
 */  
  function testCreateHasManyFind() {
    
    $articleModel =& new TestRelationsArticle();
    $_data = array(
      'TestRelationsArticle' => array(
        'Title' => 'UT CHMF Title',
        'Body' => 'UT CHMF Body'
      )
    );
    $articleModel->create();
    $saveResultArticle = $articleModel->save($_data);
    
    $this->assertTrue($saveResultArticle);
    
    
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
    
    $this->assertTrue($saveResultComment);
    
    
    $findResult = $articleModel->find('first', array(
      'conditions' => array(
        'TestRelationsArticle.-recid' => $articleModel->getId() 
      )
    ));
    
    
    $this->assertTrue($findResult);
    $this->assertEqual($findResult['TestRelationsArticle']['Title'], 'UT CHMF Title');
    $this->assertEqual(count($findResult['TestComment']), 2);
    $this->assertEqual($findResult['TestComment'][0]['body'], 'UT CHFM Comment Body');
    $this->assertEqual($findResult['TestComment'][1]['body'], 'UT CHFM Comment Body TWO');
  }
   
 /**
  * testCreateBelongsToFind method
  *
  * @access public
  * @return void
  */ 
  function testCreateBelongsToFind() {
    
    $userModel =& new TestUser();
    $_data = array(
      'TestUser' => array(
        'name_first' => 'UT CBTF First Name',
        'name_last' => 'UT CBTF Last Name'
      )
    );
    $userModel->create();
    $saveResultUser = $userModel->save($_data);
    
    $this->assertTrue($saveResultUser);
    
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
    
    $this->assertTrue($saveResultArticle);
    
    $findResult = $articleModel->find('first', array(
      'conditions' => array(
        'TestRelationsArticle.-recid' => $articleModel->getId() 
      )
    ));
    
    
    $this->assertTrue($findResult);
    $this->assertEqual($findResult['TestRelationsArticle']['Title'], 'UT CBTF Title');
    $this->assertEqual(count($findResult['TestUser']), 1);
    $this->assertEqual($findResult['TestUser'][0]['name_first'], 'UT CBTF First Name');
    $this->assertEqual($findResult['TestUser'][0]['name_last'], 'UT CBTF Last Name');
  }
  
/**
 * testOrFind method
 *
 * @access public
 * @return void
 */ 
  function testOrFind() {
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
  
    $this->assertTrue($saveResult);
    
    $findResult = $model->find('all', array(
      'conditions' => array(
        'TestArticle.Body' => $_data['TestArticle'][0]['Body'],
        'or' => true,
        'TestArticle.Title' => $_data['TestArticle'][1]['Title']
      )
    ));
    
    $this->assertTrue($findResult);
    $this->assertEqual(count($findResult), 2);
    $this->assertEqual($findResult[0]['TestArticle']['Body'], $_data['TestArticle'][0]['Body']);
    $this->assertEqual($findResult[1]['TestArticle']['Title'], $_data['TestArticle'][1]['Title']); 
  }
  
/**
 * testScriptExecution method
 *
 * @access public
 * @return void
 */  
  function testScriptExecution() {

    $model =& new TestArticle();
    $_data = array(
      'TestArticle' => array(
        'Title' => 'UT script Title'
      )
    );
    $model->create();
    $saveResult = $model->save($_data);

    $_recid = $model->id;

    $result = $model->find('all', array(
      'conditions' => array(
        '-recid' => $_recid,
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
 * @access public
 * @return void
 */  
  function testValueList() {

    $model =& new TestArticle();
    $_data = array(
      'TestArticle' => array(
        'Title' => 'UT test value lists'
      )
    );
    $model->create();
    $saveResult = $model->save($_data);

    $_recid = $model->id;

    $result = $model->read();

    $schema = $model->schema();
    pr($schema);
    
    $this->assertEqual(count($schema['Title']['valuelist']), 3);
    $this->assertEqual(count($schema['Body']['valuelist']), 3);
  }


/**
 * endCase method
 *
 * wrap up script
 * @access public
 * @return void
 */
  function endCase() {
    
    
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
    // delete users
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

?>