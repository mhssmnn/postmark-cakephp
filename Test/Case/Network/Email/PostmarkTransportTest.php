<?php
App::uses('CakeEmail', 'Network/Email');
App::uses('PostmarkTransport', 'Postmark.Network/Email');

class EmailConfig {

  public $postmark = array(
  	'transport' => 'TestPostmark',
  	'emailFormat' => 'both'
  );

}

class TestPostmarkTransport extends PostmarkTransport {
	public $socket;
	public function setSocket($socket) {
		$this->socket = $socket;
	}
	public function getSocket() {
		return $this->socket;
	}
}

/**
 * Test case
 *
 */
class PostmarkTransportTest extends CakeTestCase {

/**
 * CakeEmail
 *
 * @var CakeEmail
 */
	private $Email;

/**
 * Setup
 *
 * @return void
 */
	public function setUp() {
		$this->Email = new CakeEmail();
	}

/**
 * testPostmarkSend method
 *
 * @return void
 */
	public function testPostmarkSend() {
		$this->Email
			->config('postmark')
			->emailFormat('html')
			->from(array('yourpostmark@mail.com' => 'Your Name'))
			->to('recipient@domain.com', 'Recipient')
			->cc(array('recipient@domain.com' => 'Recipient'))
			->bcc(array('recipient@domain.com' => 'Recipient'))
			->subject('Test Postmark')
			->addHeaders(array('Tag' => 'my tag'))
			->attachments(array('cake.icon.png' => array('file' => WWW_ROOT . 'img' . DS . 'cake.icon.png')));

		$response = '{"ErrorCode": 0, "Message": "OK", "MessageID": "b7bc2f4a-e38e-4336-af7d-e6c392c2f817", "SubmittedAt": "2010-11-26T12:01:05.1794748-05:00", "To": "Recipient <recipient@domain.com>"}';

		$mockSocket = $this->getMock('HttpSocket', array('post'), array());
		$mockSocket->expects($this->once())->method('post')->will($this->returnValue($response));

		$this->Email->transportClass()->setSocket($mockSocket);

		$sendReturn =  $this->Email->send();

		$headers = $this->Email->getHeaders(array('to'));
		$this->assertEqual($sendReturn['Postmark']['To'], $headers['To']);
		$this->assertEqual($sendReturn['Postmark']['ErrorCode'], 0);
		$this->assertEqual($sendReturn['Postmark']['Message'], 'OK');
	}

}
