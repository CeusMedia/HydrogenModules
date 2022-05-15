<?php
class Mail_Work_Issue_Change extends Mail_Work_Issue_Abstract
{
	protected $factsChanges;
	protected $labelsStates;
	protected $note;

	protected function generate(): self
	{
		$data	= $this->data;
		$this->labelsStates		= (array) $this->getWords( 'work/issue', 'states' );
		$this->prepareFacts( $data );

		$issue	= $data['issue'];
		$this->setSubject( 'Problemreport #'.$issue->issueId.': ['.$this->labelsStates[$issue->status].'] '.$issue->title );
		$this->setHtml( $this->renderHtmlBody( $data ) );
		$this->setText( $this->renderTextBody( $data ) );
		return $this;
	}

	protected function prepareFacts( array $data )
	{
		parent::prepareFacts( $data );
		$issue	= $data['issue'];

		$this->factsChanges	= new View_Helper_Mail_Facts( $this->env );
		$this->note	= $this->modelIssueNote->getByIndex( 'issueId', $issue->issueId, array( 'issueNoteId' => 'DESC' ) );
		if( $this->note ){
			$this->note->user	= $this->modelUser->get( $this->note->userId );
			$this->factsChanges	= new View_Helper_Work_Issue_ChangeFacts( $this->env );
			$this->factsChanges->setNote( $this->note );
			$this->changeNote	= new View_Helper_Work_Issue_ChangeNote( $this->env );
			$this->changeNote->setNote( $this->note );
		}
	}

	protected function renderHtmlBody(): string
	{
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
		$words		= $this->env->getLanguage()->getWords( 'work/issue' );
		$issue		= $this->data['issue'];

		$message	= array();
		$panelFacts	= '';
		$panelNote	= '';
		if( $this->note ){
			$worker		= $this->renderUser( $this->note->user, TRUE );
			$message[]	= $worker.' hat einen Problemreport bearbeitet.';
			$panelFacts	= '
				<div class="content-panel">
					<h3>Änderungen</h3>
					<div class="content-panel-inner">
						'.$this->factsChanges->setFormat( View_Helper_Mail_Facts::FORMAT_HTML )->render().'
					</div>
				</div>';
			$panelNote	= '
				<div class="content-panel">
					<h3>Notiz zur Änderung</h3>
					<div class="content-panel-inner">
						'.$this->changeNote->render().'
					</div>
				</div>';

		}
		else
			$message[]	= 'Ein Problemreport wurde bearbeitet.';
		if( $issue->projectId ){
			$projectLink	= UI_HTML_Elements::Link( './manage/project/view/'.$issue->projectId, $issue->project->title );
			$message[]		= 'Du bekommst diese Mail, da du im Projekt '.$projectLink.' involviert bist.';
		}
		$message	= UI_HTML_Tag::create( 'div', join( '<br/>', $message ), array( 'class' => 'alert alert-info' ) );

		$body	= '
<div>
	'.$message.'
	<div class="content-panel">
		<h3>Eintrag</h3>
		<div class="content-panel-inner">
			'.$this->factsMain->setFormat( View_Helper_Mail_Facts::FORMAT_HTML )->render().'
		</div>
	</div>
	'.$panelFacts.'
	'.$panelNote.'
	<div class="content-panel">
		<h3>Informationen</h3>
		<div class="content-panel-inner">
			'.$this->factsAll->setFormat( View_Helper_Mail_Facts::FORMAT_HTML )->render().'
		</div>
	</div>
</div>';
		return $body;
	}

	protected function renderTextBody(): string
	{
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
		$words		= $this->env->getLanguage()->getWords( 'work/issue' );
		$issue		= $this->data['issue'];

		$message	= array();
		if( $this->note )
			$message[]	= $this->renderUser( $this->note->user, FALSE ).' hat einen neuen Problemreport geschrieben.';
		else
			$message[]	= 'Ein neuer Problemreport wurde geschrieben.';
		if( $issue->projectId )
			$message[]	= 'Du bekommst diese Mail, da du im Projekt "'.$issue->project->title.'" involviert bist.';
		$message	= join( PHP_EOL, $message );

		$body		= '
'.View_Helper_Mail_Text::underscore( 'Neuer Problemreport', '=' ).PHP_EOL.'
'.$message.PHP_EOL.'
'.View_Helper_Mail_Text::underscore( 'Eintrag' ).PHP_EOL.'
'.$this->factsMain->setFormat( View_Helper_Mail_Facts::FORMAT_TEXT )->render().PHP_EOL.PHP_EOL.'
'.View_Helper_Mail_Text::underscore( 'Änderungen' ).PHP_EOL.'
'.$this->factsChanges->setFormat( View_Helper_Mail_Facts::FORMAT_TEXT )->render().PHP_EOL.PHP_EOL.'
'.View_Helper_Mail_Text::underscore( 'Informationen' ).PHP_EOL.'
'.$this->factsAll->setFormat( View_Helper_Mail_Facts::FORMAT_TEXT )->render().PHP_EOL.'';

		$list	= array();
		foreach( explode( PHP_EOL, $body ) as $nr => $line )
			$list[]	= View_Helper_Mail_Text::indent( $line, 0, 76 );
		return join( PHP_EOL, $list );
	}
}
