<?php


$storage	= new Storage();
$undo		= new DatabaseUndo( $this->env->getDatabase(), $storage );
$this->env->storage	= $undo;

$model	= new Model_Mission( $this->env );
#$model->add( array( 'content' => 'Test-'.time() ) );
#$model->edit( 54, array( 'content' => 'Test-'.time() ) );
#$undo->revert();
#print_m( $undo->storage->items );
#die;

return '
Hello World!
';
?>
