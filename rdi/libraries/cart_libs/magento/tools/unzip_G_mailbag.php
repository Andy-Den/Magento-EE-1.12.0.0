<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
chdir('../../../../');
include 'init.php';

global $inPath, $rdi_path, $manager;

/* clean up if needed
if ($handle = opendir("in/")) 
{  

    while (false !== ($filename = readdir($handle))) 
    {
        if(substr($filename,-4)== ".jpg")
        {				
           unlink($rdi_path . $inPath . "/" .$filename);
        }
    }

    closedir($handle);
}*/
    


if ($handle = opendir("in/images/")) 
{  

    while (false !== ($filename = readdir($handle))) 
    {
        if(substr($filename,-5)== "t.jpg")
        {				
           unlink($rdi_path . $inPath . "/images/" .$filename);
        }
    }

    closedir($handle);
}  


   $dirHandle = @opendir($rdi_path . $inPath);
   if (!$dirHandle) 
   {
       // if the directory cannot be opened skip upload
       echo 'Cannot open the directory' . $rdi_path . $inPath;
   } 
   else 
   {
       while ($file = readdir($dirHandle))             
       { 
           if ($file != "."     
                   && strpos(strtolower($file), "g") === 0
                   && substr(strtolower($file), -4) == '.zip'
                   && file_exists($inPath . '/' . $file)
                   && is_readable($inPath . '/' . $file)
              ) 
           {    
				copy($rdi_path . $inPath . "/" . $file,$rdi_path . $inPath . "/images/" . $file);
				
               $manager->unzip_file($rdi_path . $inPath . "/images/" . $file,$rdi_path . $inPath . "/images/");
           }
       }           
   }
    
    
if ($handle = opendir("in/images/")) 
{  

    while (false !== ($filename = readdir($handle))) 
    {
        if(substr($filename,-5)== "t.jpg")
        {				
           unlink($rdi_path . $inPath . "/images/" .$filename);
        }
    }

    closedir($handle);
}    
    



?>