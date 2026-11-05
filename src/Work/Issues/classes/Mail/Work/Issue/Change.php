<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class Mail_Work_Issue_Change extends Mail_Work_Issue_Abstract
{
	/** @var View_Helper_Mail_Facts|View_Helper_Work_Issue_ChangeFacts|NULL $factsChanges */
	protected View_Helper_Mail_Facts|View_Helper_Work_Issue_ChangeFacts|NULL $factsChanges		= NULL;
	protected ?View_Helper_Work_Issue_ChangeNote $changeNote		= NULL;
	protected array $labelsStates;
	protected ?object $note											= NULL;

	/**
	 *	@return		self
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function generate(): static
	{
		$data	= $this->data;
		$this->labelsStates		= $this->getWords( 'work/issue', 'states' );
		$this->prepareFacts( $data );

		$issue	= $data['issue'];
		$this->setSubject( sprintf( $this->words['mail']['subjectChange'], $issue->issueId, $this->labelsStates[$issue->status], $issue->title ) );
		$this->setHtml( $this->renderHtmlBody() );
		$this->setText( $this->renderTextBody() );
		return $this;
	}

	/**
	 *	@param		array		$data
	 *	@return		void
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function prepareFacts( array $data ): void
	{
		parent::prepareFacts( $data );
		$issue	= $data['issue'];

		$this->factsChanges	= new View_Helper_Mail_Facts();
		$this->note	= $this->modelIssueNote->getByIndex( 'issueId', $issue->issueId, ['issueNoteId' => 'DESC'] );
		if( $this->note ){
			$this->note->user	= $this->modelUser->get( $this->note->userId );
			$this->factsChanges	= new View_Helper_Work_Issue_ChangeFacts( $this->env );
			$this->factsChanges->setNote( $this->note );
			$this->changeNote	= new View_Helper_Work_Issue_ChangeNote( $this->env );
			$this->changeNote->setNote( $this->note );
		}
	}

	/**
	 *	@return		string
	 */
	protected function renderHtmlBody(): string
	{
		$wordsMain	= $this->env->getLanguage()->getWords( 'main' );
		$words		= $this->env->getLanguage()->getWords( 'work/issue' );
		$issue		= $this->data['issue'];

		$message	= [];
		$panelFacts	= '';
		$panelNote	= '';
		if( $this->note ){
			$worker		= $this->renderUser( $this->note->user, TRUE );
			$message[]	= sprintf( $this->words['mail']['messageChangedBy'], $worker );
			$panelFacts	= '
				<div class="content-panel">
					<h3>'.$this->words['mail']['headingChanges'].'</h3>
					<div class="content-panel-inner">
						'.$this->factsChanges->setFormat( View_Helper_Mail_Facts::FORMAT_HTML )->render().'
					</div>
				</div>';
			$panelNote	= '
				<div class="content-panel">
					<h3>'.$this->words['mail']['headingNote'].'</h3>
					<div class="content-panel-inner">
						'.$this->changeNote->render().'
					</div>
				</div>';

		}
		else
			$message[]	= $this->words['mail']['messageChanged'];
		if( $issue->projectId ){
			$projectLink	= HtmlElements::Link( './manage/project/view/'.$issue->projectId, $issue->project->title );
			$message[]		= sprintf( $this->words['mail']['messageInvolved'], $projectLink );
		}
		$message	= HtmlTag::create( 'div', join( '<br/>', $message ), ['class' => 'alert alert-info'] );

		$body	= '
<div>
	'.$message.'
	<div class="content-panel">
		<h3>'.$this->words['mail']['headingIssue'].'</h3>
		<div class="content-panel-inner">
			'.$this->factsMain->setFormat( View_Helper_Mail_Facts::FORMAT_HTML )->render().'
		</div>
	</div>
	'.$panelFacts.'
	'.$panelNote.'
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
		$words		= $this->env->getLanguage()->getWords( 'work/issue' );
		$issue		= $this->data['issue'];

		$message	= [];
		if( $this->note )
			$message[]	= sprintf( $this->words['mail']['messageNewBy'], $this->renderUser( $this->note->user, FALSE ) );
		else
			$message[]	= $this->words['mail']['messageNew'];
		if( $issue->projectId )
			$message[]	= sprintf( $this->words['mail']['messageInvolved'], '"'.$issue->project->title.'"' );
		$message	= join( PHP_EOL, $message );

		$body		= '
'.View_Helper_Mail_Text::underscore( $this->words['mail']['headingNew'], '=' ).PHP_EOL.'
'.$message.PHP_EOL.'
'.View_Helper_Mail_Text::underscore( $this->words['mail']['headingIssue'] ).PHP_EOL.'
'.$this->factsMain->setFormat( View_Helper_Mail_Facts::FORMAT_TEXT )->render().PHP_EOL.PHP_EOL.'
'.View_Helper_Mail_Text::underscore( $this->words['mail']['headingChanges'] ).PHP_EOL.'
'.$this->factsChanges->setFormat( View_Helper_Mail_Facts::FORMAT_TEXT )->render().PHP_EOL.PHP_EOL.'
'.View_Helper_Mail_Text::underscore( $this->words['mail']['headingInfo'] ).PHP_EOL.'
'.$this->factsAll->setFormat( View_Helper_Mail_Facts::FORMAT_TEXT )->render().PHP_EOL;

		$list	= [];
		foreach( explode( PHP_EOL, $body ) as $nr => $line )
			$list[]	= View_Helper_Mail_Text::indent( $line, 0, 76 );
		return join( PHP_EOL, $list );
	}
}
