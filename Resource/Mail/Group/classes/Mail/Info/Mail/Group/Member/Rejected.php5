<?php
class Mail_Info_Mail_Group_Member_Rejected extends Mail_Abstract{

	protected function generate( $data = array() ){
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
//		$wordsMails	= $this->env->getLanguage()->getWords( 'auth/local', 'mails' );

//		$member	= $data['member']->title ? $data['member']->title : $data['member']->address;
		$this->setSubject( 'Ihr Beitritt in der Gruppe "'.$data['group']->title.'"' );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
		$data['config']		= $this->env->getConfig()->getAll();
		$data['member']		= array(
			'title'		=> $data['name'],
			'address'	=> $data['address'],
		);
		$data['link']		= array(
			'group'			=> $this->env->url.'info/mail/group/view/'.$data['group']->mailGroupId,
		);

		$plain	= $this->view->loadContentFile( 'mail/info/mail/group/member/rejected.txt', $data );
		$this->setText( $plain );

		return (object) array(
			'plain'	=> $plain,
			'html'	=> NULL,
		);
	}
}
?>
