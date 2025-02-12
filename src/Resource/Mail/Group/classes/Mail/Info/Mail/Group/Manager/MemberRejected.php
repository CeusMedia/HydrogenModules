<?php
class Mail_Info_Mail_Group_Manager_MemberRejected extends Mail_Abstract
{
	protected function generate(): static
	{
		$data		= $this->data;
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
//		$wordsMails	= $this->env->getLanguage()->getWords( 'auth/local', 'mails' );

//		$member	= $data['member']->title ? $data['member']->title : $data['member']->address;
		$this->setSubject( 'Mitgliedschaft in der Gruppe "'.$data['group']->title.'" abgelehnt' );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
		$data['config']		= $this->env->getConfig()->getAll();
		$data['greeting']	= strlen( trim( $data['greeting'] ) ) ? $data['greeting'] : '-';
//		$data['member']->link	= $this->env->url.'work/mail/group/member/edit/'.$data['member']->mailGroupMemberId;
		$data['link']		= [
			'group'			=> $this->env->url.'work/mail/group/edit/'.$data['group']->mailGroupId,
			'member'		=> $this->env->url.'work/mail/group/member/edit/'.$data['member']->mailGroupMemberId,
		];
		$data['member']		= [
			'title'		=> $data['name'],
			'address'	=> $data['address'],
		];

		$plain	= $this->loadContentFile( 'mail/info/mail/group/manager/memberRejected.txt', $data ) ?? '';
		$this->setText( $plain );

/*		$html	= preg_replace( "/(http[\S]+)([.,])?/u", '<a href="\\1">\\1</a>\\2', $plain );
		$html	= nl2br( $html );
		$this->setHtml( $html );*/
		return $this;
	}
}
