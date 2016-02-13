<?php 
require_once ("app/Mage.php");
umask(0);
Mage::app();//('shoemart');//Mage::app('default'); 
Mage::getSingleton('core/session', array('name' => 'frontend'));
$helper = Mage::helper('signaturelink');
$slSession = $helper->generateSLSession();

$validate_securebuy = $_REQUEST['validate_securebuy'];
if($validate_securebuy=="1"){
		$te_user = $_REQUEST['te_user'];
		$te_pass = $_REQUEST['te_pass'];
		$store_id = $_REQUEST['storeid'];
		$hop_client_id = $helper->GetHOPClientID($te_user, $te_pass, $store_id);	
		
		if(empty($hop_client_id)){
			die('error');
		} else {
			die($hop_client_id);
		}		
} 
?>
<div id="content">
<?php
   //handle the final step here so that we have everything in one page
   if (@$_GET["final"] != "") {
     $helper->HandleACSResponse(); //defined at the end of the page
   }
   else if ($_SERVER['REQUEST_METHOD'] == "POST") {
     $helper->Handle3DSecureAuthentication(); //defined at the end of the page
	 exit;
   }
   else {
	    $enable_3d_secure = $helper->Show3DS($_GET); //true or false
	  	if($enable_3d_secure == '1') { 
			$helper->MPIAuthentication($_GET); 
		} else { 
		?>
			<script language="javascript">
				parent.closeOpenedIframe('success','<?php echo $baseurlarr[0];?>');
			</script>
		<?php 
		}	
   }