<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Work_Issue_Dashboard
{
	protected Environment $env;

	protected int|string $currentUserId	= 0;
	protected array $statuses	= [
		Model_Issue::STATUS_NEW,
		Model_Issue::STATUS_ASSIGNED,
		Model_Issue::STATUS_ACCEPTED,
		Model_Issue::STATUS_PROGRESSING
	];
	protected array $orders		= [];
	protected array $limits		= [];
	protected array $issues		= [];
	protected array $words		= [];
	protected string $type		= '';

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	public function render(): string
	{
		/** @var Logic_Project $logicProject */
		$logicProject	= Logic_Project::getInstance( $this->env );

		$userProjects	= $logicProject->getUserProjects( $this->currentUserId, TRUE );
		if( !$userProjects ){
			return HtmlTag::create( 'div', 'Keine Projekte vorhanden.', ['class' => 'alert alert-info'] );
		}

		if( 0 !== count( $this->issues ) )
			$issues	= $this->issues;
		else{
			$modelIssue		= new Model_Issue( $this->env );
			$issues			= $modelIssue->getAll( [
				'status'	=> $this->statuses,
				'projectId'	=> array_keys( $userProjects ),
			], $this->orders, $this->limits );
		}

		$rows	= [];
		$icons			= [
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-exclamation', 'title' => 'Fehler'] ),
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-wrench', 'title' => 'Aufgabe'] ),
			HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-lightbulb-o', 'title' => 'Wunsch/Idee'] ),
		];
		foreach( $issues as $issue ) {
			$icon	= $icons[$issue->type];
			$link	= HtmlTag::create( 'a', $icon.'&nbsp;'.$issue->title, [
				'href'	=> './work/issue/edit/'.$issue->issueId
			] );
			$rows[]	= HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', $link, ['class' => 'autocut'] ),
			] );
		}
		$table	= HtmlTag::create( 'table', $rows, ['class' => 'table table-condensed table-fixed'] );
		return HtmlTag::create( 'div', $table );
	}


	public function setCurrentUser( int|string $userId ): self
	{
		$this->currentUserId	= $userId;
		return $this;
	}

	public function setIssues( array $issues ): self
	{
		$this->issues		= $issues;
		return $this;
	}
	public function setOrders( array $orders ): self
	{
		$this->orders		= $orders;
		return $this;
	}

	public function setStatuses( array $statuses ): self
	{
		$this->statuses		= $statuses;
		return $this;
	}

	public function setType( string $type ): self
	{
		$this->type		= trim( $type );
		return $this;
	}

	public function setWords( array $words ): self
	{
		$this->words		= $words;
		return $this;
	}
}