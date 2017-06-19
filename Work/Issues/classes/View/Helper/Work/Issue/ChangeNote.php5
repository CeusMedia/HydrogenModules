<?php
class View_Helper_Work_Issue_ChangeNote{

	protected $env;
	protected $note;

	public function __construct( $env ){
		$this->env	= $env;
	}

	public function setNote( $note ){
		$this->note	= $note;
	}

	public function render(){
		if( !$this->note )
			return '';

		$words		= $this->env->getLanguage()->getWords( 'work/issue' );

		$noteText	= '<em><small class="muted">Kein Kommentar.</small></em>';
		if( trim( $this->note->note ) ){
			if( $this->env->getModules()->has( 'UI_Markdown' ) )
				$noteText	= View_Helper_Markdown::transformStatic( $this->env, $this->note->note );
			else if( $this->env->getModules()->has( 'UI_Helper_Content' ) )
				$noteText	= View_Helper_ContentConverter::render( $this->env, $this->note->note );
			else
				$noteText	= nl2br( $this->note->note );
		}
		$note	= UI_HTML_Tag::create( 'tt', $noteText, array( 'class' => 'issue-change-list-note-content' ) );
		return $note;
	}

	public function renderAsText(){
		if( !$this->note )
			return '';

		$words		= $this->env->getLanguage()->getWords( 'work/issue' );

		$noteText	= 'Kein Kommentar.';
		if( trim( $this->note->note ) )
			$noteText	= $this->note->note;
		return $noteText.PHP_EOL;
	}
}
?>
