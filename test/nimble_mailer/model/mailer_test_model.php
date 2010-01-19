<?php

	class MailerTestModel extends NimbleMailer {
		
		public function foo($string) {
			$this->recipient = 'sdavis@stsci.edu';
			$this->subject = 'WHOA';
			$this->from = 'sdavis@stsci.edu';
			$this->string = $string;
		}
		
		
		public function bar($string) {
			$this->recipients = 'sdavis@stsci.edu';
			$this->subject = 'WHOA2';
			$this->from = 'sdavis@stsci.edu';
			$this->string = $string;
		}
		
	}

?>