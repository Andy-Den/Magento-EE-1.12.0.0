<?php

include_once "init.php";

//get the processor for the import return function
$upload = $pos->get_processor("rdi_pos_upload")->upload('return');

?>