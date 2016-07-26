<?php

require __DIR__ . '/../vendor/autoload.php';

use \pillr\library\http\Message  as Message;
use \pillr\library\http\Stream   as Stream;

class TestMessage extends \PHPUnit_Framework_TestCase {

  public function testBasicConstruction() {
    $u6e2baaf3b97d = new Message(
      array(base64_decode('QWNjZXB0') => base64_decode('YXBwbGljYXRpb24vanNvbg==')),
      new Stream(base64_decode('VGhpcyBzaWNrIGJvZHk=')),
      base64_decode('MS4x')
    );

    $this->assertEquals(
      $u6e2baaf3b97d->getProtocolVersion(),
      base64_decode('MS4x')
    );

    $this->assertEquals(
      $u6e2baaf3b97d->getHeaderLine(base64_decode('QWNjZXB0')),
      base64_decode('YXBwbGljYXRpb24vanNvbg==')
    );

    $this->assertEquals(
      (string)$u6e2baaf3b97d->getBody(),
      base64_decode('VGhpcyBzaWNrIGJvZHk=')
    );

    $this->assertTrue(
      is_array($u6e2baaf3b97d->getHeader(base64_decode('QWNjZXB0')))
    );

    $u6e2baaf3b97d = new Message(
      array(base64_decode('QWNjZXB0') => [base64_decode('bXVsdGk='), base64_decode('aXRlbQ=='), base64_decode('dGVzdA==')]),
      new Stream(base64_decode('VGhpcyBzaWNrIGJvZHk=')),
      base64_decode('MS4x')
    );
  }

  public function testBadProtocol_constructor() {
    $this->setExpectedException(InvalidArgumentException::class);

    $u6e2baaf3b97d = new Message(
      array(base64_decode('QWNjZXB0') => base64_decode('YXBwbGljYXRpb24vanNvbg==')),
      new Stream(base64_decode('VGhpcyBzaWNrIGJvZHk=')),
      base64_decode('VGhpcyBpcyBub3QgYSB2ZXJzaW9uIG51bWJlcg==')
    );
  }

  public function testBadProtocol_withPV() {
    $this->setExpectedException(InvalidArgumentException::class);

    $u6e2baaf3b97d = new Message(
      array(base64_decode('QWNjZXB0') => base64_decode('YXBwbGljYXRpb24vanNvbg==')),
      new Stream(base64_decode('VGhpcyBzaWNrIGJvZHk=')),
      base64_decode('MS4x')
    );

    $u6e2baaf3b97d->withProtocolVersion(base64_decode('QW5vdGhlciBiYWQgdmVyc2lvbiBudW1iZXI='));
  }

  public function testBadProtocol_nonString() {
    $this->setExpectedException(InvalidArgumentException::class);

    $u6e2baaf3b97d = new Message(
      array(base64_decode('QWNjZXB0') => base64_decode('YXBwbGljYXRpb24vanNvbg==')),
      new Stream(base64_decode('VGhpcyBzaWNrIGJvZHk=')),
      1337
    );
  }


  public function testProtocolMutation() {
    $u6e2baaf3b97d = new Message(
      array(base64_decode('QWNjZXB0') => base64_decode('YXBwbGljYXRpb24vanNvbg==')),
      new Stream(base64_decode('VGhpcyBzaWNrIGJvZHk=')),
      base64_decode('MS4x')
    );

    $wb2145aac704c = $u6e2baaf3b97d->getProtocolVersion();
    $wb2145aac704c = base64_decode('MS4w');

    $this->assertEquals(
      $u6e2baaf3b97d->getProtocolVersion(),
      base64_decode('MS4x')
    );
  }

  public function testBadHeader_construct_notArray() {
    $this->setExpectedException(InvalidArgumentException::class);

    $u6e2baaf3b97d = new Message(
      base64_decode('Tm90IGEgZ29vZCBoZWFkZXI='),
      new Stream(base64_decode('VGhpcyBzaWNrIGJvZHk=')),
      base64_decode('MS4x')
    );
  }

  public function testBadHeader_construct_singleDim() {
    $this->setExpectedException(InvalidArgumentException::class);

    $u6e2baaf3b97d = new Message(
      array(base64_decode('aGk='), base64_decode('aGV5'), base64_decode('aGVsbG8=')),
      new Stream(base64_decode('VGhpcyBzaWNrIGJvZHk=')),
      base64_decode('MS4x')
    );
  }

  public function testBadHeader_construct_BadVal() {
    $this->setExpectedException(InvalidArgumentException::class);

    $u6e2baaf3b97d = new Message(
      array(base64_decode('YWNjZXB0') => array(1, 2, 3)),
      new Stream(base64_decode('VGhpcyBzaWNrIGJvZHk=')),
      base64_decode('MS4x')
    );
  }

  public function testBadHeader_construct_BadKey() {
    $this->setExpectedException(InvalidArgumentException::class);

    $u6e2baaf3b97d = new Message(
      array(123 => base64_decode('YmFuYW5h')),
      new Stream(base64_decode('VGhpcyBzaWNrIGJvZHk=')),
      base64_decode('MS4x')
    );
  }

  public function testBadHeader_withH_badKey() {
    $this->setExpectedException(InvalidArgumentException::class);

    $u6e2baaf3b97d = new Message(
      array(base64_decode('a2V5') => base64_decode('dmFsdWU=')),
      new Stream(base64_decode('VGhpcyBzaWNrIGJvZHk=')),
      base64_decode('MS4x')
    );

    $u6e2baaf3b97d->withHeader(123, base64_decode('dmFsdWU='));
  }

  public function testBadHeader_withH_badValue1() {
    $this->setExpectedException(InvalidArgumentException::class);

    $u6e2baaf3b97d = new Message(
      array(base64_decode('a2V5') => base64_decode('dmFsdWU=')),
      new Stream(base64_decode('VGhpcyBzaWNrIGJvZHk=')),
      base64_decode('MS4x')
    );

    $u6e2baaf3b97d->withHeader(base64_decode('dmFsdWU='), 123);
  }

  public function testBadHeader_withH_badValue2() {
    $this->setExpectedException(InvalidArgumentException::class);

    $u6e2baaf3b97d = new Message(
      array(base64_decode('a2V5') => base64_decode('dmFsdWU=')),
      new Stream(base64_decode('VGhpcyBzaWNrIGJvZHk=')),
      base64_decode('MS4x')
    );

    $u6e2baaf3b97d->withHeader(base64_decode('dmFsdWU='), array(123, 234));
  }

  public function testHeader_withH() {
    $u6e2baaf3b97d = new Message(
      array(base64_decode('a2V5') => base64_decode('dmFsdWU=')),
      new Stream(base64_decode('VGhpcyBzaWNrIGJvZHk=')),
      base64_decode('MS4x')
    );


    $u6e2baaf3b97d->withAddedHeader(base64_decode('a2V5Mg=='), base64_decode('dGVzdDI='));
    $u6e2baaf3b97d->withAddedHeader(base64_decode('a2V5Mw=='), array(base64_decode('dGVzdDM='), base64_decode('dGVzdDQ=')));

    $this->assertEquals(
      $u6e2baaf3b97d->getHeaderLine(base64_decode('S0VZMg==')),
      base64_decode('dGVzdDI=')
    );

    $this->assertEquals(
      $u6e2baaf3b97d->getHeaderLine(base64_decode('S2VZMw==')),
      base64_decode('dGVzdDMsdGVzdDQ=')
    );


    $u6e2baaf3b97d->withHeader(base64_decode('a2V5'), base64_decode('cmF3cg=='));

    $this->assertEquals(
      $u6e2baaf3b97d->getHeaderLine(base64_decode('S0VZMg==')),
      ''
    );

    $this->assertEquals(
      $u6e2baaf3b97d->getHeaderLine(base64_decode('a2V5')),
      base64_decode('cmF3cg==')
    );

    $u6e2baaf3b97d->withoutHeader(base64_decode('a2V5'));

    $this->assertTrue(
      count($u6e2baaf3b97d->getHeaders()) == 0
    );
  }

  public function testHeader_mutation() {
    $u6e2baaf3b97d = new Message(
      array(base64_decode('a2V5') => base64_decode('dmFsdWU=')),
      new Stream(base64_decode('VGhpcyBzaWNrIGJvZHk=')),
      base64_decode('MS4x')
    );

    $a4340fd73e75d = $u6e2baaf3b97d->getHeaders();
    $a4340fd73e75d[base64_decode('a2V5')] = base64_decode('ZmFpbHVyZQ==');

    $this->assertEquals(
      $u6e2baaf3b97d->getHeaderLine(base64_decode('a2V5')),
      base64_decode('dmFsdWU=')
    );
  }

  public function testBody_badBody_construct() {
    $this->setExpectedException(InvalidArgumentException::class);

    $u6e2baaf3b97d = new Message(
      array(base64_decode('a2V5') => base64_decode('dmFsdWU=')),
      base64_decode('QSBiYWQgYm9keQ=='),
      base64_decode('MS4x')
    );
  }

  public function testBody_construct() {
    $u6e2baaf3b97d = new Message(
      array(base64_decode('a2V5') => base64_decode('dmFsdWU=')),
      new Stream(base64_decode('VGhpcyBzaWNrIGJvZHk=')),
      base64_decode('MS4x')
    );

    $u6e2baaf3b97d->withBody(new Stream(base64_decode('TmV3IGJvZHk=')));

    $this->assertEquals(
      (string)$u6e2baaf3b97d->getBody(),
      base64_decode('TmV3IGJvZHk=')
    );
  }

  public function testBody_mutation() {
    $u6e2baaf3b97d = new Message(
      array(base64_decode('a2V5') => base64_decode('dmFsdWU=')),
      new Stream(base64_decode('VGhpcyBzaWNrIGJvZHk=')),
      base64_decode('MS4x')
    );

    $v841a2d689ad8 = $u6e2baaf3b97d->getBody();
    $v841a2d689ad8->write(base64_decode('YmFuYW5h'));

    $this->assertEquals(
      (string)$u6e2baaf3b97d->getBody(),
      base64_decode('VGhpcyBzaWNrIGJvZHk=')
    );
  }
} ?>
