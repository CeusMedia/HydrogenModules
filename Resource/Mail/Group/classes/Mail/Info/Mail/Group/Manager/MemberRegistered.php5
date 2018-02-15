<?php
class Mail_Info_Mail_Group_Manager_MemberRegistered extends Mail_Abstract{

	protected function generate( $data = array() ){
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
//		$wordsMails	= $this->env->getLanguage()->getWords( 'auth/local', 'mails' );

		$member	= $data['member']->title ? $data['member']->title : $data['member']->address;
		$this->setSubject( 'Gruppe "'.$data['group']->title.'": '.$member.' ist beigetreten und benötigt Freigabe' );

		$data['appTitle']	= $wordsMain['main']['title'];
		$data['appBaseUrl']	= $this->env->url;
		$data['config']		= $this->env->getConfig()->getAll();
		$data['greeting']	= strlen( trim( $data['greeting'] ) ) ? $data['greeting'] : '-';
		$data['member']->link	= $this->env->url.'work/mail/group/member/edit/'.$data['member']->mailGroupMemberId;
		$data['link']		= array(
			'activate'		=> $this->env->url.'work/mail/group/setMemberStatus/'.$data['group']->mailGroupId.'/'.$data['member']->mailGroupMemberId.'/'.Model_Mail_Group_Member::STATUS_ACTIVATED,
			'deactivate'	=> $this->env->url.'work/mail/group/setMemberStatus/'.$data['group']->mailGroupId.'/'.$data['member']->mailGroupMemberId.'/'.Model_Mail_Group_Member::STATUS_DEACTIVATED,
			'group'			=> $this->env->url.'work/mail/group/edit/'.$data['group']->mailGroupId,
		);

		$plain	= $this->view->loadContentFile( 'mail/info/mail/group/manager/member/registered.txt', $data );
		$this->setText( $plain );

/*		$html	= preg_replace( "/(http[\S]+)([.,])?/u", '<a href="\\1">\\1</a>\\2', $plain );
		$html	= nl2br( $html );
		$this->setHtml( $html );*/

		return (object) array(
			'plain'	=> $plain,
			'html'	=> NULL,
		);
	}
}
?>
