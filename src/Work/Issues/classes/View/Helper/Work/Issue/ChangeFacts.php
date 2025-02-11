<?php

use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Work_Issue_ChangeFacts
{
	public const FORMAT_HTML		= 1;
	public const FORMAT_TEXT		= 2;
	public const FORMATS			= [
		self::FORMAT_HTML,
		self::FORMAT_TEXT,
	];

	protected Environment $env;
	protected Model_User $modelUser;
	protected Model_Issue_Change $modelChange;
	protected ?View_Helper_Mail_Facts $factsChanges		= NULL;
	protected ?object $note								= NULL;
	protected int $format								= self::FORMAT_HTML;

	/**
	 *	@param		Environment		$env
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$this->modelUser	= new Model_User( $this->env );
		$this->modelChange	= new Model_Issue_Change( $this->env );
	}

	/**
	 *	@return		string
	 */
	public function render(): string
	{
		if( $this->format === self::FORMAT_TEXT )
			return $this->renderAsText();
		return $this->renderAsHtml();
	}

	/**
	 *	@param		int		$format
	 *	@return		self
	 */
	public function setFormat( int $format ): self
	{
		$this->format	= $format;
		return $this;
	}

	/**
	 *	@param		object		$note
	 *	@return		self
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function setNote( object $note ): self
	{
		$this->note	= $note;
		$this->prepareFacts();
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function prepareFacts(): void
	{
		$words			= $this->env->getLanguage()->getWords( 'work/issue' );
		$changerHtml	= '-';
		$changerText	= '-';
		if( $this->note->userId ){
			$this->note->user	= $this->modelUser->get( $this->note->userId );
			$changerText	= $this->note->user->username;
			if( class_exists( 'View_Helper_Member' ) ){
				$helper		= new View_Helper_Member( $this->env );
				$helper->setLinkUrl( 'member/view/%s' );
				$helper->setUser( $this->note->userId );
				$changerHtml	= $helper->render();
			}
			else{
				$changerHtml	= '<a href="./manage/user/edit/'.$this->note->user->userId.'">'.$this->note->user->username.'</a>';
				$changerHtml	= '<span class="role role'.$this->note->user->roleId.'">'.$changerHtml.'</span>';
			}
		}

		$this->note->user		= $this->modelUser->get( $this->note->userId );
		$changes	= $this->modelChange->getAllByIndex( 'noteId', $this->note->issueNoteId );

		$changedAt		= '<span class="issue-note-date">'.date( 'd.m.Y H:i:s', $this->note->timestamp ).'</span>';

		$this->factsChanges		= new View_Helper_Mail_Facts();
		$this->factsChanges->setLabels( $words['changes'] );
		$this->factsChanges->setListClass( 'facts-vertical' );
		$this->factsChanges->setTextLabelLength( 13 );
		$this->factsChanges->add( 'Bearbeiter', $changerHtml, $changerText );
		$this->factsChanges->add( 'Zeitpunkt', $changedAt, date( 'd.m.Y H:i:s', $this->note->timestamp ) );

		$helper			= new View_Helper_Work_Issue_ChangeFact( $this->env );
		foreach( $changes as $change ){
			$helper->setChange( $change );
			$this->factsChanges->add(
				$change->type,
				$helper->setFormat( View_Helper_Mail_Facts::FORMAT_HTML )->render(),
				$helper->setFormat( View_Helper_Mail_Facts::FORMAT_TEXT )->render()
			);
		}
	}

	protected function renderAsHtml(): string
	{
		if( !$this->note )
			return '';
		return $this->factsChanges->setFormat( View_Helper_Mail_Facts::FORMAT_HTML )->render();
	}


	protected function renderAsText(): string
	{
		if( !$this->note )
			return '';
		return $this->factsChanges->setFormat( View_Helper_Mail_Facts::FORMAT_TEXT )->render();
	}
}
