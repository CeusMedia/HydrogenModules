<?php
class View_Helper_Work_Issue_ChangeFacts{

	protected $note;

	public function __construct( $env ){
		$this->env	= $env;
		$this->modelUser	= new Model_User( $this->env );
		$this->modelChange	= new Model_Issue_Change( $this->env );
	}

	protected function prepareFacts(){
		$words			= $this->env->getLanguage()->getWords( 'work/issue' );
		$changerHtml	= '-';
		$changerText	= '-';
		if( $this->note->userId ){
			$this->note->user	= $this->modelUser->get( $this->note->userId );
			$changerText	= $this->note->user->username;
			if( class_exists( 'View_Helper_Member' ) ){
				$helper		= new View_Helper_Member( $this->env );
				$helper->setLinkUrl( 'member/view/%s' );
				$helper->setUser( $this->note->userId );
				$changerHtml	= $helper->render();
			}
			else{
				$changerHtml	= '<a href="./manage/user/edit/'.$this->note->user->userId.'">'.$this->note->user->username.'</a>';
				$changerHtml	= '<span class="role role'.$this->note->user->roleId.'">'.$changerHtml.'</span>';
			}
		}

		$this->note->user		= $this->modelUser->get( $this->note->userId );
		$changes	= $this->modelChange->getAllByIndex( 'noteId', $this->note->issueNoteId );

		$changedAt		= '<span class="issue-note-date">'.date( 'd.m.Y H:i:s', $this->note->timestamp ).'</span>';

		$this->factsChanges		= new View_Helper_Mail_Facts( $this->env );
		$this->factsChanges->setLabels( $words['changes'] );
		$this->factsChanges->setListClass( 'facts-vertical' );
		$this->factsChanges->setTextLabelLength( 13 );
		$this->factsChanges->add( 'Bearbeiter', $changerHtml, $changerText );
		$this->factsChanges->add( 'Zeitpunkt', $changedAt, date( 'd.m.Y H:i:s', $this->note->timestamp ) );

		$helper			= new View_Helper_Work_Issue_ChangeFact( $this->env );
		foreach( $changes as $change ){
			$helper->setChange( $change );
			$this->factsChanges->add( $change->type, $helper->render(), $helper->renderAsText() );
		}
		$helper		= new View_Helper_Work_Issue_ChangeNote( $this->env );
		$helper->setNote( $this->note );
		$this->factsChanges->add( 10, $helper->render(), $helper->renderAsText() );

	}

	public function setNote( $note ){
		$this->note	= $note;
		$this->prepareFacts();
	}

	public function render(){
		if( !$this->note )
			return '';
		return $this->factsChanges->render();
	}


	public function renderAsText(){
		if( !$this->note )
			return '';
		return $this->factsChanges->renderAsText();
	}
}
?>
