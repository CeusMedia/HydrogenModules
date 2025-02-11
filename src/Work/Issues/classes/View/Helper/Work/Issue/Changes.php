<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Work_Issue_Changes
{
	protected Environment $env;
	protected Model_Issue_Note $modelNote;
	protected ?object $issue		= NULL;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$this->modelNote	= new Model_Issue_Note( $this->env );
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function render(): string
	{
		if( !$this->issue )
			throw new RuntimeException( 'No issue set.' );
		$list			= [];
		$notes			= $this->modelNote->getAllByIndex( 'issueId', $this->issue->issueId );
		$helperFacts	= new View_Helper_Work_Issue_ChangeFacts( $this->env );
		$helperNote		= new View_Helper_Work_Issue_ChangeNote( $this->env );
		foreach( $notes as $note ){
			$helperFacts->setNote( $note );
			$helperNote->setNote( $note );
			$list[]		= HtmlTag::create( 'tr',
				HtmlTag::create( 'td',
					HtmlTag::create( 'div', [
						HtmlTag::create( 'div', $helperFacts->render(), [
							'class'	=> 'span5',
							'id'	=> 'issue-change-list-facts'
						] ),
						HtmlTag::create( 'div', $helperNote->render(), [
							'class'	=> 'span7',
							'id'	=> 'issue-change-list-note'
						] ),
						'<br/>'
					], ['class' => 'issue-note row-fluid'] )
				)
			);
		}
		$tbody		= HtmlTag::create( 'tbody', $list );
		return HtmlTag::create( 'table', $tbody, ['class' => 'table table-striped table-fixed'] );
	}

	/**
	 *	@param		object		$issue
	 *	@return		self
	 */
	public function setIssue( object $issue ): self
	{
		$this->issue	= $issue;
		return $this;
	}
}
