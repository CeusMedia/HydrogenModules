<?php
class Mail_Info_Mail_Group_Member_Rejected extends Mail_Abstract
{
	protected function generate(): static
	{
		$data		= $this->data;
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
//		$wordsMails	= $this->env->getLanguage()->getWords( 'auth/local', 'mails' );

		$sender	= new \CeusMedia\Mail\Address( $data['group']->address );
		$sender->setName( $data['group']->title );
		$this->setSender( $sender );

//		$member	= $data['member']->title ? $data['member']->title : $data['member']->address;
		$this->setSubject( 'Beitritt zur Gruppe "'.$data['group']->title.'" abgelehnt' );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
		$data['config']		= $this->env->getConfig()->getAll();
		$data['link']		= [
			'group'			=> $this->env->url.'info/mail/group/view/'.$data['group']->mailGroupId,
		];

		$plain	= $this->loadContentFile( 'mail/info/mail/group/member/rejected.txt', $data ) ?? '';
		$this->setText( $plain );
		return $this;
	}
}
