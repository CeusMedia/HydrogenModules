<?php
class View_Helper_Info_Dashboard_Modal_Add extends CMF_Hydrogen_View_Helper_Abstract{

	protected $dashboards	= array();
	protected $panels		= array();

	public function __construct( $env ){
		$this->setEnv( $env );
	}

	public function render(){
		$w			= (object) $this->getWords( 'add', 'info/dashboard' );

		$list		= UI_HTML_Tag::create( 'div', $w->emptyPanels, array( 'class' => 'alert alert-warning' ) );
		if( $this->panels ){
			$list	= array();
			foreach( $this->panels as $panelId => $panel ){
				$input	= UI_HTML_Tag::create( 'input', NULL, array(
					'type'	=> 'checkbox',
					'name'	=> 'panels[]',
					'value'	=> $panelId,
				) );
				$list[]	= UI_HTML_Tag::create( 'label', $input.'&nbsp;'.$panel->title, array(
					'class'	=> 'checkbox',
				) );
			}
			$list	= UI_HTML_Tag::create( 'div', $list, array( 'style' => "padding: 0 0.5em 1em 0.5em;" ) );
		}

		$fieldSelect	= '';
		if( count( !$this->dashboards ) > 0 )
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
					'.UI_HTML_Tag::create( 'h5', $w->labelPanels ).'
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
	 *	@return		void
	 */
	public function setDashboards( $dashboards ){
		$this->dashboards	= $dashboards;
	}

	/**
	 *	Set map of registered panels.
	 *	@access		public
	 *	@param		array		$panels				Map of registered panels
	 *	@return		void
	 */
	public function setPanels( $panels ){
		$this->panels		= $panels;
	}
}
