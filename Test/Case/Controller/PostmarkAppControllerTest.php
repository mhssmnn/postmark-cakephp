<?php

// Fake Comment class to represent local model
class Comment {

  public $data = null;

  // Implements the callback
  public function createFromPostmarkHook($data) {
    $this->data = $data;
    return true;
  }
}

class PostmarkAppControllerTest extends ControllerTestCase {

/**
 * Fixtures
 *
 * @var array
 */
  public $fixtures = array();

/**
 * Inbound postmark data
 *
 * @var array
 */
  public $inbound = null;

/**
 * Model with attached inbound data
 *
 * @var array
 */
  public $Comment = null;

/**
 * setUp method
 *
 * @return void
 */
  public function setUp() {
    parent::setUp();

    $path = CakePlugin::path('Postmark') . 'Test' . DS . 'Fixture' . DS;
    $this->inbound = file_get_contents($path . 'inbound.json');

    $this->_oldPostmarkModel = Configure::read('Postmark.Inbound.model');
    Configure::write('Postmark.Inbound.model', 'Comment');
  }

/**
 * tearDown method
 *
 * @return void
 */
  public function tearDown() {
    unset($this->inbound);
    Configure::write('Postmark.Inbound.model', $this->_oldPostmarkModel);

    parent::tearDown();
  }

/**
 * testInbound method
 *
 * @return void
 */
  public function testInbound() {
    $Postmark = $this->generate('Postmark.PostmarkApp', array(
      'methods' => array('_getModel')
    ));

    $this->Comment = new Comment();

    $Postmark->expects($this->once())->method('_getModel')->will($this->returnValue($this->Comment));

    $this->testAction('/postmark/inbound.json', array('data' => $this->inbound, 'method' => 'post'));

    $this->assertEquals($this->Comment->data['Subject'], 'This is an inbound message');
    $this->assertEquals($this->Comment->data['From'], 'myUser@theirDomain.com');
    $this->assertEquals(array_values($this->Comment->data['FromFull']), array('myUser@theirDomain.com', 'John Doe'));
    $this->assertEquals($this->Comment->data['Date'], 'Thu, 5 Apr 2012 16:59:01 +0200');
    $this->assertEquals($this->Comment->data['ReplyTo'], 'myUsersReplyAddress@theirDomain.com');
    $this->assertEquals($this->Comment->data['MailboxHash'], 'ahoy');
    $this->assertEquals($this->Comment->data['Tag'], '');
    $this->assertEquals($this->Comment->data['MessageID'], '22c74902-a0c1-4511-804f2-341342852c90');
    $this->assertEquals(strlen($this->Comment->data['TextBody']), 7);
    $this->assertEquals(strlen($this->Comment->data['HtmlBody']), 15);

    // Headers
    $this->assertEquals($this->Comment->data['Headers']['X-Spam-Status'], 'No');
    $this->assertEquals($this->Comment->data['Headers']['X-Spam-Checker-Version'], 'SpamAssassin 3.3.1 (2010-03-16) onrs-ord-pm-inbound1.wildbit.com');
    $this->assertEquals($this->Comment->data['Headers']['X-Spam-Score'], '-0.1');
    $this->assertEquals($this->Comment->data['Headers']['X-Spam-Tests'], 'DKIM_SIGNED,DKIM_VALID,DKIM_VALID_AU,SPF_PASS');
    $this->assertContains('Pass', $this->Comment->data['Headers']['Received-SPF']);
    $this->assertEquals($this->Comment->data['Headers']['MIME-Version'], '1.0');
    $this->assertEquals($this->Comment->data['Headers']['Message-ID'], '<CAGXpo2WKfxHWZ5UFYCR3H_J9SNMG+5AXUovfEFL6DjWBJSyZaA@mail.gmail.com>');

    // Recipients
    $recipients = $this->Comment->data['ToFull'];
    $this->assertEquals(count($recipients), 2);
    $this->assertEquals($recipients[0]['Email'], '451d9b70cf9364d23ff6f9d51d870251569e+ahoy@inbound.postmarkapp.com');
    $this->assertEquals($recipients[0]['Name'], FALSE);
    $this->assertEquals($recipients[1]['Email'], '451d9b70cf9364d23ff025154f870251569e+ahoy@inbound.postmarkapp.com');
    $this->assertEquals($recipients[1]['Name'], 'Ian Tofull');

    // CC Recipients
    $undisclosed_recipients = $this->Comment->data['CcFull'];
    $this->assertEquals(count($undisclosed_recipients), 2);
    $this->assertEquals($undisclosed_recipients[0]['Email'], 'sample.cc@emailDomain.com');
    $this->assertEquals($undisclosed_recipients[0]['Name'], 'Full name');
    $this->assertEquals($undisclosed_recipients[1]['Email'], 'another.cc@emailDomain.com');
    $this->assertEquals($undisclosed_recipients[1]['Name'], 'Another Cc');
  }

/**
 * testBounce method
 *
 * @return void
 */
  public function testBounce() {
    
  }


}