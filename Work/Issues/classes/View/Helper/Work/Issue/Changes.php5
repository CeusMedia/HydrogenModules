<?php
class View_Helper_Work_Issue_Changes{

	public function __construct( $env ){
		$this->env	= $env;
		$this->modelNote	= new Model_Issue_Note( $this->env );
	}

	public function setIssue( $issue ){
		$this->issue	= $issue;
	}

	public function render(){
		if( !$this->issue )
			throw new RuntimeException( 'No issue set.' );
		$list			= array();
		$notes			= $this->modelNote->getAllByIndex( 'issueId', $this->issue->issueId );
		$helper			= new View_Helper_Work_Issue_ChangeNote( $this->env );
		$helperFacts	= new View_Helper_Work_Issue_ChangeFacts( $this->env );
		$helperNote		= new View_Helper_Work_Issue_ChangeNote( $this->env );
		foreach( $notes as $note ){
			$helperFacts->setNote( $note );
			$helperNote->setNote( $note );
			$list[]		= UI_HTML_Tag::create( 'tr',
				UI_HTML_Tag::create( 'td',
					UI_HTML_Tag::create( 'div', array(
						UI_HTML_Tag::create( 'div', $helperFacts->render(), array(
							'class'	=> 'span5',
							'id'	=> 'issue-change-list-facts'
						) ),
						UI_HTML_Tag::create( 'div', $helperNote->render(), array(
							'class'	=> 'span7',
							'id'	=> 'issue-change-list-note'
						) ),
						'<br/>'
					), array( 'class' => 'issue-note row-fluid' ) )
				)
			);
		}
		$tbody		= UI_HTML_Tag::create( 'tbody', $list );
		$table		= UI_HTML_Tag::create( 'table', $tbody, array( 'class' => 'table table-striped table-fixed' ) );
		return $table;
	}
}
?>
