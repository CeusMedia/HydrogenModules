<?php
class Mail_Info_Mail_Group_Members_MemberDeactivated extends Mail_Abstract
{
	protected function generate( $data = array() )
	{
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
//		$wordsMails	= $this->env->getLanguage()->getWords( 'auth/local', 'mails' );

		$sender	= new \CeusMedia\Mail\Address( $data['group']->address );
		$sender->setName( $data['group']->title );
		$this->setSender( $sender );

		$member	= $data['member']->title ? $data['member']->title : $data['member']->address;
		$this->setSubject( 'Mitglied '.$member.' in der Gruppe "'.$data['group']->title.'" wurde deaktiviert' );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
		$data['config']		= $this->env->getConfig()->getAll();
		$data['link']		= array(
			'group'			=> $this->env->url.'info/mail/group/view/'.$data['group']->mailGroupId,
		);

		$plain	= $this->view->loadContentFile( 'mail/info/mail/group/members/memberDeactivated.txt', $data );
		$this->setText( $plain );
		return $this;
	}
}
