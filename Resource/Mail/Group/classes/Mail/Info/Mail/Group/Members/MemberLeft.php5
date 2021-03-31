<?php
class Mail_Info_Mail_Group_Members_MemberLeft extends Mail_Abstract
{
	protected function generate( $data = array() )
	{
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
//		$wordsMails	= $this->env->getLanguage()->getWords( 'auth/local', 'mails' );

		$sender	= new \CeusMedia\Mail\Address( $data['group']->address );
		$sender->setName( $data['group']->title );
		$this->setSender( $sender );

		$member	= $data['member']->title ? $data['member']->title : $data['member']->address;
		$this->setSubject( 'Mitglied '.$member.' hat die Gruppe "'.$data['group']->title.'" verlassen' );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
		$data['config']		= $this->env->getConfig()->getAll();
		$data['link']		= array(
			'group'			=> $this->env->url.'info/mail/group/view/'.$data['group']->mailGroupId,
		);
		$data['greeting']	= strlen( trim( $data['greeting'] ) ) ? $data['greeting'] : '-';

		$plain	= $this->view->loadContentFile( 'mail/info/mail/group/members/memberLeft.txt', $data );
		$this->setText( $plain );

/*		$html	= preg_replace( "/(http[\S]+)([.,])?/u", '<a href="\\1">\\1</a>\\2', $plain );
		$html	= nl2br( $html );
		$this->setHtml( $html );*/
		return $this;
	}
}
