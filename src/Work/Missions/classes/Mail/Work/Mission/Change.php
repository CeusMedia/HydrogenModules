<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

abstract class Mail_Work_Mission_Change extends Mail_Work_Mission_Abstract
{
	protected string $baseUrl;
	protected array $facts					= [];
	protected array $labels;
	protected ?string $languageSection		= NULL;
	protected string $changedFactClassPos	= 'label label-success';
	protected string $changedFactClassNeg	= 'label label-important';
	protected array $words;

	protected function enlistFact( string $key, string $value, $class = NULL ): void
	{
		$labelKey	= 'label'.ucfirst( $key );
		if( !is_null( $class ) ){
			if( $class === TRUE )
				$class	= $this->changedFactClassPos;
			else if( $class === FALSE )
				$class	= $this->changedFactClassNeg;
			$value	= HtmlTag::create( 'span', $value, ['class' => $class] );
		}
		$term		= HtmlTag::create( 'dt', $this->labels[$labelKey] );
		$definition	= HtmlTag::create( 'dd', $value );
		$this->facts[$key]	= $term.$definition;
	}

	protected function generate(): self
	{
		$this->baseUrl			= $this->env->getConfig()->get( 'app.base.url' );
		$this->words			= $this->getWords( 'work/mission', $this->languageSection );
		$this->labels			= $this->getWords( 'work/mission', 'add' );
		return $this;
	}

	protected function renderUser( object $user, $link = FALSE ): string
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
			$iconUser	= HtmlTag::create( 'i', '', ['class' => 'not_icon-user fa fa-fw fa-user'] );
			$fullname	= '('.$user->firstname.' '.$user->surname.')';
			$fullname	= HtmlTag::create( 'small', $fullname, ['class' => 'muted'] );
			$userLabel	= $iconUser.'&nbsp;'.$user->username.'&nbsp;'.$fullname;
		}
		return $userLabel;
	}

	protected function renderUserAsText( object $user ): string
	{
		if( !$user )
			return '-';
		$fullname	= '';
		if( strlen( trim( $user->firstname ) ) && strlen( trim( $user->surname ) ) ){
			$parts	= [];
			if( 0 !== strlen( trim( $user->firstname ) ) )
				$parts[]	= trim( $user->firstname );
			if( 0 !== strlen( trim( $user->surname ) ) )
				$parts[]	= trim( $user->surname );
			$fullname	= ' ('.join( ' ', $parts ).')';
		}
		return $user->username.$fullname;
	}

	protected function renderLinkedTitle( object $mission ): string
	{
		return HtmlTag::create( 'a', $mission->title, [
			'href'	=> './work/mission/view/'.$mission->missionId
		] );
	}

	protected function setSubjectFromMission( object $mission ): self
	{
		$subjectKey	= $mission->type ? 'subjectEvent' : 'subjectTask';
		$subject	= sprintf( $this->words[$subjectKey], $mission->title );
		$this->setSubject( $subject );
		return $this;
	}
}
