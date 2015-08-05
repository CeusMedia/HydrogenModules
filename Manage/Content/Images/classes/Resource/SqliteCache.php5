<?php
class Resource_SqliteCache{

	public function __construct( $cacheFile ){
		if( !extension_loaded('pdo') )
			throw new RuntimeException( 'PHP extension PDO is not installed' );
		if( !extension_loaded('pdo_sqlite' ) )
			throw new RuntimeException( 'PDO driver pdo_sqlite is not installed' );

		$this->db	= new PDO( "sqlite:".$cacheFile );
		$q	= $this->db->query( 'SELECT * FROM store LIMIT 1' );
		if( $q === FALSE )
			$this->db->query( "CREATE TABLE store (id text, data blob, PRIMARY KEY (id) )");
	}

	public function flush( $prefix = NULL ){
		if( $prefix )
			$this->db->query( "DELETE FROM store WHERE id LIKE '".str_replace( "%", "\%", $prefix )."%';" );
		else
			$this->db->query( "TRUNCATE store" );
		$this->db->query( "VACUUM store" );
	}

	public function get( $id ){
		$q	= $this->db->query( "SELECT data FROM store WHERE id = '".$id."'" )->fetch( PDO::FETCH_OBJ );
		if( $q )
			return $q->data;
		return NULL;
	}

	public function index(){
		$list	= array();
		$q		= $this->db->query( "SELECT id FROM store" );
		foreach( $q->fetchAll( PDO::FETCH_OBJ ) as $entry )
			$list[]	= $entry->id;
		return $list;
	}

	public function remove( $id ){
		$this->db->query( "DELETE FROM store WHERE id='".$id."';" );
		$this->db->query( "VACUUM store" );
	}

	public function set( $id, $data ){
		$q	= $this->db->query( "INSERT INTO store VALUES ('".$id."', '".addslashes( $data )."');" );
		print_m( $q->fetch() );
		print_m( $this->db->errorInfo() );
		die;
	}
}
?>
