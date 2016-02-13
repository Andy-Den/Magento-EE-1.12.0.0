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
class Harapartners_HpSalesforce_Model_Adapter
{
    
    protected $_connection;
    protected $_isTransaction;

    /**
     * Class constructor
     */
    public function __construct()
    {
    	/* @var $connection SforcePartnerClient */
        $connection = Mage::helper('hpsalesforce')->initSalesforceConnection();
        $this->_connection = $connection;
    }

    public function getConnection()
    {
        return $this->_connection;
    }
    
//
//	public function fetchAll($options)
//	{
//		$select = $this->getConection()->select();
//		if (! empty($options['from'])) {
//			list ($name, $cols) = $options['from'];
//			$select->from($name, $cols);
//		}
//		if (! empty($options['where'])) {
//			if (isset($options['where']['type'])) {
//				$options['where'] = array(
//				$options['where']
//				);
//			}
//			foreach ($options['where'] as $where) {
//				$select->$where['type']($where['value']);
//			}
//		}
//		try {
//			$stmt = $this->getConection()->query($select);
//			return $stmt->fetchAll();
//		} catch (Zend_Db_Statement_Exception $ze) {
//			return false;
//		}
//	}
//
//
//	public function getRequestGen($type)
//	{
//		return Mage::getModel('hpintwms/adapter_request_' . $type);
//	}
//
//	public function insert($query)
//	{
//		$tableName = $query['table'];
//		$value = $query['value'];
//		try {
//			return $this->getConection()->insert($tableName, $value);
//		} catch (Zend_Db_Statement_Mysqli_Exception $e) {
//			return false;
//		}
//	}
//
//	public function update($query)
//	{
//		$tableName = $query['table'];
//		$value = $query['value'];
//		$where = $query['where'];
//		try {
//			return $this->getConection()->update($tableName, $value, $where);
//		} catch (Zend_Db_Statement_Mysqli_Exception $e) {
//			return false;
//		}
//	}
//
//	public function upsort($query, $pk)
//	{
//		// Create where
//		$isSelectNeeded = true;
//		$tableName = $query['table'];
//		foreach ($pk as $key) {
//			if (empty($query['value'][$key])) {
//				$isSelectNeeded = false;
//				break;
//			}
//			$where[] = "{$key} = {$query['value'][$key]}";
//		}
//
//		// Check for Existence
//		if ($isSelectNeeded) {
//			$select = $this->getConection()->select();
//			$select->from($tableName, $pk);
//			foreach ($where as $cond) {
//				$select->where($cond);
//			}
//
//			$result = $this->getConection()->fetchRow($select->__toString());
//		}
//
//		// Perform Function
//		if(empty($result)){
//			return $this->insert($query);
//		}else{
//			$query['where'] = $where;
//			return $this->update($query);
//		}
//	}
//
//	public function startTransaction()
//	{
//		$result = 0;
//		if (! $this->_isTransaction) {
//			$result = $this->getConection()->beginTransaction();
//			$this->_isTransaction = true;
//		}
//		return $result;
//	}
//
//	public function transactionRollback()
//	{
//		if ($this->_isTransaction) {
//			return $this->getConection()->rollBack();
//		}
//		return false;
//	}
//
//	public function transactionCommit()
//	{
//		if ($this->_isTransaction) {
//			return $this->getConection()->commit();
//		}
//		return false;
//	}


}
