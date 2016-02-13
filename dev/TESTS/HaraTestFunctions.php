<?php

class HaraTestFunctions
{
    protected $_testName = 'unknown';

    public function initMage()
    {
        error_reporting(- 1);
        $path = dirname(__FILE__) . '/../../app/Mage.php';
        require_once realpath($path);
        Mage::app();
        
        return $this;
    }

    public function outputControlMessage($msg)
    {
        echo "<br> {$this->_testName} Requires: {$msg} <br>";
    }

    public function outputWarningMessage($msg)
    {
        echo "{$this->_testName} Note: {$msg} <br>";
    }

    public function outputStartMsg($name = '')
    {
        echo "--- Starting {$this->_testName} ---<br><br>";
    }

    public function outputEndMsg($name)
    {
        echo "<br><br>--- Ending {$this->_testName} ---<br>";
    }

    public function setTestName($name)
    {
        $this->_testName = $name;
    }
}
