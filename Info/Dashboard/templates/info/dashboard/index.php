<?php

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/info/dashboard/' ) );

return $textTop.$dashboard.$textBottom;

?>
