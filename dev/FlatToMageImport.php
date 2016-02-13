<?php
$importChopper = new Flattomageimport();
$importChopper->masterFunction();

class Flattomageimport
{
    // Constants
    const FIELD_SKU = 'sku';
    const FIELD_TYPE = '_type';
    const FIELD_SUPER_PRODUCTS_SKU = '_super_products_sku';
    const FIELD_SUPER_ATTRIBUTE_CODE = '_super_attribute_code';
    const FIELD_SUPER_ATTRIBUTE_OPTION = '_super_attribute_option';
    const FIELD_MEDIA_LABLE = '_media_lable';
    
    const TYPE_SIMPLE = 'simple';
    const TYPE_CONFIGURABLE = 'configurable';
    
    protected $_inFileHandle;
    protected $_outFileHandle;
    protected $_outFileName;
    protected $_chunkCount = 0;
    protected $_isCheckFileSize = true;
    protected $_chunkSizeBytes = 2097152;
    // 14680064 ->14 mB // 7340032 -> 7 mB // 33554432 -> 32mb // 2097152 -> 2 mb // 4194304 -> 4mb // 524288 -> is .5mb // 209715 -> .2mb
    // 8000000; //8388608 is 8meg ; 10485760 is 10meg // size 750000 
    

    protected $_header;
    protected $_commaArray = array();
    protected $_unsetIndexes = array();
    
    protected $_superAttribs;
    protected $_superAttributeMaster;
    protected $_superAttributeCompressionField;
    protected $_configurableProductParentField;
    protected $_rowsToUnset;
    protected $_indexSku;
    protected $_indexType;
    protected $_indexConfigurableProductParent;
    protected $_indexSuperAttributeMaster;
    protected $_indexSuperAttributeCompressionField;
    protected $_indexSuperAttributes = array();
    
    protected $_isCompression = 0;
    protected $_compressionAtrribOffest = 0;
    protected $_masterAttribOffsetMap = array();
    
    protected $_bunchToConfig = array();
    protected $_uncompressToSimple = array();
    protected $_bunchedInfo = array();

    // === INIT === //
    protected function _initCleanHeader()
    {
        // Create the leading comas for the config rows.
        foreach ($this->_header as $number) {
            $commaArray[] = '';
        }
        // Remove the unset; From comma and header
        foreach ($this->_rowsToUnset as $name) {
            $i = array_search($name, $this->_header);
            if ($i !== false) {
                $this->_unsetIndexes[] = $i;
                unset($this->_header[$i]);
                array_pop($commaArray);
            }
        }
        
        // Add one back to header for type And Save for later
        $commaArray[] = '';
        $this->_commaArray = $commaArray;
        
        // Now we can add the Type & Super colums to the header
        array_push($this->_header, self::FIELD_TYPE, self::FIELD_SUPER_PRODUCTS_SKU, self::FIELD_SUPER_ATTRIBUTE_CODE, self::FIELD_SUPER_ATTRIBUTE_OPTION, self::FIELD_MEDIA_LABLE);
    }

    protected function _initIndexMap()
    {
        // Map Header to index
        $header = $this->_header;
        
        $this->_indexType = array_search(self::FIELD_TYPE, $header);
        $this->_indexConfigurableProductParent = array_search($this->_configurableProductParentField, $header);
        $this->_indexSuperAttributeMaster = array_search($this->_superAttributeMaster, $header);
        $this->_indexSuperAttributeCompressionField = array_search($this->_superAttributeCompressionField, $header);
        $this->_indexSku = array_search(self::FIELD_SKU, $header);
        foreach ($this->_superAttribs as $superAttrib) {
            $tempIndex = array_search($superAttrib, $this->_header);
            if ($tempIndex !== false) {
                // It exists so add it to index
                $this->_indexSuperAttributes[$superAttrib] = array_search($superAttrib, $this->_header);
            } else {
                // So it does not exist. Print warning
                echo 'Missing Super Attribute Column:' . $superAttrib;
            }
        }
    }

    protected function _initAttributes()
    {
        $superAttribs = array(
            //'shoe_size' , 
            //'width'
            'size'
        );
        $superAttribMaster = 'color';
        array_unshift($superAttribs, $superAttribMaster);
        
        // Save for Later
        $this->_superAttribs = $superAttribs;
        $this->_superAttributeMaster = $superAttribMaster;
        $this->_superAttributeCompressionField = 'shoe_color_weird'; // OLD $superAttribMapField;
        $this->_configurableProductParentField = 'parent_sku'; // OLD $sortFieldName;
        $this->_rowsToUnset = array( // OLD $unsetRows;
            'parent_sku' , 
            'ca_parent_sku'
        );
    }

    protected function _initBunchMapping()
    {
        $this->_uncompressToSimple = array();
        $this->_bunchToConfig = array(
            'image'
        );
        $this->_indexsBunchToConfig = array();
        foreach ($this->_bunchToConfig as $bunchItem) {
            $tempIndex = array_search($bunchItem, $this->_header);
            if ($tempIndex !== false) {
                $this->_indexsBunchToConfig[$bunchItem] = array_search($bunchItem, $this->_header);
            } else {
                echo 'Missing bunch Attribute Column:' . $bunchItem;
            }
        }
    }

    // === END INIT === //
    

    // === Out File Related === //
    protected function _isOutFileFull()
    {
        clearstatcache();
        $outFileName = $this->_getOutFileName();
        if (file_exists($outFileName)) {
            if ($this->_isCheckFileSize) {
                if (filesize($outFileName) > $this->_chunkSizeBytes) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    protected function _getOutFileName()
    {
        $outFilePrefix = 'validSorted_Chunk_';
        $outFileSuffix = '.csv';
        
        $outFileName = $outFilePrefix . $this->_chunkCount . $outFileSuffix;
        $this->_outFileName = $outFileName;
        return $outFileName;
    }

    protected function _closeAndOpenNewOutFileForWriting()
    {
        fclose($this->_outFileHandle);
        $this->_chunkCount ++;
        $this->_openNewOutFileForWriting();
    }

    protected function _openNewOutFileForWriting()
    {
        $outFileName = $this->_getOutFileName();
        $outFileHandle = fopen($outFileName, 'w');
        fputcsv($outFileHandle, $this->_header);
        $this->_outFileHandle = $outFileHandle;
    }

    // === END Out File Related === //
    

    // === Attribute Compression Related === //
    protected function _getNewAttributeSuffix($rowData)
    {
        if (! $this->_isCompression) {
            return $rowData[$this->_indexSuperAttributeMaster];
        }

        $compressionAtrribOffest = 0;
        $superAttributeMaster = $this->_superAttributeCompressionField;
        
        $masterAttribSuffixMap = $compressionAtrribOffest < 10 ? $rowData[$this->_indexSuperAttributeMaster] . '_0' . $compressionAtrribOffest : $rowData[$this->_indexSuperAttributeMaster] . '_' . $compressionAtrribOffest;
        $this->_masterAttribOffsetMap[$rowData[$this->_indexSuperAttributeCompressionField]] = $masterAttribSuffixMap;
        
        $this->_compressionAtrribOffest = $compressionAtrribOffest;
        return $masterAttribSuffixMap;
    }

    protected function _getNextAttributeSuffix($rowData, $superBuffer, $superAttrib)
    {
        if (! $this->_isCompression) {
            return $rowData[$this->_indexSuperAttributeMaster];
        }
        
        $compressionAtrribOffest = $this->_compressionAtrribOffest;
        $superAttributeMaster = $this->_superAttributeCompressionField;
        
        $masterAttribSuffixMap = $compressionAtrribOffest < 10 ? $rowData[$this->_indexSuperAttributeMaster] . '_0' . $compressionAtrribOffest : $rowData[$this->_indexSuperAttributeMaster] . '_' . $compressionAtrribOffest;
        while (isset($superBuffer[$masterAttribSuffixMap][$superAttrib])) {
            // This should get a valid slot For mapped value
            $compressionAtrribOffest ++;
            $masterAttribSuffixMap = $compressionAtrribOffest < 10 ? $rowData[$this->_indexSuperAttributeMaster] . '_0' . $compressionAtrribOffest : $rowData[$this->_indexSuperAttributeMaster] . '_' . $compressionAtrribOffest;
        }
        $this->_masterAttribOffsetMap[$rowData[$this->_indexSuperAttributeCompressionField]] = $masterAttribSuffixMap;
        
        $this->_compressionAtrribOffest = $compressionAtrribOffest;
        return $masterAttribSuffixMap;
    }

    // === END Attribute Compression Related === //
    

    function __construct() // TODO change to Mage version if needed...
    {
        // Open In File
        $fileLocation = 'april.csv'; //'validSortedChoppedRdy_sku_colorsFilled.csv';
        $this->_inFileHandle = fopen($fileLocation, 'r');
        $this->_header = fgetcsv($this->_inFileHandle);
        
        // INIT ATTRIBUTES
        $this->_initAttributes();
        $this->_initIndexMap();
        $this->_initBunchMapping();
        $this->_initCleanHeader();
        
        // Open First out
        $this->_openNewOutFileForWriting();
    
    }
    
    protected function _isNewConfigurable(array $rowData){
        if($this->_isConfigProducts){
            if($rowData[3]=='configurable'){
                return true;
            }else{
                return false;
            }
        }else {
            // Check $config Row
        }
    }
    
    protected function _isNewCompression(array $rowData){
        return false;
    }

    public function masterFunction()
    {
        echo 'This Takes a SORTED list as input. Use dos/unix sort command from command line';
        
        // MAIN INIT is in construct
        // INIT Locals
        $buffer = array();
        $superBuffer = array();
        $oldRowId = '';
        $newRowId;
        $oldCompressionMap = '';
        $newCompressionMap;
        $count = 0;
        $masterAttribSuffixMap = '';
        $superAttributeMaster = $this->_superAttributeMaster;
        
        $indexConfigurableProductParent = $this->_indexConfigurableProductParent; // $sortfield
        $indexSuperAttributeCompressionField = $this->_indexSuperAttributeCompressionField; // $attribMapFieldIndex        
        

        // RUN
        while ($rowData = fgetcsv($this->_inFileHandle)) {
            $count ++;
            // $newRowId = $this->_getNewRowId($rowData); //$rowData[$indexConfigurableProductParent];
            // $newCompressionMap =  $this->_getNewRowId($rowData);// $rowData[$indexSuperAttributeCompressionField];
            if ($this->_isNewConfigurable($rowData)) {
                if ($this->_isNewCompression($rowData)) {
                    // WE have a duplicate on the Master Super Attribute OR it is the first one
                    $masterAttribSuffixMap = $this->_getNextAttributeSuffix($rowData, $superBuffer, $superAttrib);
                    $oldCompressionMap = $newCompressionMap;
                }
                $buffer[] = $rowData;
                foreach ($this->_superAttribs as $superAttrib) {
                    $tempSuperName = ($superAttrib === $this->_superAttributeMaster) ? $masterAttribSuffixMap : $rowData[$this->_indexSuperAttributes[$superAttrib]];
                    $superBuffer[$masterAttribSuffixMap][$superAttrib][] = array(
                        'SuperName' => $tempSuperName , 
                        'sku' => $rowData[$this->_indexSku]
                    );
                }
                $this->_bunchedInfo[$rowData[self::FIELD_SKU]]['media'] = $rowData[13];
            } else {
                // New Config Item (Or First): Write it out
                $this->_writeToCsv($buffer, $superBuffer);
                
                // === Reset Stuff & Start a New Buffer/SuperBuffer === //
                $masterAttribSuffixMap = $this->_getNewAttributeSuffix($rowData);
                
                // $oldRowId = $newRowId;
                // $oldCompressionMap = $newCompressionMap;
                

                // This adds the first row and resets the fields
                $buffer = array(
                    $rowData
                );
                $superBuffer = array();
                foreach ($this->_superAttribs as $superAttrib) {
                    $tempSuperName = ($superAttrib === $this->_superAttributeMaster) ? $masterAttribSuffixMap : $rowData[$this->_indexSuperAttributes[$superAttrib]];
                    $superBuffer[$masterAttribSuffixMap][$superAttrib][] = array(
                        'SuperName' => $tempSuperName , 
                        'sku' => $rowData[$this->_indexSku]
                    );
                }
                $this->_bunchedInfo[$rowData[self::FIELD_SKU]]['media'] = $rowData[13];
            }
        }
        
        //save Last
        $this->_writeToCsv($buffer, $superBuffer);
    }

    protected function _writeToCsv($buffer, $superBuffer)
    {
        // Write it out
        if ($buffer) {
            if ($this->_isOutFileFull()) {
                $this->_closeAndOpenNewOutFileForWriting();
            }
            
            // Write to the file
            foreach ($buffer as $line) {
                // This converts the Attribute from the regular name to the Master Attrib Name (Multi => Mulit_01)
                $line[$this->_indexSuperAttributeMaster] = $this->_masterAttribOffsetMap[$line[$this->_indexSuperAttributeCompressionField]];
                
                // Unset some rows before printing.
                foreach ($this->_unsetIndexes as $unsetIndex) {
                    unset($line[$unsetIndex]);
                }
                
                // Add the type to the row & write it out
                array_push($line, self::TYPE_SIMPLE, '', '', '', '');
                fputcsv($this->_outFileHandle, $line);
            }
            
            // Write the super stuff
            $this->_writeSuperAttribsToCsv($buffer, $superBuffer);
        }
    }

    protected function _writeSuperAttribsToCsv($buffer, $superBuffer)
    {
        $superAttributeMaster = $this->_superAttributeMaster;
        $commaArray = $this->_commaArray;
        
        // First we need to write the config product out. This is done here to reduce loop complexity. It shouldnt go past the first instance.
        // We need to use from buffer
        $firstLine = reset($buffer);
        
        // Change the SKU to the oldRowID
        $firstLine[$this->_indexSku] = 'TESTSKU'; // $oldRowId;
        

        // This converts the Attribute from the regular name to the Master Attrib Name (Multi => Mulit_01)
        $firstLine[$this->_indexSuperAttributeMaster] = $this->_masterAttribOffsetMap[$firstLine[$this->_indexSuperAttributeCompressionField]];
        $usedSuperAttribs = array();
        
        foreach ($this->_unsetIndexes as $unsetIndex) {
            // Unset some rows before printing. They are sanitized before?
            unset($firstLine[$unsetIndex]);
        }
        
        array_push($firstLine, self::TYPE_CONFIGURABLE);
        unset($outinfo); // TODO ??
        

        foreach ($superBuffer as $masterAttribName => $subAttribs) {
            // Find one row with more then one entry
            foreach ($subAttribs as $subAttribName => $Attribs) {
                if (count($Attribs) > 1) {
                    foreach ($Attribs as $attribName => $attribSku) {
                        // Take one, unset it, write it and break
                        // If we dont find one then the super writing should not find any either.
                        $outInfo = array(
                            self::FIELD_SUPER_PRODUCTS_SKU => $attribSku['sku'] , 
                            self::FIELD_SUPER_ATTRIBUTE_CODE => $subAttribName , 
                            self::FIELD_SUPER_ATTRIBUTE_OPTION => $attribSku['SuperName']
                        );
                        $tempArray = $firstLine + $outInfo;
                        unset($superBuffer[$masterAttribName][$subAttribName][$attribName]); // Need to reach back to the original Array.
                        if (! isset($usedSuperAttribs[$subAttribName][$attribSku['SuperName']]) || $subAttribName === $this->_superAttributeMaster) {
                            fputcsv($this->_outFileHandle, $tempArray);
                        }
                        $usedSuperAttribs[$subAttribName][$attribSku['SuperName']] = $attribSku['sku']; // save used key value pairs
                        break (3);
                    }
                }
            }
        }
        
        // This is for writing the rest
        // This supports 3 levels of config product => TODO add more layers !!!!
        foreach ($superBuffer as $masterAttribName => $subAttribs) {
            foreach ($subAttribs as $subAttribName => $Attribs) {
                if (count($Attribs) > 1) {
                    // Do twice becasue its two layers deep. This is the problem from data structure ish?
                    foreach ($Attribs as $attribName => $attribSku) {
                        $outInfo = array(
                            self::FIELD_SUPER_PRODUCTS_SKU => $attribSku['sku'] , 
                            self::FIELD_SUPER_ATTRIBUTE_CODE => $subAttribName , 
                            self::FIELD_SUPER_ATTRIBUTE_OPTION => $attribSku['SuperName']
                        );
                        $tempArray = $commaArray + $outInfo; // Arrays Copy
                        if (! isset($usedSuperAttribs[$subAttribName][$attribSku['SuperName']]) || $subAttribName === $this->_superAttributeMaster) {
                            fputcsv($this->_outFileHandle, $tempArray);
                        }
                        $usedSuperAttribs[$subAttribName][$attribSku['SuperName']] = $attribSku['sku']; // save used key value pairs
                    }
                }
            }
        }
    }
}
