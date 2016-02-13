<?php

/**
 * Adapted by Christopher Shennan 
 * http://www.chrisshennan.com
 * 
 * Date: 20/04/2011
 * 
 * Adaptered from original post by Srinigenie
 * Original Post - http://www.magentocommerce.com/boards/viewthread/9391/
 */

class Mage_Eav_Model_Import extends Mage_Eav_Model_Mysql4_Entity_Attribute {

  private $fileName;
  private $delimiter = '|';
  private $enclosure = '"';

  private function &getCsv() {
    $file = fopen($this->fileName, "r");
    while (!feof($file)) {
      $csvArr[] = fgetcsv($file, 0, $this->delimiter, $this->enclosure);
    }

    fclose($file);
    return $csvArr;
  }

  protected function populateOptionTable($attribId) {
    echo "Upload Begin<br/>";

    $fields = array();
    $values = array(); // store id => values
    $optionValues = array(); // option id => $values
    $option = array('value' => $optionValues);
    $updateOptionValId;
    $values = null;
    $row = null;
    $disCounter = 0;

    $optionTable = $this->getTable('attribute_option');
    $optionValueTable = $this->getTable('attribute_option_value');
    $write = $this->_getWriteAdapter();
    $csvStoreArr = array();

    // Get CSV into Array
    $csv = & $this->getCsv();

    $read = $this->_getReadAdapter();

    // exit if the csv file is empty or if it contains only the headers
    if (count($csv) < 1 or count($csv) == 1)
      return;

    $fields = $csv[0]; // get the field headers from first row of CSV
    // get the store Ids
    $stores = Mage::getModel('core/store')
            ->getResourceCollection()
            ->setLoadDefault(true)
            ->load();

    // determine the stores for which option values are being uploaded for
    foreach ($fields as $hdr) {
      if ($hdr === 'position' || $hdr === 'isDefault' || $hdr === 'ERROR') {
        continue;
      }
      foreach ($stores as $store) {
        if ($store->getCode() === $hdr)
          $csvStoreArr[$hdr] = $store->getId();
      }
    }

    // start reading the option values - from row 1 (note that 0 represents headers)
    for ($indx = 1; $indx < count($csv); $indx++) {
      $values = null; // initialize to null
      $row = $csv[$indx]; // get row

      if (isset($row) && count($row) > 0) {

        //escape the single quote
        //$whereParam = $read->quote($row);


        if (is_array($row))
          $whereParam = '(\'' . implode($row, '\',\'') . '\')';
        else if (strlen($row))
          $whereParam = '(\'' . $row . '\')';

        $select = $read->select()->from(array('vals' => $optionValueTable))
                ->join(array('opt' => $optionTable), 'opt.option_id=vals.option_id')
                ->where('opt.attribute_id=?', $attribId);
        $select = $select
                ->where('vals.value in ' . $whereParam);

        $optionValData = $read->fetchAll($select);

        unset($select);

        // get the option Id for this option
        if (count($optionValData) > 0) {
          $optionValDataRow = $optionValData[0];
          $optionId = $optionValDataRow['option_id'];
        } else
          $optionId = null;

        $intOptionId = (int) $optionId;

        if (!$intOptionId) {
          $data = array(
              'attribute_id' => $attribId,
              'sort_order' => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
          );
          try {
            $write->insert($optionTable, $data);
            $intOptionId = $write->lastInsertId();
          } catch (Exception $e) {
            Mage::log($e->getMessage());
          }
        } else {
          $data = array(
              'sort_order' => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
          );
          $write->update($optionTable, $data, $write->quoteInto('option_id=?', $intOptionId));
        }

        $colIndx = 0; //initialize row's column index
        if (isset($row) && is_array($row) && count($row) > 0) {
          foreach ($row as $optVal) {
            if ($fields[$colIndx] !== 'position' || $fields[$colIndx] !== 'isDefault' || $fields[$colIndx] !== 'ERROR') {
              $values[$csvStoreArr[$fields[$colIndx]]] = $optVal; // store id => option value
            }
            $colIndx++;
          }
        }
      }


      if (isset($values) && is_array($values) && count($values) > 0) {
        foreach ($values as $storeId => $value) {
          if (!empty($value) || strlen($value) > 0) {
            $value = trim($value);
            $data = array(
                'option_id' => $intOptionId,
                'store_id' => $storeId,
                'value' => $value,
            );
            $optionValInsert = true;
            $optionValUpdate = false;

            foreach ($optionValData as $valData) {
              if ((int) $valData['option_id'] === $intOptionId &&
                      (int) $valData['store_id'] === $storeId) {
                $optionValInsert = false;
                if (strcasecmp(trim($valData['value']), $value) !== 0) {
                  $optionValUpdate = true;
                  $updateOptionValId = $valData['value_id'];
                }
                break;
              }
            }

            if ($optionValInsert) {
              $write->insert($optionValueTable, $data);
              Mage::log('Inserted Value -' . $value);
            } else if ($optionValUpdate) {
              $write->update($optionValueTable, $data, $write->quoteInto('option_id=?', $updateOptionValId));
              Mage::log('Updated Value -' . $value);
            }
          }
        }
      }
      $optionValues[$optionId] = $values;

      if ($indx % 20 == 0) {
        echo "" . $indx . ' - uploaded!!<br />';
        Mage::log($indx . ' - attributes uploaded!!', null, $this->fileName . '.log');
      }
    }
    echo "" . $indx . ' - uploaded!!<br />';
    echo '<b> Attribute Upload Finished </b><br />';
    $option['value'] = $optionValues;
    return null;
  }

  /**
   * Enter description here...
   *
   * @param Mage_Core_Model_Abstract $object
   * @return Mage_Eav_Model_Mysql4_Entity_Attribute
   */
  public function saveOptionValues($attributeId, $fn) {
    $option = array();

    $this->fileName = $fn;

    echo '<strong>Importing Attributes</strong><br/><br/>Reading file contents - ' . $this->fileName . '<br />';

    Mage::log("Upload Begin", null, $this->fileName . '.log');
    // Step 1 -- Get attribute Id from attribute code
    $atrribId = $attributeId; //569
    // Step 2 Obtain the option values into an array
    $option = $this->populateOptionTable($atrribId);
  }

}