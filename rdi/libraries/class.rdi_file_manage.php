<?php

/*
 * This class was built for the purpose of checking files and deleting them after a set number of days.
 * it may later be expanded to check other things so for the moment I'm going to leave things farely open ended.
 *  
 * class created on 1-17-2012 by Kalinda Little
 * 
 * PHP version 5.3
 *
 * @author     Kalinda Little 
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\FileManagement
 */

class file_manage extends rdi_general{
    /*
     * since I don't know many of the classes yet, I'm going to assume for the moment 
     * that this name is not taken. it can be changed later if needed.
     * in addition I don't know what if anythign this class needs to extend.
     */
    
    private $folder;
    
    
    public function __construct(){
        
        // this is a test
        //$this->del_old_files(5, "libraries/testing");
        
    }
    
    public function get_files($folder){
        /*
         * this function should get all the files in the folder passed in,
         * and return them in an array.
         */
        
            $this->folder = $folder;
            
            //$dir = opendir($folder);
            
            // load $files, an array of everything in the folder.
            $allFiles = scandir($folder);
            
            /*
             *  go through the array to delete anything we don't care about
             *  in this case we are only looking for files so no subdirectories 
             *  and only real files so no "." or the like.  
             */
            
             $ourFiles = array();
             if(is_array($allFiles)){
                foreach($allFiles as $file){
                    //echo $file." -- <br/>";
                    if(
                        !is_array($file) && // is not a folder
                        $file != "." && // is not a dot
                        $file != ".." && // is not a pair of dots
                        count(explode(".", $file)) > 1 // has at least one dot in it ( for the extention: file.txt )
                        // add more as nessisary
                    ){
                        $ourFiles[] = $file;
                    }
                }
                
                // check that at least some of the contents meat the paramiters.
                if(count($ourFiles) < 1){
                    return false;
                }
                
                return $ourFiles;
                
             }else{
                  // if there are no files in the folder return false
                 return false;
             }
        
    }
    
    
    public function get_file_info($file, $folder = ""){
        /*
         * this function takes in a single file and returns all the 
         * information about that file in an array.
         */
        
        // check the folder
        if(strlen($folder) < 1 || empty($folder)){
            $folder = $this->folder;
        }
        
        // check if the file exists in that folder
        if( !file_exists($folder."/".$file)){
            // and if not return false
            return false;
        }
        
        // get the info
        $info = pathinfo($folder."/".$file);
        
        return $info;
        
    }
    
    public function get_modified_date($file, $folder = ""){
        /*
         * this function takes a file and folder and returns the last modified date of that file.
         */
        
        
        // check the folder
        if(strlen($folder) < 1 || empty($folder)){
            $folder = $this->folder;
        }
        
        // check if the file exists in that folder
        if( !file_exists($folder."/".$file)){
            // and if not return false
            return false;
        }
        
        $date = filemtime($folder."/".$file);
        
        return $date;
    }
    
    public function check_permissions($file){
        /*
         * this function will check the permissions of a user to a file and return 
         * either true if the user has permission or false if not.
         */
        
        // for now I'm just going to return true since I havn't seen the permission systems yet.
        if($file){
            return true;
        }
    }
    
    public function check_date($days, $file, $folder = ""){
        /*
         * this function takes the number of days and the file and then 
         * checks if the file has been modified in that time
         * it then returns true or false
         */
        
        
        // check the folder
        if(strlen($folder) < 1 || empty($folder)){
            $folder = $this->folder;
        }
        
        $date = $this->get_modified_date($file, $folder);
        
        if(!$date){
            return false;
        }
        
        $checkDate = mktime(0,0,0, date("m"), (date("j")- $days), date("Y"));
        
        if($date < $checkDate){
            return false;
        }else{
            return true;
        }
    }
    
    public function delete_old_files($days, $folder){
        /*
         *  this function should get all the files in a folder and check the modification dates 
         *  from there it should delete the files that are more then $days old
         */
        
        if(file_exists($folder))
        {
            // get the files
            $files = $this->get_files($folder);

            if(is_array($files)){

                foreach($files as $file){

                    // check the change date of the file
                    $check = $this->check_date($days, $file, $folder);

                    $perms = $this->check_permissions($file);

                    if(!$check && $perms){
                        //echo "Deleting file: ".$file."<br/>";
                        // delete the file
                        unlink($folder."/".$file);
                    }
                }
            }
        }
    }
    
    public function unzip_file($full_filename,$target_path = 'rdi/in', $archive = "archive")
    {   
         /*
         *  This function unzips a file and archives it. PMB 01282013
         */
        
        // get the files
        global $inPath, $rdi_path, $debug; 

	$message = true;
		
        if($full_filename) 
        {

            $path_parts = pathinfo($full_filename);
            $filename   = $path_parts["filename"];
            $source     = $full_filename; 
            $ext        = $path_parts["extension"];

            $continue   = strtolower($ext) == 'zip' ? true : false;

            if(!$continue) 
            {
                 $message = "The file you are trying to upload is not a .zip file. Please try again.";
                 echo $message;
            }

              // change this to the correct site path
            
                $zip = new ZipArchive();
                $x = $zip->open($source);
                if ($x === true) 
                {
                    $zip->extractTo($target_path); // change this to the correct site path
                    $zip->close();

                    if($archive !== "")
                    {
                        if(!file_exists($target_path . "/{$archive}"))
                        {
                            mkdir($target_path . "/{$archive}",0777);
                        }

                        if(rename($source,$target_path . "/{$archive}/" . $filename . "." . $ext))
                        {
                            $debug->write("class.rdi_file_manage.php", "", "archived zipped file", 1,$source);
                        }
                        else
                        {
                            echo "could not archive zip file check permissions ". $source;
                            $debug->write("class.rdi_file_manage.php", "", "could not archive zip file check permissions", 1,$source);
                        }
                    }
                }
            

        }
        
        if($message)
        {
            return true;
        }   
         
        echo $message;
        return false;
        
    }
    
    
    
}

?>
