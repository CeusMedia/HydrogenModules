<?php
class Mail_Info_Mail_Group_Dectivated extends Mail_Abstract
{
	protected function generate(): static
	{
		$data		= $this->data;
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
//		$wordsMails	= $this->env->getLanguage()->getWords( 'auth/local', 'mails' );

		$member	= $data['member']->title ?: $data['member']->address;
		$this->setSubject( 'Mitgliedschaft in der Gruppe "'.$data['group']->title.'" abgelehnt' );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
		$data['config']		= $this->env->getConfig()->getAll();
		$data['link']		= [
			'leave'			=> $this->env->url.'info/mail/group/leave/'.$data['group']->mailGroupId,
		];

		$plain	= $this->loadContentFile( 'mail/info/mail/group/deactivated.txt', $data ) ?? '';
		$this->setText( $plain );
		return $this;
	}
}
