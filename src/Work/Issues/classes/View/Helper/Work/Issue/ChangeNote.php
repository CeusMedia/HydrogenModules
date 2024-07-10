<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Work_Issue_ChangeNote
{
	public const FORMAT_HTML		= 1;
	public const FORMAT_TEXT		= 2;

	public const FORMATS			= [
		self::FORMAT_HTML,
		self::FORMAT_TEXT,
	];

	protected Environment $env;
	protected ?object $note		= NULL;
	protected int $format		= self::FORMAT_HTML;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	public function render(): string
	{
		if( $this->format === self::FORMAT_TEXT )
			return $this->renderAsText();
		return $this->renderAsHtml();
	}

	public function setFormat( int $format ): self
	{
		$this->format	= $format;
		return $this;
	}

	public function setNote( object $note ): self
	{
		$this->note	= $note;
		return $this;
	}

	//  --  PROTECTED  --  //

	protected function renderAsHtml(): string
	{
		if( !$this->note )
			return '';

		$words		= $this->env->getLanguage()->getWords( 'work/issue' );

		$noteText	= '<em><small class="muted">Kein Kommentar.</small></em>';
		if( trim( $this->note->note ?? '' ) ){
			if( $this->env->getModules()->has( 'UI_Markdown' ) )
				$noteText	= View_Helper_Markdown::transformStatic( $this->env, $this->note->note );
			else if( $this->env->getModules()->has( 'UI_Helper_Content' ) )
				$noteText	= View_Helper_ContentConverter::render( $this->env, $this->note->note );
			else
				$noteText	= nl2br( $this->note->note );
		}
		$note	= HtmlTag::create( 'tt', $noteText, ['class' => 'issue-change-list-note-content'] );
		return $note;
	}

	protected function renderAsText(): string
	{
		if( !$this->note )
			return '';

		$words		= $this->env->getLanguage()->getWords( 'work/issue' );

		$noteText	= 'Kein Kommentar.';
		if( trim( $this->note->note ) )
			$noteText	= $this->note->note;
		return $noteText.PHP_EOL;
	}
}
