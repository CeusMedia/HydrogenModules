<?php
class Mail_Info_Mail_Group_Member_Joining extends Mail_Abstract{

	protected function generate( $data = array() ){
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
//		$wordsMails	= $this->env->getLanguage()->getWords( 'auth/local', 'mails' );

		$sender	= new \CeusMedia\Mail\Address( $data['group']->address );
		$sender->setName( $data['group']->title );
		$this->setSender( $sender );

		$member	= $data['member']->title ? $data['member']->title : $data['member']->address;
		$this->setSubject( 'Ihr Beitritt in der Gruppe "'.$data['group']->title.'"' );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
		$data['config']		= $this->env->getConfig()->getAll();
		$data['link']		= array(
			'group'			=> $this->env->url.'info/mail/group/view/'.$data['group']->mailGroupId,
			'leave'			=> $this->env->url.'info/mail/group/leave/'.$data['group']->mailGroupId,
			'confirm'		=> $this->env->url.'info/mail/group/completeMemberAction/'.$data['action']->mailGroupActionId.'/'.$data['action']->uuid,
		);

		$plain	= $this->view->loadContentFile( 'mail/info/mail/group/member/joining.txt', $data );
		$this->setText( $plain );

		return (object) array(
			'plain'	=> $plain,
			'html'	=> NULL,
		);
	}
}
?>
