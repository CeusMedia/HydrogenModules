<?php
abstract class Mail_Work_Mission_Change extends Mail_Work_Mission_Abstract
{
	protected $baseUrl;
	protected $facts				= [];
	protected $labels;
	protected $languageSection		= NULL;
	protected $changedFactClassPos	= 'label label-success';
	protected $changedFactClassNeg	= 'label label-important';

	protected function enlistFact( $key, $value, $class = NULL )
	{
		$labelKey	= 'label'.ucfirst( $key );
		if( !is_null( $class ) ){
			if( $class === TRUE )
				$class	= $this->changedFactClassPos;
			else if( $class === FALSE )
				$class	= $this->changedFactClassNeg;
			$value	= UI_HTML_Tag::create( 'span', $value, array( 'class' => $class ) );
		}
		$term		= UI_HTML_Tag::create( 'dt', $this->labels->$labelKey );
		$definition	= UI_HTML_Tag::create( 'dd', $value );
		$this->facts[$key]   =   $term.$definition;
	}

	protected function generate(): self
	{
		$this->baseUrl			= $this->env->getConfig()->get( 'app.base.url' );
		$this->words			= (object) $this->getWords( 'work/mission', $this->languageSection );
		$this->labels			= (object) $this->getWords( 'work/mission', 'add' );
		return $this;
	}

	protected function renderUser( $user, $link = FALSE )
	{
		if( !$user )
			return '-';
		if( $this->env->getModules()->has( 'Members' ) ){
			$helper	= new View_Helper_Member( $this->env );
			$helper->setUser( $user );
			$helper->setMode( 'inline' );
			$helper->setLinkUrl( 'member/view/'.$user->userId );
			$userLabel	= $helper->render();
		}
		else{
			$iconUser	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'not_icon-user fa fa-fw fa-user' ) );
			$fullname	= '('.$user->firstname.' '.$user->surname.')';
			$fullname	= UI_HTML_Tag::create( 'small', $fullname, array( 'class' => 'muted' ) );
			$userLabel	= $iconUser.'&nbsp;'.$user->username.'&nbsp;'.$fullname;
		}
		return $userLabel;
	}

	protected function renderUserAsText( $user )
	{
		if( !$user )
			return '-';
		$fullname	= '';
		if( strlen( trim( $user->firstname ) ) && strlen( trim( $user->surname ) ) ){
			$parts	= [];
			if( strlen( trim( $user->firstname ) ) )
				$parts[]	= trim( $user->firstname );
			if( strlen( trim( $user->surname ) ) )
				$parts[]	= trim( $user->surname );
			$fullname	= ' ('.join( ' ', $parts ).')';
		}
		return $user->username.$fullname;
	}

	protected function renderLinkedTitle( $mission )
	{
		return UI_HTML_Tag::create( 'a', $mission->title, array(
			'href'	=> './work/mission/view/'.$mission->missionId
		) );
	}

	protected function setSubjectFromMission( $mission )
	{
		$subjectKey	= $mission->type ? 'subjectEvent' : 'subjectTask';
		$subject	= sprintf( $this->words->$subjectKey, $mission->title );
		$this->setSubject( $subject );
	}
}
