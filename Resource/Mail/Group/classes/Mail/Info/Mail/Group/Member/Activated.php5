<?php
class Mail_Info_Mail_Group_Member_Activated extends Mail_Abstract{

	protected function generate( $data = array() ){
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
//		$wordsMails	= $this->env->getLanguage()->getWords( 'auth/local', 'mails' );

		$member	= $data['member']->title ? $data['member']->title : $data['member']->address;
		$this->setSubject( 'Mitgliedschaft in der Gruppe "'.$data['group']->title.'" aktiviert' );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
		$data['config']		= $this->env->getConfig()->getAll();
		$data['link']		= array(
		);

		$plain	= $this->view->loadContentFile( 'mail/info/mail/group/member/activated.txt', $data );
		$this->setText( $plain );

		return (object) array(
			'plain'	=> $plain,
			'html'	=> NULL,
		);
	}
}
?>
