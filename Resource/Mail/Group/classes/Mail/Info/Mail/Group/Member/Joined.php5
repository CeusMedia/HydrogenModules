<?php
class Mail_Info_Mail_Group_Member_Joined extends Mail_Abstract{

	protected function generate( $data = array() ){
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
//		$wordsMails	= $this->env->getLanguage()->getWords( 'auth/local', 'mails' );

		$sender	= new \CeusMedia\Mail\Address( $data['group']->address );
		$sender->setName( $data['group']->title );
		$this->setSender( $sender );

		$member	= $data['member']->title ? $data['member']->title : $data['member']->address;
		$this->setSubject( 'Beitritt in der Gruppe "'.$data['group']->title.'" bestätigt' );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
		$data['config']		= $this->env->getConfig()->getAll();
		$data['link']		= array(
			'group'			=> $this->env->url.'info/mail/group/view/'.$data['group']->mailGroupId,
			'leave'			=> $this->env->url.'info/mail/group/leave/'.$data['group']->mailGroupId,
		);

		$plain	= $this->view->loadContentFile( 'mail/info/mail/group/member/joined.txt', $data );
		$this->setText( $plain );

		return (object) array(
			'plain'	=> $plain,
			'html'	=> NULL,
		);
	}
}
?>
