<?php

use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View\Helper\Abstraction;

class View_Helper_Info_Dashboard_Modal_Add extends Abstraction
{
	protected array $dashboards	= [];
	protected array $panels		= [];

	public function __construct( Environment $env )
	{
		$this->setEnv( $env );
	}

	public function render(): string
	{
		$w			= (object) $this->getWords( 'add', 'info/dashboard' );

		$list		= HtmlTag::create( 'div', $w->emptyPanels, ['class' => 'alert alert-warning'] );

		$moduleConfig	= $this->env->getConfig()->getAll( 'module.info_dashboard.', TRUE );
		$defaultPanels	= explode( ',', $moduleConfig->get( 'panels' ) );

		if( $this->panels ){
			$list	= [];
			foreach( $this->panels as $panelId => $panel ){
				$input	= HtmlTag::create( 'input', NULL, [
					'type'		=> 'checkbox',
					'name'		=> 'panels[]',
					'value'		=> $panelId,
					'checked'	=> in_array( $panelId, $defaultPanels ) ? "checked" : NULL,
				] );
				$key	= str_pad( $panel->rank, 3, 0, STR_PAD_LEFT ).'.'.uniqid();
				$list[$key]	= HtmlTag::create( 'label', $input.'&nbsp;'.$panel->title, [
					'class'	=> 'checkbox',
				] );
			}
			ksort( $list );
			$list	= HtmlTag::create( 'div', $list, ['style' => "padding: 0 0.5em 1em 0.5em;"] );
		}

		$fieldSelect	= '';
		if( count( $this->dashboards ) > 0 )
			$fieldSelect	= '
			<div class="row-fluid">
				<div class="span12">
					<label class="checkbox">
						<input type="checkbox" name="select" id="input_select" value="1" checked="checked"/>
						'.$w->labelSelect.'
					</label>
				</div>
			</div>';


		return '
<form action="./info/dashboard/add" method="post">
	<div id="myModalInfoDashboardAdd" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="myModalLabel"><i class="fa fa-fw fa-th"></i>&nbsp;'.$w->heading.'</h3>
		</div>
		<div class="modal-body">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title" class="mandatory required">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" required="required" placeholder="'.$w->placeholderTitle.'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_description">'.$w->labelDescription.'</label>
<!--					<textarea name="description" id="input_description" rows="3" class="span12 not-TinyMCE" data-tinymce-mode="minimal"></textarea>-->
					<input type="text" name="description" id="input_description" class="span12"/>
				</div>
			</div>
			'.$fieldSelect.'
			<div class="row-fluid">
				<div class="span12">
					'.HtmlTag::create( 'h5', $w->labelPanels ).'
					'.$list.'
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn" data-dismiss="modal" aria-hidden="true"><i class="icon-arrow-left"></i>&nbsp;'.$w->buttonCancel.'</button>
			<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i>&nbsp;'.$w->buttonSave.'</button>
		</div>
	</div>
</form>';
	}

	/**
	 *	Set list of user dashboards.
	 *	@access		public
	 *	@param		array		$dashboards			List of user dashboards
	 *	@return		self
	 */
	public function setDashboards( array $dashboards ): self
	{
		$this->dashboards	= $dashboards;
		return $this;
	}

	/**
	 *	Set map of registered panels.
	 *	@access		public
	 *	@param		array		$panels				Map of registered panels
	 *	@return		self
	 */
	public function setPanels( array $panels ): self
	{
		$this->panels		= $panels;
		return $this;
	}
}
