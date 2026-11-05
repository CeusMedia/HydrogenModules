<?php
class Mail_Info_Mail_Group_Dectivated extends Mail_Abstract
{
	protected function generate(): static
	{
		$data		= $this->data;
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
//		$wordsMails	= $this->env->getLanguage()->getWords( 'auth/local', 'mails' );

		$member	= $data['member']->title ?: $data['member']->address;
		$wordsMails	= $this->env->getLanguage()->getSection( 'info/mail/group', 'mails' );
		$this->setSubject( sprintf( $wordsMails['groupMemberDeactivated'], $data['group']->title ) );

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
