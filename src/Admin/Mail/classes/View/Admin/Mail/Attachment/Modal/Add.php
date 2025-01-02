<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Admin_Mail_Attachment_Modal_Add extends View_Helper_Bootstrap_Modal
{
	public View_Helper_Bootstrap_Modal_Trigger $trigger;

	protected ?string $title			= NULL;
	protected array $classes			= [];
	protected array $files				= [];
	protected array $moduleWords;

	public function __construct( Environment $env )
	{
		parent::__construct( $env );
		$this->moduleWords	= $this->env->getLanguage()->getWords( 'admin/mail/attachment' );

		$iconCancel		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-arrow-left'] );
		$iconSave		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-check'] );

		$w	= (object) $this->moduleWords['add'];
		$this->setHeading( $w->heading );
		$this->setFormAction( 'admin/mail/attachment/add' );
		$this->setButtonLabelSubmit( $iconSave.'&nbsp;'.$w->buttonSave );
		$this->setButtonLabelCancel( $iconCancel.'&nbsp;'.$w->buttonCancel );
		$this->trigger	= new View_Helper_Bootstrap_Modal_Trigger( $env );
		$this->trigger->setModalId( $this->id );
		$this->trigger->setLabel( $this->moduleWords['index']['buttonRegister'] );
	}

	protected function renderBody(): string
	{
		$w			= (object) $this->moduleWords['add'];

		ksort( $this->files );
		$optFile	= ['' => ''];
		foreach( $this->files as $file )
			$optFile[$file->filePath]	= $file->filePath;
		$optFile	= HtmlElements::Options( $optFile );

		$optClass	= ['' => ''];
		foreach( $this->classes as $class )
			$optClass[$class]	= $class;
		$optClass	= HtmlElements::Options( $optClass );
		$optStatus	= HtmlElements::Options( $this->moduleWords['states'], 1 );

		return '
			<div class="row-fluid">
				<div class="span12">
					<label for="input_file" class="mandatory required">'.$w->labelFile.'</label>
					<select id="input_file" name="file" class="span12" required="required">'.$optFile.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_class" class="mandatory required">'.$w->labelClass.'</label>
					<select id="input_class" name="class" class="span12" required="required">'.$optClass.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_language" class="mandatory required">'.$w->labelLanguage.'</label>
					<input type="text" id="input_language" name="language" class="span12" required="required"/>
				</div>
				<div class="span4">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select id="input_status" name="status" class="span12">'.$optStatus.'</select>
				</div>
			</div>';
	}

	public function render(): string
	{
		$this->body	= $this->renderBody();
		return parent::render();
	}

	//  --  SETTERS  --  //

	public function setClasses( array $classes ): static
	{
		$this->classes		= $classes;
		return $this;
	}

	public function setFiles( array $files ): static
	{
		$this->files		= $files;
		return $this;
	}

	public function setId( int|string $id ): static
	{
		$this->trigger->setModalId( $id );
		return parent::setId( $id );
	}
}