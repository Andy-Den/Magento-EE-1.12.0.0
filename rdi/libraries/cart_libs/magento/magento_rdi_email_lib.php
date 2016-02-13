<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of magento_rdi_email_lib
 *
 * @author PMBliss
 * @package Core\Common\Magento\Email
 */
class magento_rdi_email_lib extends rdi_general {
    /*
     * Email class for magento.
     */

    public function __construct()
    {
        if (!class_exists('Mage'))
        {
            include "../app/Mage.php";

            umask(0);
            Mage::app();
        }
    }

    /*
     * csv is the default file type for now.
     */

    public function send_email($mess, $subject, $file = "")
    {
        $this->_echo($subject);
        $fromEmail = "support_magento@retaildimensions.com";
        $fromName = "Retail Dimensions - Magento Support";
        $toEmail = "pmbliss@retaildimensions.com";
        $toName = "Customer Name";
        $body = $mess;


        try
        {
            /* $config = array(
              'host' => 'smtp.gmail.com',
              'port' => 587,
              'ssl' => 'tls',
              'auth' => 'login',
              'username' => 'pmathbliss@gmail.com',
              'password' => ''
              );

              $tr = new Zend_Mail_Transport_Smtp('smtp.gmail.com',$config);
              //Zend_Mail::setDefaultTransport($transport); */

            $mail = new Zend_Mail();
            $mail->setFrom($fromEmail, $fromName);
            $mail->addTo($toEmail, $toName);
            $mail->setSubject($subject);
            $mail->setBodyHtml($body); // here u also use setBodyText options.

            $data = file_get_contents($file);

            // this is for to set the file format
            $at = new Zend_Mime_Part($data);

            $at->type = 'application/csv'; // if u have PDF then it would like -> 'application/pdf'
            $at->disposition = Zend_Mime::DISPOSITION_INLINE;
            $at->encoding = Zend_Mime::ENCODING_8BIT;
            $at->filename = "report.csv";
            $mail->addAttachment($at);
            $r = $mail->send($tr);
        } catch (Exception $e)
        {
            echo $e->getMessage();
        }
    }

}

?>
