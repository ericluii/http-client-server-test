<?php

require __DIR__ . '/../vendor/autoload.php';

use \pillr\library\http\Message  as Message;
use \pillr\library\http\Stream   as Stream;

class TestMessage extends \PHPUnit_Framework_TestCase {

  public function testBasicConstruction() {
    $msg = new Message(
      array('Accept' => 'application/json'),
      new Stream('This sick body'),
      '1.1'
    );

    $this->assertEquals(
      $msg->getProtocolVersion(),
      '1.1'
    );

    $this->assertEquals(
      $msg->getHeaderLine('Accept'),
      'application/json'
    );

    $this->assertEquals(
      (string)$msg->getBody(),
      'This sick body'
    );

    $this->assertTrue(
      is_array($msg->getHeader('Accept'))
    );

    $msg = new Message(
      array('Accept' => ['multi', 'item', 'test']),
      new Stream('This sick body'),
      '1.1'
    );
  }

  public function testBadProtocol_constructor() {
    $this->setExpectedException(InvalidArgumentException::class);

    $msg = new Message(
      array('Accept' => 'application/json'),
      new Stream('This sick body'),
      'This is not a version number'
    );
  }

  public function testBadProtocol_withPV() {
    $this->setExpectedException(InvalidArgumentException::class);

    $msg = new Message(
      array('Accept' => 'application/json'),
      new Stream('This sick body'),
      '1.1'
    );

    $msg->withProtocolVersion('Another bad version number');
  }

  public function testBadProtocol_nonString() {
    $this->setExpectedException(InvalidArgumentException::class);

    $msg = new Message(
      array('Accept' => 'application/json'),
      new Stream('This sick body'),
      1337
    );
  }

  // Redundant test - but here so to remember about case
  public function testProtocolMutation() {
    $msg = new Message(
      array('Accept' => 'application/json'),
      new Stream('This sick body'),
      '1.1'
    );

    $var = $msg->getProtocolVersion();
    $var = '1.0';

    $this->assertEquals(
      $msg->getProtocolVersion(),
      '1.1'
    );
  }

  public function testBadHeader_construct_notArray() {
    $this->setExpectedException(InvalidArgumentException::class);

    $msg = new Message(
      'Not a good header',
      new Stream('This sick body'),
      '1.1'
    );
  }

  public function testBadHeader_construct_singleDim() {
    $this->setExpectedException(InvalidArgumentException::class);

    $msg = new Message(
      array('hi', 'hey', 'hello'),
      new Stream('This sick body'),
      '1.1'
    );
  }

  public function testBadHeader_construct_BadVal() {
    $this->setExpectedException(InvalidArgumentException::class);

    $msg = new Message(
      array('accept' => array(1, 2, 3)),
      new Stream('This sick body'),
      '1.1'
    );
  }

  public function testBadHeader_construct_BadKey() {
    $this->setExpectedException(InvalidArgumentException::class);

    $msg = new Message(
      array(123 => 'banana'),
      new Stream('This sick body'),
      '1.1'
    );
  }

  public function testBadHeader_withH_badKey() {
    $this->setExpectedException(InvalidArgumentException::class);

    $msg = new Message(
      array('key' => 'value'),
      new Stream('This sick body'),
      '1.1'
    );

    $msg->withHeader(123, 'value');
  }

  public function testBadHeader_withH_badValue1() {
    $this->setExpectedException(InvalidArgumentException::class);

    $msg = new Message(
      array('key' => 'value'),
      new Stream('This sick body'),
      '1.1'
    );

    $msg->withHeader('value', 123);
  }

  public function testBadHeader_withH_badValue2() {
    $this->setExpectedException(InvalidArgumentException::class);

    $msg = new Message(
      array('key' => 'value'),
      new Stream('This sick body'),
      '1.1'
    );

    $msg->withHeader('value', array(123, 234));
  }

  public function testHeader_withH() {
    $msg = new Message(
      array('key' => 'value'),
      new Stream('This sick body'),
      '1.1'
    );

    // Test adding headers
    $msg->withAddedHeader('key2', 'test2');
    $msg->withAddedHeader('key3', array('test3', 'test4'));

    $this->assertEquals(
      $msg->getHeaderLine('KEY2'), // test insensitive as well
      'test2'
    );

    $this->assertEquals(
      $msg->getHeaderLine('KeY3'), // test insensitive as well
      'test3,test4'
    );

    // Test removing headers
    $msg->withHeader('key', 'rawr');

    $this->assertEquals(
      $msg->getHeaderLine('KEY2'),
      ''
    );

    $this->assertEquals(
      $msg->getHeaderLine('key'),
      'rawr'
    );

    $msg->withoutHeader('key');

    $this->assertTrue(
      count($msg->getHeaders()) == 0
    );
  }

  public function testHeader_mutation() {
    $msg = new Message(
      array('key' => 'value'),
      new Stream('This sick body'),
      '1.1'
    );

    $headers = $msg->getHeaders();
    $headers['key'] = 'failure';

    $this->assertEquals(
      $msg->getHeaderLine('key'),
      'value'
    );
  }

  public function testBody_badBody_construct() {
    $this->setExpectedException(InvalidArgumentException::class);

    $msg = new Message(
      array('key' => 'value'),
      'A bad body',
      '1.1'
    );
  }

  public function testBody_construct() {
    $msg = new Message(
      array('key' => 'value'),
      new Stream('This sick body'),
      '1.1'
    );

    $msg->withBody(new Stream('New body'));

    $this->assertEquals(
      (string)$msg->getBody(),
      'New body'
    );
  }

  public function testBody_mutation() {
    $msg = new Message(
      array('key' => 'value'),
      new Stream('This sick body'),
      '1.1'
    );

    $body = $msg->getBody();
    $body->write('banana');

    $this->assertEquals(
      (string)$msg->getBody(),
      'This sick body'
    );
  }
}
