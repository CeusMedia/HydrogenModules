<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class Mail_Work_Issue_New extends Mail_Work_Issue_Abstract
{
	protected function generate(): self
	{
		$data	= $this->data;
		$this->prepareFacts( $data );

		$issue		= $this->data['issue'];
		$subject	= 'Neuer Problemreport #%s: %s';
		$this->setSubject( sprintf( $subject, $issue->issueId, $issue->title ) );

		$this->setHtml( $this->renderHtmlBody() );
		$this->setText( $this->renderTextBody() );
		return $this;
	}

	protected function renderHtmlBody(): string
	{
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
		$issue		= $this->data['issue'];
		$message	= [];
		if( $issue->reporterId ){
			$reporter	= $this->renderUser( $issue->reporter, TRUE );
			$message[]	= $reporter.' hat einen neuen Problemreport geschrieben.';
		}
		else
			$message[]	= 'Ein neuer Problemreport wurde geschrieben.';
		if( $issue->projectId ){
			$projectLink	= UI_HTML_Elements::Link( './manage/project/view/'.$issue->projectId, $issue->project->title );
			$message[]		= 'Du bekommst diese Mail, da du im Projekt '.$projectLink.' involviert bist.';
		}
		$message	= HtmlTag::create( 'div', join( '<br/>', $message ), array( 'class' => 'alert alert-info' ) );

		$body		= '
<div>
	'.$message.'
	<div class="content-panel">
		<h3>Eintrag</h3>
		<div class="content-panel-inner">
			'.$this->factsMain->setFormat( View_Helper_Mail_Facts::FORMAT_HTML )->render().'
		</div>
	</div>
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
		$issue		= $this->data['issue'];
		$message	= [];
		if( $issue->reporterId )
			$message[]	= $this->renderUser( $issue->reporter, FALSE ).' hat einen neuen Problemreport geschrieben.';
		else
			$message[]	= 'Ein neuer Problemreport wurde geschrieben.';
		if( $issue->projectId )
			$message[]	= 'Du bekommst diese Mail, da du im Projekt "'.$issue->project->title.'" involviert bist.';
		$message	= join( PHP_EOL, $message );

		$body		= '
'.View_Helper_Mail_Text::underscore( 'Neuer Problemreport', '=' ).PHP_EOL.'
'.$message.PHP_EOL.'
'.View_Helper_Mail_Text::underscore( 'Neuer Eintrag' ).PHP_EOL.'
'.$this->factsMain->setFormat( View_Helper_Mail_Facts::FORMAT_TEXT )->render().PHP_EOL.PHP_EOL.'
'.View_Helper_Mail_Text::underscore( 'Informationen' ).PHP_EOL.'
'.$this->factsAll->setFormat( View_Helper_Mail_Facts::FORMAT_TEXT )->render().PHP_EOL.'';

		$list	= [];
		foreach( explode( PHP_EOL, $body ) as $nr => $line )
			$list[]	= View_Helper_Mail_Text::indent( $line, 0, 76 );
		return join( PHP_EOL, $list );
	}
}
