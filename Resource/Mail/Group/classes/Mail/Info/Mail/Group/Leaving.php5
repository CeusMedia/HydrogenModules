<?php
class Mail_Info_Mail_Group_Leaving extends Mail_Abstract{

	protected function generate( $data = array() ){
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
//		$wordsMails	= $this->env->getLanguage()->getWords( 'auth/local', 'mails' );

		$member	= $data['member']->title ? $data['member']->title : $data['member']->address;
		$this->setSubject( 'Ihr Austritt aus der Gruppe "'.$data['group']->title.'"' );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
		$data['config']		= $this->env->getConfig()->getAll();
		$data['link']		= array(
			'confirm'		=> $this->env->url.'info/mail/group/completeMemberAction/'.$data['action']->mailGroupActionId.'/'.$data['action']->uuid,
		);

		$plain	= $this->view->loadContentFile( 'mail/info/mail/group/leaving.txt', $data );
		$this->setText( $plain );

		return (object) array(
			'plain'	=> $plain,
			'html'	=> NULL,
		);
	}
}
?>
