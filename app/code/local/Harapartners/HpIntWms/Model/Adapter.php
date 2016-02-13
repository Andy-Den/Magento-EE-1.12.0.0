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
class Harapartners_HpIntWms_Model_Adapter
{
    
    protected $_conection;
    protected $_isTransaction;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $dbLocation = Mage::getStoreConfig('hpintwms/conection_config/endpoint_info');
        $dbUser = Mage::getStoreConfig('hpintwms/conection_config/username');
        $dbPassword = Mage::getStoreConfig('hpintwms/conection_config/password');
        $dbName = Mage::getStoreConfig('hpintwms/conection_config/database_name');
        $port = Mage::getStoreConfig('hpintwms/conection_config/port');
        $zendConnectionType = Mage::getStoreConfig('hpintwms/conection_config/zend_conection_type');
        $pdoAdapterType = Mage::getStoreConfig('hpintwms/conection_config/pdo_adapter_type');
        
        $arrayParams = array();
        $arrayParams['host'] = $dbLocation;
        $arrayParams['username'] = $dbUser;
        $arrayParams['password'] = $dbPassword;
        $arrayParams['dbname'] = $dbName;
        if ($port) {
            $arrayParams['port'] = $port;
        }
        if ($pdoAdapterType) {
            $arrayParams['pdoType'] = $pdoAdapterType;
        }
        
        $db = Zend_Db::factory($zendConnectionType, $arrayParams);
        $this->_conection = $db;
    }

    public function getInventory()
    {
        $conection = $this->getConection();
        $select = $conection->select()->from('Inventory', '*'); //item_inventory
        $stmt = $conection->query($select);
        return $stmt->fetchAll();
    }

    public function fetchAll($options)
    {
        $select = $this->getConection()->select();
        if (! empty($options['from'])) {
            list ($name, $cols) = $options['from'];
            $select->from($name, $cols);
        }
        if (! empty($options['where'])) {
            if (isset($options['where']['type'])) {
                $options['where'] = array(
                    $options['where']
                );
            }
            foreach ($options['where'] as $where) {
                $select->$where['type']($where['value']);
            }
        }
        try {
            $stmt = $this->getConection()->query($select);
            return $stmt->fetchAll();
        } catch (Zend_Db_Statement_Exception $ze) {
            return false;
        }
    }

    public function getConection()
    {
        return $this->_conection;
    }

    public function getRequestGen($type)
    {
        return Mage::getModel('hpintwms/adapter_request_' . $type);
    }

    public function insert($query)
    {
        $tableName = $query['table'];
        $value = $query['value'];
        try {
            return $this->getConection()->insert($tableName, $value);
        } catch (Zend_Db_Statement_Mysqli_Exception $e) {
            return false;
        }
    }

    public function update($query)
    {
        $tableName = $query['table'];
        $value = $query['value'];
        $where = $query['where'];
        try {
            return $this->getConection()->update($tableName, $value, $where);
        } catch (Zend_Db_Statement_Mysqli_Exception $e) {
            return false;
        }
    }

    public function upsort($query, $pk)
    {
        // Create where
        $isSelectNeeded = true;
        $tableName = $query['table'];
        foreach ($pk as $key) {
            if (empty($query['value'][$key])) {
                $isSelectNeeded = false;
                break;
            }
            $where[] = "{$key} = {$query['value'][$key]}";
        }
        
        // Check for Existence
        if ($isSelectNeeded) {
            $select = $this->getConection()->select();
            $select->from($tableName, $pk);
            foreach ($where as $cond) {
                $select->where($cond);
            }
            
            $result = $this->getConection()->fetchRow($select->__toString());
        }
        
        // Perform Function
        if (empty($result)) {
            return $this->insert($query);
        } else {
            $query['where'] = $where;
            return $this->update($query);
        }
    }

    public function startTransaction()
    {
        $result = 0;
        if (! $this->_isTransaction) {
            $result = $this->getConection()->beginTransaction();
            $this->_isTransaction = true;
        }
        return $result;
    }

    public function transactionRollback()
    {
        if ($this->_isTransaction) {
            return $this->getConection()->rollBack();
        }
        return false;
    }

    public function transactionCommit()
    {
        if ($this->_isTransaction) {
            return $this->getConection()->commit();
        }
        return false;
    }

}
