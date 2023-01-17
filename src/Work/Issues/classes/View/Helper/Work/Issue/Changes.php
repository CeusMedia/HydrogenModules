<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Work_Issue_Changes
{
	protected $env;
	protected $modelNote;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$this->modelNote	= new Model_Issue_Note( $this->env );
	}

	public function render(): string
	{
		if( !$this->issue )
			throw new RuntimeException( 'No issue set.' );
		$list			= [];
		$notes			= $this->modelNote->getAllByIndex( 'issueId', $this->issue->issueId );
		$helper			= new View_Helper_Work_Issue_ChangeNote( $this->env );
		$helperFacts	= new View_Helper_Work_Issue_ChangeFacts( $this->env );
		$helperNote		= new View_Helper_Work_Issue_ChangeNote( $this->env );
		foreach( $notes as $note ){
			$helperFacts->setNote( $note );
			$helperNote->setNote( $note );
			$list[]		= HtmlTag::create( 'tr',
				HtmlTag::create( 'td',
					HtmlTag::create( 'div', array(
						HtmlTag::create( 'div', $helperFacts->render(), [
							'class'	=> 'span5',
							'id'	=> 'issue-change-list-facts'
						] ),
						HtmlTag::create( 'div', $helperNote->render(), [
							'class'	=> 'span7',
							'id'	=> 'issue-change-list-note'
						] ),
						'<br/>'
					), ['class' => 'issue-note row-fluid'] )
				)
			);
		}
		$tbody		= HtmlTag::create( 'tbody', $list );
		$table		= HtmlTag::create( 'table', $tbody, ['class' => 'table table-striped table-fixed'] );
		return $table;
	}

	public function setIssue( $issue ): self
	{
		$this->issue	= $issue;
		return $this;
	}
}
