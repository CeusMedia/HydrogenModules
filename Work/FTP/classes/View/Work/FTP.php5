<?php
class View_Work_FTP extends CMF_Hydrogen_View{

	public function index(){

		$pathCurrent	= $this->getData( 'pathCurrent' );
		$pathDeepest	= $this->getData( 'pathDeepest' );
		$entries		= $this->getData( 'entries' );

		$this->addData( 'table', $this->renderFileTable( $pathCurrent, $entries ) );
		$this->addData( 'position', $this->renderPosition( $pathCurrent, $pathDeepest ) );
	}

	protected function renderPosition( $pathCurrent, $pathDeepest, $labelHome = "Home" ){
		$way	= "";
		$levels	= array( ''	=> $labelHome );
		foreach( explode( "/", $pathDeepest ) as $part ){
			$way .= $way ? '/'.$part : $part;
			$levels[$way]	= $part;
		}
		foreach( $levels as $path => $label ){
			$divider	= "";
			$attrItem	= array( 'class' => 'active' );
			if( $path !== $pathDeepest )
				$divider	= UI_HTML_Tag::create( 'span', '/', array( 'class' => 'divider' ) );
			if( $pathCurrent !== $path ){
				$url	= './FTP'.( $path ? "?path=".$path : "" );
				$label	= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url ) );
				$attrItem['class']	= NULL;
			}
			$list[]	= UI_HTML_Tag::create( 'li', $label.' '.$divider, $attrItem );
		}
		return UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'breadcrumb' ) );
	}

	protected function renderFileTable( $path, $entries ){
		$rows		= array();
		$folders	= array();
		$files		= array();
		foreach( $entries as $entry ){
#			print_m( $entry );
#			die;
			$entry	= (object) $entry;
			$icon	= '<i class="icon-'.( $entry->isdir ? 'folder-close' : 'file' ).'"></i> ';
			$label		= $icon.$entry->name;

			if( $entry->isdir ){
				$pathNew	= $path ? $path.'/'.$entry->name : $entry->name;
				$link		= UI_HTML_Tag::create( 'a', $label, array( 'href' => './FTP?path='.$pathNew ) );
				$size		= $entry->folders.' <i class="icon-folder-close"></i> / '.$entry->files.' <i class="icon-file"></i>';
			}
			else{
				$size		= Alg_UnitFormater::formatBytes( $entry->size, 1 );
				$link	= $label;
			}

			$cells	= array(
				UI_HTML_Tag::create( 'td', $link ),
				UI_HTML_Tag::create( 'td', $size ),
				UI_HTML_Tag::create( 'td', $entry->day.' '.$entry->month.' '.$entry->year ),
				UI_HTML_Tag::create( 'td', $entry->permissions ),
			);
			$entry->html	= UI_HTML_Tag::create( 'tr', $cells );
			if( $entry->isdir ){
				$folders[$entry->name]	= $entry->html;
			}
			else{
				$files[$entry->name]	= $entry->html;
			}
		}
		ksort( $folders );
		ksort( $files );
		$list	= $folders + $files;
		$colgroup	= UI_HTML_Elements::ColumnGroup( array( "50%", "15%", "15%", "15%" ) );
		$heads		= UI_HTML_Elements::TableHeads( array( 'Name', 'Size', 'Date', 'Permissions' ) );
		$thead	= UI_HTML_Tag::create( 'thead', $heads );
		$tbody	= UI_HTML_Tag::create( 'tbody', UI_HTML_Tag::create( 'tr', $list ) );
		return UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-condensed table-striped' ) );
	}
}
?>
