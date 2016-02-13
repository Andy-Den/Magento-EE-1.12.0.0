<?php
/**
 * Magento Helper Extension class.
 */
class rdiInventoryUpdate extends Mage_CatalogInventory_Model_Stock_Item {

    private $indexer;

    public function loadByProduct($product_id)
    {
        if (!isset($this->indexer))
        {
            $this->indexer = Mage::getSingleton('index/indexer');
        }

        $this->_getResource()->loadByProductId($this, $product_id);
        $this->setOrigData();
        return $this;
    }

    public function setQty($quantity)
    {
        parent::setQty($quantity);

        return $this;
    }

    public function setProcessIndexEvents($process = true)
    {
        $this->_processIndexEvents = $process;
        return $this;
    }

    public function newSave()
    {
        $this->indexer->logEvent($this, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE);
    }

}


?>