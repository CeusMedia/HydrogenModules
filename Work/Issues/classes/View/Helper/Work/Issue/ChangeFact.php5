<?php
class View_Helper_Work_Issue_ChangeFact{

	public function __construct( $env ){
		$this->env	= $env;
		$this->modelUser	= new Model_User( $this->env );
		$this->modelIssue	= new Model_Issue( $this->env );
		$this->modelNote	= new Model_Issue_Note( $this->env );
		$this->modelChange	= new Model_Issue_Change( $this->env );
	}

	public function setChange( $change ){
		$this->change	= $change;
	}

	public function render(){
		if( !$this->change )
			throw new RuntimeException( 'No change set.' );
		$words		= $this->env->getLanguage()->getWords( 'work/issue' );
		switch( $this->change->type ){
			case 1:
			case 2:
				$from	= UI_HTML_Tag::create( 'small', 'unbekannt', array( 'class' => 'muted' ) );
				$to		= UI_HTML_Tag::create( 'small', 'unbekannt', array( 'class' => 'muted' ) );
				if( $this->change->from && $this->modelUser->get( $this->change->from ) ){
					$from	= $this->modelUser->get( $this->change->from )->username;
					$from	= UI_HTML_Tag::create( 'a', $from, array( 'href' => './user/view/'.$this->change->from ) );
					$from	= UI_HTML_Tag::create( 'span', $from, array( 'class' => 'issue-user' ) );
				}
				if( $this->change->to && $this->modelUser->get( $this->change->to ) ){
					$to		= $this->modelUser->get( $this->change->to )->username;
					$to		= UI_HTML_Tag::create( 'a', $to, array( 'href' => './user/view/'.$this->change->from ) );
					$to		= UI_HTML_Tag::create( 'span', $to, array( 'class' => 'issue-user' ) );
				}
				$change	= $from." -> ".$to;
				break;
			case 3:
				$logic	= new Logic_Project( $this->env );
				$from	= UI_HTML_Tag::create( 'small', 'unbekannt', array( 'class' => 'muted' ) );
				$to		= UI_HTML_Tag::create( 'small', 'unbekannt', array( 'class' => 'muted' ) );
				if( $this->change->from )
					$from	= UI_HTML_Tag::create( 'span', $logic->get( $this->change->from )->title, array( 'class' => '' ) );
				if( $this->change->to )
					$to		= UI_HTML_Tag::create( 'span', $logic->get( $this->change->to )->title, array( 'class' => '' ) );
				$change	= $from." -> ".$to;
				break;
			case 4:
				$from	= UI_HTML_Tag::create( 'span', $words['types'][$this->change->from], array( 'class' => 'issue-type type-'.$this->change->from ) );
				$to		= UI_HTML_Tag::create( 'span', $words['types'][$this->change->to], array( 'class' => 'issue-type type-'.$this->change->to ) );
				$change	= $from." -> ".$to;
				break;
			case 5:
				$from	= UI_HTML_Tag::create( 'span', $words['severities'][$this->change->from], array( 'class' => 'issue-severity severity-'.$this->change->from ) );
				$to		= UI_HTML_Tag::create( 'span', $words['severities'][$this->change->to], array( 'class' => 'issue-severity severity-'.$this->change->to ) );
				$change	= $from." -> ".$to;
				break;
			case 6:
				$from	= UI_HTML_Tag::create( 'span', $words['priorities'][$this->change->from], array( 'class' => 'issue-priority priority-'.$this->change->from ) );
				$to		= UI_HTML_Tag::create( 'span', $words['priorities'][$this->change->to], array( 'class' => 'issue-priority priority-'.$this->change->to ) );
				$change	= $from." -> ".$to;
				break;
			case 7:
				$from	= UI_HTML_Tag::create( 'span', $words['states'][$this->change->from], array( 'class' => 'issue-status status-'.$this->change->from ) );
				$to		= UI_HTML_Tag::create( 'span', $words['states'][$this->change->to], array( 'class' => 'issue-status status-'.$this->change->to ) );
				$change	= $from." -> ".$to;
				break;
			case 8:
				$from	= UI_HTML_Tag::create( 'span', $this->change->from.'%', array( 'class' => 'issue-progress progress-'.( floor( $this->change->from / 25 ) * 25 ) ) );
				$to		= UI_HTML_Tag::create( 'span', $this->change->to.'%', array( 'class' => 'issue-progress progress-'.( floor( $this->change->to / 25 ) * 25 ) ) );
				$change	= $from." -> ".$to;
				break;
			default:
				$change	= 'unbekannt';
		}
		return UI_HTML_Tag::create( 'dd', $change );
	}

	public function renderAsText(){
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
				$logic	= new Logic_Project( $this->env );
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
				$this->change	= $from." -> ".$to;
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
?>
