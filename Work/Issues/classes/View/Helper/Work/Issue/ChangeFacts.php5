<?php
class View_Helper_Work_Issue_ChangeFacts{

	public function __construct( $env ){
		$this->env	= $env;
		$this->modelUser	= new Model_User( $this->env );
		$this->modelChange	= new Model_Issue_Change( $this->env );
	}

	public function setNote( $note ){
		$this->note	= $note;
	}

	public function render(){
		if( !$this->note )
			throw new RuntimeException( 'No note set.' );
		$words			= $this->env->getLanguage()->getWords( 'work/issue' );
		$this->note->user		= $this->modelUser->get( $this->note->userId );
		$this->note->changes	= $this->modelChange->getAllByIndex( 'noteId', $this->note->issueNoteId );
		$manager	= '-';
		if( $this->note->user ){
			$manager	= '<a href="./manage/user/edit/'.$this->note->user->userId.'">'.$this->note->user->username.'</a>';
			$manager	= '<span class="role role'.$this->note->user->roleId.'">'.$manager.'</span>';
		}
		$noteChanges	= array(
			UI_HTML_Tag::create( 'dt', 'Bearbeiter' ),
			UI_HTML_Tag::create( 'dd', $manager ),
			UI_HTML_Tag::create( 'dt', 'Zeitpunkt' ),
			UI_HTML_Tag::create( 'dd', '<span class="issue-note-date">'.date( 'd.m.Y H:i:s', $this->note->timestamp ).'</span>' ),
		);
		$helper			= new View_Helper_Work_Issue_ChangeFact( $this->env );
		foreach( $this->note->changes as $change ){
			$helper->setChange( $change );
			$noteChanges[]	= $helper->render();
		}
		return UI_HTML_Tag::create( 'dl', $noteChanges );
	}
}
?>
