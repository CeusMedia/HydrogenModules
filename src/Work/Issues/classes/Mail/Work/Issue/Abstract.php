<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

abstract class Mail_Work_Issue_Abstract extends Mail_Abstract
{
	protected array $words;
	protected ?View_Helper_Mail_Facts $factsAll		= NULL;
	protected ?View_Helper_Mail_Facts $factsMain	= NULL;
	protected Logic_Project $logicProject;
	protected Model_User $modelUser;
	protected Model_Issue $modelIssue;
	protected Model_Issue_Note $modelIssueNote;
	protected Model_Issue_Change $modelIssueChange;

	/**
	 *	This method is called after construction is done and right before generation takes place.
	 *	@access		protected
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		parent::__onInit();
//		$this->addThemeStyle( 'layout.css' );
//		$this->addThemeStyle( 'layout.panels.css' );
		$this->addThemeStyle( 'site.user.css' );
		$this->addThemeStyle( 'site.work.issue.css' );
		$this->addBodyClass( 'moduleWorkIssues' );
		$this->words		= (array) $this->getWords( 'work/issue' );
		$this->logicProject		= Logic_Project::getInstance( $this->env );
		$this->modelUser		= new Model_User( $this->env );										//  get model of users
		$this->modelIssue		= new Model_Issue( $this->env );									//  get model of issues
		$this->modelIssueNote	= new Model_Issue_Note( $this->env );								//  get model of issue notes
		$this->modelIssueChange	= new Model_Issue_Change( $this->env );								//  get model of issue changes
	}

	protected function prepareFacts( array $data )
	{
		$issue		= $data['issue'];

		$this->factsMain	= new View_Helper_Mail_Facts();
		$this->factsMain->setLabels( $this->words['edit'] );
		$this->factsMain->setListClass( 'facts-vertical' );
		$this->factsMain->setTextLabelLength( 13 );
		$this->factsMain->add(
			'priority',
			'<span class="issue-priority priority-'.$issue->priority.'">'.$this->words['priorities'][$issue->priority].'</span>',
			$this->words['priorities'][$issue->priority]
		);
		$this->factsMain->add(
			'type',
			'<span class="issue-type type-'.$issue->type.'">'.$this->words['types'][$issue->type].'</span>',
			$this->words['types'][$issue->type]
		);
		$this->factsMain->add(
			'title',
			'<big><a href="./work/issue/edit/'.$issue->issueId.'">'.$issue->title.'</a></big>',
			$issue->title
		);
		$this->factsMain->add(
			'content',
			'<tt>'.nl2br( $issue->content ).'</tt><br/><br/>',
			$issue->content.PHP_EOL
		);

		$this->factsAll	= new View_Helper_Mail_Facts();
		$this->factsAll->setLabels( $this->words['edit'] );
		$this->factsAll->setListClass( 'not-facts-vertical dl-horizontal' );
		$this->factsAll->setTextLabelLength( 13 );
		if( $issue->projectId ){
			$projectLink	= HtmlElements::Link( './manage/project/view/'.$issue->projectId, $issue->project->title );
			$this->factsAll->add(
				'project',
				'<span class="project status'.$issue->project->status.'">'.$projectLink.'</span>',
				$issue->project->title
			);
		}
		if( $issue->type )
			$this->factsAll->add(
				'type',
				'<span class="issue-type type-'.$issue->type.'">'.$this->words['types'][$issue->type].'</span>',
				$this->words['types'][$issue->type]
			);
		if( $issue->severity )
			$this->factsAll->add(
				'severity',
				'<span class="issue-severity severity-'.$issue->severity.'">'.$this->words['severities'][$issue->severity].'</span>',
				$this->words['severities'][$issue->severity]
			);
		if( $issue->priority )
			$this->factsAll->add(
				'priority',
				'<span class="issue-priority priority-'.$issue->priority.'">'.$this->words['priorities'][$issue->priority].'</span>',
				$this->words['priorities'][$issue->priority]
			);
		$this->factsAll->add(
			'status',
			'<span class="issue-status status-'.$issue->status.'">'.$this->words['states'][$issue->status].'</span>',
			$this->words['states'][$issue->status]
		);
		if( $issue->reporterId ){
			$this->factsAll->add(
				'reporter',
				$this->renderUser( $issue->reporter, TRUE ),
				$this->renderUser( $issue->reporter, FALSE )
			);
		}
		if( $issue->managerId ){
			$this->factsAll->add(
				'manager',
				$this->renderUser( $issue->manager, TRUE ),
				$this->renderUser( $issue->manager, FALSE )
			);
		}
		$this->factsAll->add(
			'id',
			'<tt>#'.$issue->issueId.'</tt>',
			'#'.$issue->issueId
		);
	}

	protected function renderUser( $user, bool $asHtml = TRUE ): string
	{
		if( !is_object( $user ) )
			return '-';
		if( !$asHtml )
			return $user->username.' ('.$user->firstname.' '.$user->surname.')';
		if( class_exists( 'View_Helper_Member' ) ){
			$helper		= new View_Helper_Member( $this->env );
			$helper->setLinkUrl( './member/view/%s' );
			$helper->setUser( $user );
			return $helper->render();
		}
		$link	= HtmlElements::Link( './member/view/'.$user->userId, $user->username );
		return HtmlTag::create( 'span', $link, ['class' => 'role role'.$user->roleId] );
	}
}
