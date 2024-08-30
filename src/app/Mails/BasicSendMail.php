<?php

namespace App\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BasicSendMail extends Mailable
{
	use Queueable, SerializesModels;

	protected $title;
	protected $text;
	protected $templateName;
	protected $data;
	protected $file;

	/**
	* Create a new message instance.
	*
	* @return void
	*/
	public function __construct($templateName, $title, $data,$file=null)
	{
		$this->templateName = $templateName;
		$this->title = $title;
		$this->data = $data;
		$this->file = $file;
	}




	// https://www.ritolab.com/entry/38
	// を参考に実装した

	/**
	* Build the message.
	*
	* @return $this
	*/
	public function build()
	{
		// htmlベースのメール送信は
		// $this->view($this->templateName)

		// テキストベースは
		// $this->text($this->templateName)

		// なぜか
		// $this->view($this->templateName)->text($this->templateName)
		// というのもできるみたい

	    $mail=$this->text($this->templateName)
	    ->subject($this->title)
	    ->with('data', $this->data);

	    if($this->file){
	        $mail->attach($this->file);
	    }

		return $mail;
	}
}
