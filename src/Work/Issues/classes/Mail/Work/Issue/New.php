<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class Mail_Work_Issue_New extends Mail_Work_Issue_Abstract
{
	/**
	 *	@return		self
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generate(): static
	{
		$data	= $this->data;
		$this->prepareFacts( $data );

		$issue		= $this->data['issue'];
		$subject	= $this->words['mail']['subjectNew'];
		$this->setSubject( sprintf( $subject, $issue->issueId, $issue->title ) );

		$this->setHtml( $this->renderHtmlBody() );
		$this->setText( $this->renderTextBody() );
		return $this;
	}

	/**
	 *	@return		string
	 */
	protected function renderHtmlBody(): string
	{
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
		$issue		= $this->data['issue'];
		$message	= [];
		if( $issue->reporterId ){
			$reporter	= $this->renderUser( $issue->reporter );
			$message[]	= sprintf( $this->words['mail']['messageNewBy'], $reporter );
		}
		else
			$message[]	= $this->words['mail']['messageNew'];
		if( $issue->projectId ){
			$projectLink	= HtmlElements::Link( './manage/project/view/'.$issue->projectId, $issue->project->title );
			$message[]		= sprintf( $this->words['mail']['messageInvolved'], $projectLink );
		}
		$message	= HtmlTag::create( 'div', join( '<br/>', $message ), ['class' => 'alert alert-info'] );

		$body		= '
<div>
	'.$message.'
	<div class="content-panel">
		<h3>'.$this->words['mail']['headingIssue'].'</h3>
		<div class="content-panel-inner">
			'.$this->factsMain->setFormat( View_Helper_Mail_Facts::FORMAT_HTML )->render().'
		</div>
	</div>
	<div class="content-panel">
		<h3>'.$this->words['mail']['headingInfo'].'</h3>
		<div class="content-panel-inner">
			'.$this->factsAll->setFormat( View_Helper_Mail_Facts::FORMAT_HTML )->render().'
		</div>
	</div>
</div>';
		return $body;
	}

	/**
	 *	@return		string
	 */
	protected function renderTextBody(): string
	{
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
		$issue		= $this->data['issue'];
		$message	= [];
		if( $issue->reporterId )
			$message[]	= sprintf( $this->words['mail']['messageNewBy'], $this->renderUser( $issue->reporter, FALSE ) );
		else
			$message[]	= $this->words['mail']['messageNew'];
		if( $issue->projectId )
			$message[]	= sprintf( $this->words['mail']['messageInvolved'], '"'.$issue->project->title.'"' );
		$message	= join( PHP_EOL, $message );

		$body		= '
'.View_Helper_Mail_Text::underscore( $this->words['mail']['headingNew'], '=' ).PHP_EOL.'
'.$message.PHP_EOL.'
'.View_Helper_Mail_Text::underscore( $this->words['mail']['headingNewEntry'] ).PHP_EOL.'
'.$this->factsMain->setFormat( View_Helper_Mail_Facts::FORMAT_TEXT )->render().PHP_EOL.PHP_EOL.'
'.View_Helper_Mail_Text::underscore( $this->words['mail']['headingInfo'] ).PHP_EOL.'
'.$this->factsAll->setFormat( View_Helper_Mail_Facts::FORMAT_TEXT )->render().PHP_EOL;

		$list	= [];
		foreach( explode( PHP_EOL, $body ) as $line )
			$list[]	= View_Helper_Mail_Text::indent( $line, 0, 76 );
		return join( PHP_EOL, $list );
	}
}
