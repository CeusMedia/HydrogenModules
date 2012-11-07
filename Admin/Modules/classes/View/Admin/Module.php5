<?php
class View_Admin_Module extends CMF_Hydrogen_View{

	public function index(){
	}

	public function install(){
	}

	protected function renderModuleButton( $module, $url, $class = 'module button' ){
		$image		= '';
		if( !empty( $module->icon ) )
			$image	= UI_HTML_Elements::Image( $module->icon, $module->title );
		$icon	= UI_HTML_Tag::create( 'div', $image, array( 'class' => 'module-icon' ) );
		$title	= UI_HTML_Tag::create( 'div', $module->title, array( 'class' => 'module-title' ) );
		$desc	= explode( '<br />', nl2br( $module->description ) );
		$desc	= array_shift( $desc );
		$desc	= UI_HTML_Tag::create( 'div', $desc, array( 'class' => 'module-desc' ) );
		$click	= 'document.location.href=\''.$url.$module->id.'\';';
		return UI_HTML_Tag::create( 'div', $icon.$title.$desc, array( 'class' => $class, 'onclick' => $click ) );
	}

	/**
	 *	Renders module overview containing large module buttons, sectioned by categories.
	 *	The URL of each module button relates the view action of the current controller.
	 *	@access		protected
	 *	@param		array		$modules		Map of all modules
	 *	@param		array		$categories		Map of all category labels
	 *	@param		array		$filters		Map of filters to apply on overview, example: array( 'type' => array( Model_Module::TYPE_CUSTOM ) )
	 *	@return		string		Rendered HTML of section modules.
	 */
	protected function renderModuleSections( $modules, $categories, $filters = array() ){
		$listSections	= array();
		foreach( $categories as $categoryId => $category ){
			$listModules	= array();
			$url			= './'.$this->controller.'/view/';
			foreach( $modules as $module ){
				if( $module->category != $categoryId )
						continue;
				if( array_key_exists( 'source', $filters ) )
					if( !in_array( $module->source, $filters['source'] ) )
						continue;
				if( array_key_exists( 'type', $filters ) )
					if( !in_array( $module->type, $filters['type'] ) )
						continue;
				$image		= '';
				if( !empty( $module->icon ) )
					$image	= UI_HTML_Elements::Image( $module->icon, $module->title );
				$listModules[]	= $this->renderModuleButton( $module, $url );
			}
			$listModules	= join( $listModules );
			if( $listModules )
				$listSections[]	= '<fieldset><legend>'.$category.'</legend>'.$listModules.'</fieldset>';
		}
		return join( $listSections );
	}

	protected function renderRelatedModulesList( $allModules, $relatedModules, $url = NULL, $listClass = NULL ){
		$words	= $this->env->getLanguage()->getWords( 'admin/module' );
		$list	= array();
		foreach( $relatedModules as $relatedModuleId => $status ){
			$alt	= $words['status-alt'][$status];
			$label	= $relatedModuleId;
			if( isset( $allModules[$relatedModuleId] ) ){
				$relatedModule	= $allModules[$relatedModuleId];
				$desc	= explode( '<br />', nl2br( $relatedModule->description ) );
				$attr	= array( 'title' => array_shift( $desc ) );
				$label	= UI_HTML_Tag::create( 'acronym', $relatedModule->title, $attr );
				if( $url )
					$label	= UI_HTML_Elements::Link( $url.$relatedModuleId, $relatedModule->title, $attr );
			}
			$class	= 'icon module module-status-'.$status;
			$label	= UI_HTML_Tag::create( 'span', $label, array( 'class' => $class, 'title' => $alt ) );
			$list[]	= UI_HTML_Elements::ListItem( $label, 1 );
		}
		return UI_HTML_Elements::unorderedList( $list, 1, array( 'class' => $listClass ) );
	}

	public function showRelationGraph(){
		$graph	= $this->getData( 'graph' );
		$tempFile	= tempnam( sys_get_temp_dir(), 'CMF' );
		File_Writer::save( $tempFile, $graph );
		$a	= array();
		$b	= 0;
		$a	= array();
		exec( 'dot -O -Tpng '.$tempFile, $a, $b );
		unlink( $tempFile );
		if( 0 && $b ){
			remark( 'a');
			print_m( $a );
			remark( 'b');
			print_m( $b );
			remark( 'c');
			xmp( File_Reader::load( 'e.log' ) );
#			$image	= new UI_Image_Error( join( $a ) );
#			UI_Image_Printer::saveImage( $tempFile, $image );
		}
		$tempFile	.= '.png';
		$image	= File_Reader::load( $tempFile );
		unlink( $tempFile );
		header( 'Content-type: image/png' );
		print( $image );
		exit;
	}

	public function view(){
	}
}
?>
