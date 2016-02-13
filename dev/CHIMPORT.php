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
    const FIELD_MEDIA_POSITION = '_media_position';
    
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
    protected $_typeInField;
    protected $_rowsToUnset;
    protected $_indexSku;
    protected $_indexType;
    protected $_indexConfigurableProductParent;
    protected $_indexTypeInField;
    protected $_indexSuperAttributeMaster;
    protected $_indexSuperAttributeCompressionField;
    protected $_indexSuperAttributes = array();
    
    protected $_isConfigProducts = true;
    protected $_currentConfigurable = '';
    protected $_isCompression = 0;
    protected $_compressionAtrribOffest = 0;
    protected $_currentCompression = '';
    protected $_masterAttribOffsetMap = array();
    
    protected $_bunchToConfig = array();
    protected $_isUncompressFields = array();
    protected $_bunchedInfo = array();
    protected $_indexsBunchToConfig = array();
    protected $_configRow;

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
        
        // Add two back to header for type
        $commaArray[] = '';
        $this->_commaArray = $commaArray;
        
        // Now we can add the Type & Super colums to the header
        $this->_header = array_values($this->_header);
        array_push($this->_header, self::FIELD_TYPE, self::FIELD_SUPER_PRODUCTS_SKU, self::FIELD_SUPER_ATTRIBUTE_CODE, self::FIELD_SUPER_ATTRIBUTE_OPTION, self::FIELD_MEDIA_LABLE, self::FIELD_MEDIA_POSITION);
    }

    protected function _initIndexMap()
    {
        // Map Header to index
        $header = $this->_header;
        
        $this->_indexType = array_search(self::FIELD_TYPE, $header);
        $this->_indexConfigurableProductParent = array_search($this->_configurableProductParentField, $header);
        $this->_indexTypeInField = array_search($this->_typeInField, $header);
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
            'size'
        );
        $superAttribMaster = 'color';
        array_unshift($superAttribs, $superAttribMaster);
        
        // Save for Later
        $this->_superAttribs = $superAttribs;
        $this->_superAttributeMaster = $superAttribMaster;
        $this->_typeInField = 'type';
        $this->_superAttributeCompressionField = 'shoe_color_weird';
        $this->_configurableProductParentField = 'style no.';
        $this->_rowsToUnset = array(
            'type' , 
            'style no.'
        ); //'parent_sku' , 
    //'ca_parent_sku'
    

    }

    protected function _initBunchMapping()
    {
        $this->_isUncompressFields['_links_related_sku'] = array_search('_links_related_sku', $this->_header);
        $this->_bunchToConfig = array(
            '_media_image' , 
            '_links_related_sku'
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
        $outFilePrefix = 'CH_may_validSorted_Chunk_';
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
        $fileLocation = 'CH_may_validSorted_Chunk_POST_LAUNCH_2.csv'; //'ch_fullv6.3.csv'; //'validSortedChoppedRdy_sku_colorsFilled.csv';
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

    protected function _isNewConfigurable(array $rowData)
    {
        if ($this->_isConfigProducts) {
            if ($rowData[$this->_indexTypeInField] == 'configurable') {
                $this->_configRow = $rowData;
                return true;
            }
        } else {
            if ($this->_currentConfigurable != $rowData[$this->_indexConfigurableProductParent]) {
                $this->_currentConfigurable = $rowData[$this->_indexConfigurableProductParent];
                return true;
            }
        }
        return false;
    }

    protected function _isNewCompression(array $rowData)
    {
        if ($this->_isCompression) {
            if ($this->_currentCompression != $rowData[$this->_indexSuperAttributeCompressionField]) {
                $this->_currentCompression = $rowData[$this->_indexSuperAttributeCompressionField];
                return true;
            }
        }
        return false;
    }

    public function masterFunction()
    {
        echo 'This Takes a SORTED list as input. Use dos/unix sort command from command line';
        
        // MAIN INIT is in construct
        // INIT Locals
        $buffer = array();
        $superBuffer = array();
        $count = 0;
        $masterAttribSuffixMap = '';
        $superAttributeMaster = $this->_superAttributeMaster;
        
        // $indexConfigurableProductParent = $this->_indexConfigurableProductParent; // $sortfield
        // $indexSuperAttributeCompressionField = $this->_indexSuperAttributeCompressionField; // $attribMapFieldIndex        
        

        // RUN
        while ($rowData = fgetcsv($this->_inFileHandle)) {
            $count ++;
            // $newRowId = $this->_getNewRowId($rowData); //$rowData[$indexConfigurableProductParent];
            // $newCompressionMap =  $this->_getNewRowId($rowData);// $rowData[$indexSuperAttributeCompressionField];
            if ($this->_isNewConfigurable($rowData)) {
                // New Config Item (Or First): Write it out
                $this->_writeToCsv($buffer, $superBuffer);
                $buffer = array();
                $superBuffer = array();
                $this->_bunchedInfo = array();
                if ($this->_isConfigProducts) {
                    continue;
                }
            }
            
            if ($this->_isNewCompression($rowData)) {
                // TODO FIX this for SM
                // WE have a duplicate on the Master Super Attribute OR it is the first one
                $masterAttribSuffixMap = $this->_getNextAttributeSuffix($rowData, $superBuffer, $superAttrib);
                $oldCompressionMap = $newCompressionMap;
            } else {
                $masterAttribSuffixMap = $rowData[$this->_indexSuperAttributeMaster];
            }
            
            foreach ($this->_superAttribs as $superAttrib) {
                $tempSuperName = ($superAttrib === $this->_superAttributeMaster) ? $masterAttribSuffixMap : $rowData[$this->_indexSuperAttributes[$superAttrib]];
                $superBuffer[$masterAttribSuffixMap][$superAttrib][] = array(
                    'SuperName' => $tempSuperName , 
                    'sku' => $rowData[$this->_indexSku]
                );
            }
            
            // Uncompress Data -> TODO extract to method
            foreach ($this->_isUncompressFields as $name => $index) {
                $tempData = explode('|', $rowData[$index]);
                if (! empty($tempData[0])) {
                    $uncompressedData[$rowData[$this->_indexSku]][$name] = $tempData;
                }
            }
            
            // This is only to save for config product.
            foreach ($this->_indexsBunchToConfig as $name => $index) {
                switch ($name) {
                    case '_media_image':
                        $this->_bunchedInfo['_media_image'][$rowData[$this->_indexsBunchToConfig['_media_image']]] = $rowData[$this->_indexSuperAttributeMaster];
                        $rowData[$this->_indexsBunchToConfig['_media_image']] = '';
                        break;
                    default:
                        if (is_array($uncompressedData[$field])) {
                            $this->_bunchedInfo[$name] + $uncompressedData[$rowData[$this->_indexSku]][$name];
                            unset($uncompressedData[$rowData[$this->_indexSku]][$field]);
                        } else {
                            $this->_bunchedInfo[$name][] = $rowData[$index];
                            $rowData[$index] = '';
                        }
                }
            }
            
            $buffer[] = $rowData;
            // === Reset Stuff & Start a New Buffer/SuperBuffer === //
        // $masterAttribSuffixMap = $this->_getNewAttributeSuffix($rowData);
        // $oldRowId = $newRowId;
        // $oldCompressionMap = $newCompressionMap;
        }
        
        // Save Last
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
                if ($this->_isCompression) {
                    $line[$this->_indexSuperAttributeMaster] = $this->_masterAttribOffsetMap[$line[$this->_indexSuperAttributeCompressionField]];
                }
                
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
        $outinfo = array();
        $usedSuperAttribs = array();
        
        // First we need to write the config product out. Slight logic changes
        // We need to use from buffer
        $firstLine = $this->_isConfigProducts ? $this->_configRow : reset($buffer);
        
        // Change the SKU to the oldRowID
        $firstLine[$this->_indexSku] = $firstLine[$this->_indexConfigurableProductParent]; // TODO fix this to use on SM
        

        // Uncompress
        foreach ($this->_isUncompressFields as $name => $index) {
            $tempData = explode('|', $firstLine[$index]);
            if (! empty($tempData[0])) {
                $this->_bunchedInfo[$name] = $tempData;
            }
        }
        
        // This converts the Attribute from the regular name to the Master Attrib Name (Multi => Mulit_01)
        if ($this->_isCompression) {
            $firstLine[$this->_indexSuperAttributeMaster] = $this->_masterAttribOffsetMap[$line[$this->_indexSuperAttributeCompressionField]];
        }
        
        foreach ($this->_unsetIndexes as $unsetIndex) {
            // Unset some rows before printing. They are sanitized before?
            unset($firstLine[$unsetIndex]);
        }
        $firstLine = array_values($firstLine);
        array_push($firstLine, self::TYPE_CONFIGURABLE);
        
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
                        
                        $mediaCount = 0;
                        foreach ($this->_bunchedInfo as $name => $values) {
                            switch ($name) {
                                case '_media_image':
                                    if ($firstLine[array_search('_media_image', $this->_header)]) {
                                        $mediaValue = $firstLine[array_search('_media_image', $this->_header)];
                                        $mediaLable = $buffer[0][$this->_indexSuperAttributeMaster];
                                        array_push($tempArray, $mediaLable);
                                    } else {
                                        end($this->_bunchedInfo['_media_image']);
                                        $mediaValue = key($this->_bunchedInfo['_media_image']);
                                        $mediaLable = array_pop($this->_bunchedInfo['_media_image']);
                                        $mediaCount ++;
                                        $tempArray[array_search('_media_image', $this->_header)] = $mediaValue;
                                        array_push($tempArray, $mediaLable, $mediaCount);
                                    }
                                    
                                    $tempArray[array_search('image', $this->_header)] = $mediaValue;
                                    $tempArray[array_search('small_image', $this->_header)] = $mediaValue;
                                    $tempArray[array_search('thumbnail', $this->_header)] = $mediaValue;
                                    break;
                                default:
                                    $tempArray[array_search($name, $this->_header)] = array_pop($this->_bunchedInfo['_links_related_sku']);
                            }
                        }
                        
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
                        $tempArray = $commaArray + $outInfo;
                        $mediaLable = '';
                        foreach ($this->_bunchedInfo as $name => $values) {
                            switch ($name) {
                                case '_media_image':
                                    end($this->_bunchedInfo['_media_image']);
                                    $mediaValue = key($this->_bunchedInfo['_media_image']);
                                    $mediaLable = array_pop($this->_bunchedInfo['_media_image']);
                                    $mediaCount ++;
                                    $tempArray[array_search('_media_image', $this->_header)] = $mediaValue;
                                    if ($mediaValue) {
                                        $tempArray[array_search('_media_is_disabled', $this->_header)] = 0;
                                        $tempArray[array_search('_media_attribute_id', $this->_header)] = 88; // TODO unhardcode
                                        // TODO add the $mediaCount
                                    }
                                    array_push($tempArray, $mediaLable, $mediaCount);
                                    break;
                                default:
                                    $tempArray[array_search($name, $this->_header)] = array_pop($this->_bunchedInfo['_links_related_sku']);
                            }
                        }
                        
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
