<?php

namespace B2;

class Mail
{
	var $mail = NULL;
	var $subject = NULL;
	var $to = NULL;

	static function factory()
	{
		$class_name = get_called_class();
		$object = new $class_name;
		$object->mail = new \Nette\Mail\Message;

		$tpl = $object->find_template();

		$text = file_get_contents($tpl);

		$html = \Michelf\Markdown::defaultTransform($text);

		$object->text = $text;
		$object->html = $html;

		return $object;
	}

	function find_template()
	{
		for($current_class = get_class($this); $current_class; $current_class = get_parent_class($current_class))
		{
			$reflector = new \ReflectionClass($current_class);
			$class_file = $reflector->getFileName();

			if(file_exists($tpl = str_replace('.php', ".md.tpl", $class_file)))
				return $tpl;
		}

		return NULL;
	}

	static function make_recipient($user)
	{
		if(!$user)
			return NULL;

		if(is_array($user))
			list($email, $name) = $user;
		elseif(!is_object($user))
			return $user;
		else
		{
			$name  = $user->title();
			$email = $user->email();
		}

		if(preg_match('/^[\w\s]+$/', $name))
			return "$name <$email>";

		return "=?UTF-8?B?".base64_encode($name)."?= <$email>";
	}

	function send()
	{
		if(empty($this->subject))
//			$this->mail->setSubject($this->title());
			$this->mail->setSubject('test');
		else
			$this->mail->setSubject($this->subject);

//		$this->mail->setBody($this->content()->text());
		$this->mail->setBody($this->text);
//		$this->mail->setHTMLBody($this->content());
		$this->mail->setHTMLBody($this->html, '/home/balancer/bors/composer-mail/vendor/balancer/b2-airbase-mail');

		$mailer = new \Nette\Mail\SendmailMailer;
		$mailer->send($this->mail);
	}

	function to($to)
	{
		$this->mail->addTo($to);
		return $this;
	}

	function to2($email, $title)
	{
		$this->mail->addTo(self::make_recipient(array($email, $title)));
		return $this;
	}
}
