<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */
class Harapartners_ShoemartEdi_Model_Transfer
{
    
    protected $_ftp = null;

    public function uploadFilesToEndpoint()
    {
        $ftp = null;
        $files = $this->_getOutgoingFiles();
        $transferResult = $this->_putFilesOnFtp($files);
        $moveToCompletedResult = $this->_moveToCompleted($files, $transferResult);
        
        return $moveToCompletedResult;
    }

    public function getFilesFromEndpoint()
    {
        $ftp = null;
        $files = $this->_getIncomingFiles();
        $transferResult = $this->_getFilesFromFtp($files);
        $moveResult = $this->_updateDledFiles($transferResult);
        
        return $moveResult;
    }

    protected function _getOutgoingFiles()
    {
        $outgoingDirectory = $this->_getOutgoingDirectory();
        $outgoingFiles = glob($outgoingDirectory . DS . '*.*', GLOB_NOESCAPE);
        return $outgoingFiles;
    }

    protected function _getIncomingFiles()
    {
        $ftpDir = '';
        $ftp = $this->_getOpenFtpConection('in');
        
        // Get Convert to direct listing (glob like)
        $files = $ftp->ls();
        foreach (files as $file) {
            $fileDirectToPath[] = $file['text'];
        }
        
        // Hook to chose which files we want to DL
        $validFileNames = $this->_parseForValidNames($fileDirectToPath);
        return $validFileNames;
    }

    protected function _parseForValidNames($files)
    {
        return $files;
    }

    protected function _getIncomingDirectory()
    {
        $relativeDir = Mage::getStoreConfig('shoemart_edi/sync_setting/base_dir');
        $baseDir = Mage::getBaseDir();
        $suffixDir = 'in';
        return $baseDir . DS . $relativeDir . DS . $suffixDir;
    }

    protected function _getOutgoingDirectory()
    {
        $relativeDir = Mage::getStoreConfig('shoemart_edi/sync_setting/base_dir');
        $baseDir = Mage::getBaseDir();
        $suffixDir = 'out';
        return $baseDir . DS . $relativeDir . DS . $suffixDir;
    }

    protected function _getOutgoingCompletedDirectory()
    {
        $relativeDir = $this->_getOutgoingDirectory();
        return $relativeDir . DS . 'completed';
    }

    /**
     * Transfers files to the FTP server
     *
     * @param array $files File Paths
     * @return array|false array of results for the files Or false on general failure
     */
    protected function _putFilesOnFtp($files)
    {
        foreach ($files as $file) {
            $results[] = true;
        }
        return $results;
        
        $ftp = $this->_getOpenFtpConection('out');
        if (! $ftp) {
            return false;
        }
        
        foreach ($files as $count => $file) {
            if ($this->_isFileSuitable($file)) {
                $fileName = basename($file);
                $result = $ftp->write($fileName, $file);
                $results[$count] = $result;
            }
        }
        
        return $results;
    }

    protected function _getFilesFromFtp($files)
    {
        $ftp = $this->_getOpenFtpConection('in');
        $mageDir = $this->_getIncomingDirectory();
        
        $results = array();
        foreach ($files as $count => $files) {
            $fileName = basename($file);
            $fullPath = $mageDir . DS . $fileName;
            $result = $ftp->read($file, $fullPath);
        }
    }

    /**
     * Gets an open conection to the FTP Endpoint
     *
     * @return Varien_Io_Ftp|false
     */
    protected function _getOpenFtpConection($direction)
    {
        if (! isset($this->_ftp)) {
            $ftp = new Varien_Io_Ftp();
            try {
                $ftp->open($this->_getFtpOptionArray($direction));
                $this->_ftp = $ftp;
            } catch (Varien_Io_Exception $ioEx) {
                Mage::logException($ioEx);
                $this->_ftp = false;
            }
        }
        
        return $this->_ftp;
    }

    protected function _moveToCompleted($files, $fileResults)
    {
        $outgoingFolder = $this->_getOutgoingDirectory();
        $completedFolder = $this->_getOutgoingCompletedDirectory();
        
        $varienFile = new Varien_Io_File();
        $varienFile->checkAndCreateFolder($completedFolder);
        $varienFile->cd($completedFolder);
        
        $results = array();
        foreach ($files as $count => $file) {
            if ($fileResults[$count]) {
                $result = $varienFile->mv($file, basename($file));
                $results[$count] = $result;
            }
        }
        
        return $results;
    }
    
    protected function _updateDledFiles($files){
        // TODO
    }

    protected function _getFtpOptionArray($direction)
    {
        $host = Mage::getStoreConfig('shoemart_edi/conection_config/endpoint');
        $port = Mage::getStoreConfig('shoemart_edi/conection_config/port');
        $user = Mage::getStoreConfig('shoemart_edi/conection_config/username');
        $password = Mage::getStoreConfig('shoemart_edi/conection_config/password');
        $path = Mage::getStoreConfig('shoemart_edi/conection_config/path_' . $direction);
        
        return array(
            'host' => $host , 
            'port' => $port , 
            'user' => $user , 
            'password' => $password , 
            'path' => $path
        );
    }
}
