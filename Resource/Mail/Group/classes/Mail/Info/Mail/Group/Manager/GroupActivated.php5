<?php
class Mail_Info_Mail_Group_Manager_GroupActivated extends Mail_Abstract
{
	protected function generate( $data = array() )
	{
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
//		$wordsMails	= $this->env->getLanguage()->getWords( 'auth/local', 'mails' );

		$this->setSubject( 'Gruppe "'.$data['group']->title.'" aktiviert' );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
		$data['config']		= $this->env->getConfig()->getAll();
		$data['link']		= array(
			'group'			=> $this->env->url.'work/mail/group/view/'.$data['group']->mailGroupId,
		);

		$plain	= $this->view->loadContentFile( 'mail/info/mail/group/manager/groupActivated.txt', $data );
		$this->setText( $plain );

/*		$html	= preg_replace( "/(http[\S]+)([.,])?/u", '<a href="\\1">\\1</a>\\2', $plain );
		$html	= nl2br( $html );
		$this->setHtml( $html );*/
		return $this;
	}
}
