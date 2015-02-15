<?php
/**
 *	@todo		implement view for files from available modules
 */
class View_Helper_Module_CodeViewer extends CMF_Hydrogen_View_Helper_Abstract {

	/**	@var	Logic_Module	$logic		Module logic instance */
	protected $logic;

	/**
	 *	Constructor.
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment_Abstract	$env		Environment object
	 *	@param		Logic_Module						$logic		Module logic instance
	 *	@return		void
	 */
	public function __construct( CMF_Hydrogen_Environment_Abstract $env, Logic_Module $logic ){
		$this->setEnv( $env );
		$this->logic	= $logic;
	}

	public function render( $moduleId, $fileType, $fileName, $sourceType = NULL ){
		$module	= $this->logic->getModule( $moduleId );
		if( !$module )
			throw new RuntimeException( 'Invalid module ID "'.$moduleId.'"' );
		$sourceType	= $sourceType !== NULL ? $sourceType : $module->type;
		$modes	= array();
		if( $sourceType > 0 && $sourceType < 4 ){													//  module is custom, copied or linked
			$pathApp	= $this->env->pathApp;															//  get path to remote application
			$config		= $this->env->getRemote()->getConfig();											//  get config object of remote application
			$pathFile	= '';
			$xmpClass	= '';
			switch( $fileType ){
				case 'class':
					$pathFile	= $pathApp.'classes/';
					$xmpClass	= 'php';
					$modes		= array( 'css', 'xml', 'javascript', 'clike', 'php' );
					break;
				case 'locale':
					$pathFile	= $pathApp.$config->get( 'path.locales');
					$xmpClass	= 'ini';
					$modes		= array( 'properties' );
					break;
				case 'script':
					$pathFile	= '';
					$xmpClass	= 'js';
					$modes		= array( 'javascript' );
					break;
				case 'style':
					$pathFile	= '';
					$xmpClass	= 'css';
					$modes		= array( 'css' );
					break;
				case 'template':
					$pathFile	= $pathApp.$config->get( 'path.templates');
					$xmpClass	= 'php';
					$modes		= array( 'css', 'xml', 'javascript', 'clike', 'php' );
					break;
				case 'file':
					$pathFile	= '';
					$xmpClass	= 'code';
					$modes		= array( 'css', 'xml', 'javascript', 'clike', 'php' );
					break;
			}
			if( !file_exists( $pathFile.$fileName ) )
				throw new RuntimeException( 'Invalid file: '.$pathFile.$fileName );
		}
		$config			= $this->env->getConfig();
		$pathJsLib		= $config->get( 'path.scripts.lib' );

		$content	= File_Reader::load( $pathFile.$fileName );
#		$code		= UI_HTML_Tag::create( 'xmp', $content, array( 'class' => 'code '.$xmpClass ) );
		$body		= '
<style>
div.CodeMirror,
div.CodeMirror-scroll {
	width: 100%;
	height: 100%;
	}
</style>
<!--<h2>'.$moduleId.' - '.$fileName.'</h2>
<code>'.$pathFile.$fileName.'</code>-->
<textarea id="code">'.htmlentities( $content, ENT_COMPAT, 'UTF-8' ).'</textarea>
<script>
var myCodeMirror = CodeMirror.fromTextArea(document.getElementById("code"));
</script>
';
		$page		= new UI_HTML_PageFrame();
		$page->addStylesheet( 'css/reset.css' );
		$page->addStylesheet( 'css/typography.css' );
#		$page->addStylesheet( 'css/xmp.formats.css' );
		$page->addStylesheet( $pathJsLib.'CodeMirror/2.25/lib/codemirror.css' );
		$page->addJavaScript( $pathJsLib.'CodeMirror/2.25/lib/codemirror.js' );
		foreach( $modes as $mode )
			$page->addJavaScript( $pathJsLib.'CodeMirror/2.25/mode/'.$mode.'/'.$mode.'.js' );
		$page->addBody( $body );
		return $page->build( array( 'style' => 'margin: 1em' ) );
	}
}
?>
