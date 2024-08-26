<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Work_Issue_ChangeFact
{
	public const FORMAT_HTML		= 1;
	public const FORMAT_TEXT		= 2;

	public const FORMATS			= [
		self::FORMAT_HTML,
		self::FORMAT_TEXT,
	];

	protected Environment $env;
	protected Model_User $modelUser;
	protected Model_Issue $modelIssue;
	protected Model_Issue_Note $modelNote;
	protected Model_Issue_Change $modelChange;
	protected int $format		= self::FORMAT_HTML;
	protected ?object $change	= NULL;

	/**
	 *	@param		Environment		$env
	 */
	public function __construct( Environment $env )
	{
		$this->env	= $env;
		$this->modelUser	= new Model_User( $this->env );
		$this->modelIssue	= new Model_Issue( $this->env );
		$this->modelNote	= new Model_Issue_Note( $this->env );
		$this->modelChange	= new Model_Issue_Change( $this->env );
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function render(): string
	{
		if( $this->format === self::FORMAT_TEXT )
			return $this->renderAsText();
		return $this->renderAsHtml();
	}

	/**
	 *	@param		object		$change
	 *	@return		self
	 */
	public function setChange( object $change ): self
	{
		$this->change	= $change;
		return $this;
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

	//  --  PROTECTED  --  //

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function renderAsHtml(): string
	{
		if( !$this->change )
			throw new RuntimeException( 'No change set.' );
		$words		= $this->env->getLanguage()->getWords( 'work/issue' );
		switch( $this->change->type ){
			case 1:
			case 2:
				$from	= HtmlTag::create( 'small', 'unbekannt', ['class' => 'muted'] );
				$to		= HtmlTag::create( 'small', 'unbekannt', ['class' => 'muted'] );
				if( $this->change->from && $this->modelUser->get( $this->change->from ) ){
					$from	= $this->modelUser->get( $this->change->from )->username;
					$from	= HtmlTag::create( 'a', $from, ['href' => './user/view/'.$this->change->from] );
					$from	= HtmlTag::create( 'span', $from, ['class' => 'issue-user'] );
				}
				if( $this->change->to && $this->modelUser->get( $this->change->to ) ){
					$to		= $this->modelUser->get( $this->change->to )->username;
					$to		= HtmlTag::create( 'a', $to, ['href' => './user/view/'.$this->change->from] );
					$to		= HtmlTag::create( 'span', $to, ['class' => 'issue-user'] );
				}
				$change	= $from." -> ".$to;
				break;
			case 3:
				/** @var Logic_Project $logic */
				$logic	= Logic_Project::getInstance( $this->env );
				$from	= HtmlTag::create( 'small', 'unbekannt', ['class' => 'muted'] );
				$to		= HtmlTag::create( 'small', 'unbekannt', ['class' => 'muted'] );
				if( $this->change->from )
					$from	= HtmlTag::create( 'span', $logic->get( $this->change->from )->title, ['class' => ''] );
				if( $this->change->to )
					$to		= HtmlTag::create( 'span', $logic->get( $this->change->to )->title, ['class' => ''] );
				$change	= $from." -> ".$to;
				break;
			case 4:
				$from	= HtmlTag::create( 'span', $words['types'][$this->change->from], ['class' => 'issue-type type-'.$this->change->from] );
				$to		= HtmlTag::create( 'span', $words['types'][$this->change->to], ['class' => 'issue-type type-'.$this->change->to] );
				$change	= $from." -> ".$to;
				break;
			case 5:
				$from	= HtmlTag::create( 'span', $words['severities'][$this->change->from], ['class' => 'issue-severity severity-'.$this->change->from] );
				$to		= HtmlTag::create( 'span', $words['severities'][$this->change->to], ['class' => 'issue-severity severity-'.$this->change->to] );
				$change	= $from." -> ".$to;
				break;
			case 6:
				$from	= HtmlTag::create( 'span', $words['priorities'][$this->change->from], ['class' => 'issue-priority priority-'.$this->change->from] );
				$to		= HtmlTag::create( 'span', $words['priorities'][$this->change->to], ['class' => 'issue-priority priority-'.$this->change->to] );
				$change	= $from." -> ".$to;
				break;
			case 7:
				$from	= HtmlTag::create( 'span', $words['states'][$this->change->from], ['class' => 'issue-status status-'.$this->change->from] );
				$to		= HtmlTag::create( 'span', $words['states'][$this->change->to], ['class' => 'issue-status status-'.$this->change->to] );
				$change	= $from." -> ".$to;
				break;
			case 8:
				$from	= HtmlTag::create( 'span', $this->change->from.'%', ['class' => 'issue-progress progress-'.( floor( $this->change->from / 25 ) * 25 )] );
				$to		= HtmlTag::create( 'span', $this->change->to.'%', ['class' => 'issue-progress progress-'.( floor( $this->change->to / 25 ) * 25 )] );
				$change	= $from." -> ".$to;
				break;
			default:
				$change	= 'unbekannt';
		}
		return HtmlTag::create( 'dd', $change );
	}

	/**
	 *	@return		string
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	protected function renderAsText(): string
	{
		if( !$this->change )
			throw new RuntimeException( 'No change set.' );
		$words		= $this->env->getLanguage()->getWords( 'work/issue' );
		$change		= 'unbekannt';
		switch( $this->change->type ){
			case 1:
			case 2:
				$from	= 'unbekannt';
				$to		= 'unbekannt';
				if( $this->change->from && $this->modelUser->get( $this->change->from ) ){
					$from	= $this->modelUser->get( $this->change->from )->username;
				}
				if( $this->change->to && $this->modelUser->get( $this->change->to ) ){
					$to		= $this->modelUser->get( $this->change->to )->username;
				}
				$change	= $from." -> ".$to;
				break;
			case 3:
				/** @var Logic_Project $logic */
				$logic	= Logic_Project::getInstance( $this->env );
				$from	= 'unbekannt';
				$to		= 'unbekannt';
				if( $this->change->from )
					$from	= $logic->get( $this->change->from )->title;
				if( $this->change->to )
					$to		= $logic->get( $this->change->to )->title;
				$change	= $from." -> ".$to;
				break;
			case 4:
				$from	= $words['types'][$this->change->from];
				$to		= $words['types'][$this->change->to];
				$change	= $from." -> ".$to;
				break;
			case 5:
				$from	= $words['severities'][$this->change->from];
				$to		= $words['severities'][$this->change->to];
				$change	= $from." -> ".$to;
				break;
			case 6:
				$from	= $words['priorities'][$this->change->from];
				$to		= $words['priorities'][$this->change->to];
				$change	= $from." -> ".$to;
				break;
			case 7:
				$from	= $words['states'][$this->change->from];
				$to		= $words['states'][$this->change->to];
				$change	= $from." -> ".$to;
				break;
			case 8:
				$from	= $this->change->from.'%';
				$to		= $this->change->to.'%';
				$change	= $from." -> ".$to;
				break;
		}
		return $change;
	}
}
