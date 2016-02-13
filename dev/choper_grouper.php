<?php

echo 'This Takes a SORTED list as input. Use dos/unix sort command from command line';

// INIT Attribs
$superAttribs = array ('shoe_size', 'width' );
$superAttribMaster = 'shoe_color';
array_unshift ( $superAttribs, $superAttribMaster );
$superAttribMapField = 'shoe_color_weird';
$sortFieldName = 'parent_sku';
$skuFieldName = 'sku';
$typeField = '_type';
$superProductField = '_super_products_sku';
$superAttributeCodeField = '_super_attribute_code';
$superAttributeOptionField = '_super_attribute_option';
$typeSimple = 'simple';
$typeConfig = 'configurable';
$unsetRows = array ('parent_sku', 'ca_parent_sku' ); // These rows will not be printed.


// INIT Other Relevetn Attribs
// 14680064 ->14 mB // 7340032 -> 7 mB // 33554432 -> 32mb // 2097152 -> 2 mb // 4194304 -> 4mb // 524288 -> is .5mb // 209715 -> .2mb
// 8000000; //8388608 is 8meg ; 10485760 is 10meg // size 750000 
$checkFileSize = true;
$chunkSizeBytes = 2097152;
$fileLocation = 'validSortedChoppedRdy_sku_colorsFilled.csv';
$outFilePrefix = 'validSorted_Chunk_';
$outFileSuffix = '.csv';
$inFileHandle = fopen ( $fileLocation, 'r' );
$chunkCount = 0;
$outFileLocation = $outFilePrefix . $chunkCount . $outFileSuffix;
$header = fgetcsv ( $inFileHandle );

// Map Header to index
$typeFieldIndex = array_search ( $typeField, $header );
$sortfieldIndex = array_search ( $sortFieldName, $header );
$attribMasterIndex = array_search ( $superAttribMaster, $header );
$attribMapFieldIndex = array_search ( $superAttribMapField, $header );
$skuIndex = array_search ( $skuFieldName, $header );
foreach ( $superAttribs as $superAttrib ) {
	$tempIndex = array_search ( $superAttrib, $header );
	if ($tempIndex !== false) {
		// It exists so add it to index
		$superAttribsIndexs [$superAttrib] = array_search ( $superAttrib, $header );
	} else {
		// So it does not exist. Print warning
		echo 'Missing Super Attribute Column:' . $superAttrib;
	}
}

// Create Comma count
// Create the leading comas for the config rows.
foreach ( $header as $number ) {
	$commaArray [] = '';
}
// Remove the unset comma, header
foreach ( $unsetRows as $name ) {
	$i = array_search ( $name, $header );
	if ($i !== false) {
		// Check that we got an index
		$unsetIndexs [] = $i;
		unset ( $header [$i] );
		array_pop ( $commaArray );
	}
}

// Add one back to header for type
$commaArray [] = '';

// Now we can add the Type & Super colums to the header
array_push ( $header, $typeField, $superProductField, $superAttributeCodeField, $superAttributeOptionField );
$outBuffer = array ();

// INIT Locals
$oldRowId = '';
$newRowId;
$oldIdMap = '';
$newIdMap;
$count = 0;
$masterAtrribOffest = 0;
$masterAttribOffsetMap = array ();

// RUN
while ( $rowData = fgetcsv ( $inFileHandle ) ) {
	$count ++;
	$newRowId = $rowData [$sortfieldIndex];
	$newIdMap = $rowData [$attribMapFieldIndex];
	if ($oldRowId == $newRowId) {
		if ($oldIdMap != $newIdMap) {
			// WE have a duplicate on the Master Super Attribute OR it is the first one
			$masterAttribSuffixMap = $masterAtrribOffest < 10 ? $rowData [$attribMasterIndex] . '_0' . $masterAtrribOffest : $rowData [$attribMasterIndex] . '_' . $masterAtrribOffest;
			while ( isset ( $superBuffer [$masterAttribSuffixMap] [$superAttrib] ) ) {
				// This should get a valid slot For mapped value
				$masterAtrribOffest ++;
				$masterAttribSuffixMap = $masterAtrribOffest < 10 ? $rowData [$attribMasterIndex] . '_0' . $masterAtrribOffest : $rowData [$attribMasterIndex] . '_' . $masterAtrribOffest;
			}
			$masterAttribOffsetMap [$rowData [$attribMapFieldIndex]] = $masterAttribSuffixMap;
			$oldIdMap = $newIdMap;
		}
		$buffer [] = $rowData;
		foreach ( $superAttribs as $superAttrib ) {
			$tempSuperName = $superAttrib === $superAttribMaster ? $masterAttribSuffixMap : $rowData [$superAttribsIndexs [$superAttrib]];
			$superBuffer [$masterAttribSuffixMap] [$superAttrib] [] = array ('SuperName' => $tempSuperName, 'sku' => $rowData [$skuIndex] );
		}
	} else {
		// This is the first of $oldRowId == $newRowId. This is a new config Item?!
		// Write it out
		if ($buffer) {
			// We have what to write !
			// Lets check the size of the output file; 10485760 should be 10 meg
			clearstatcache ();
			if (file_exists ( $outFileLocation )) {
				if ($checkFileSize) {
					// We are checking file size : Else we will just write to the same file
					if (filesize ( $outFileLocation ) > $chunkSizeBytes) {
						// File is too big (For now)
						fclose ( $outFileHandle );
						
						// Incriment chunk counter AND Open new one
						$chunkCount ++;
						$outFileLocation = $outFilePrefix . $chunkCount . $outFileSuffix;
						$outFileHandle = fopen ( $outFilePrefix . $chunkCount . $outFileSuffix, 'w' );
						fputcsv ( $outFileHandle, $header );
					}
				}
			} else {
				// File does not exist
				$outFileHandle = fopen ( $outFilePrefix . $chunkCount . $outFileSuffix, 'w' );
				fputcsv ( $outFileHandle, $header );
			}
			
			// Write to the file
			foreach ( $buffer as $line ) {
				// This converts the Attribute from the regular name to the Master Attrib Name (Multi => Mulit_01)
				$line [$attribMasterIndex] = $masterAttribOffsetMap [$line [$attribMapFieldIndex]];
				
				foreach ( $unsetIndexs as $unsetIndex ) {
					// Unset some rows before printing. They are sanitized before?
					unset ( $line [$unsetIndex] );
				}
				
				// Add the type to the row & write it out
				array_push ( $line, $typeSimple, '', '', '' );
				fputcsv ( $outFileHandle, $line );
			}
			
			// Write the super stuff
			// First we need to write the config product out. This is done here to reduce loop complexity. It shouldnt go past the first instance.
			// We need to use from buffer
			$firstLine = reset ( $buffer );
			
			// Change the SKU to the oldRowID
			$firstLine [$skuIndex] = $oldRowId;
			
			// This converts the Attribute from the regular name to the Master Attrib Name (Multi => Mulit_01)
			$firstLine [$attribMasterIndex] = $masterAttribOffsetMap [$firstLine [$attribMapFieldIndex]];
			$usedSuperAttribs = array ();
			
			foreach ( $unsetIndexs as $unsetIndex ) {
				// Unset some rows before printing. They are sanitized before?
				unset ( $firstLine [$unsetIndex] );
			}
			
			array_push ( $firstLine, $typeConfig );
			unset ( $outinfo );
			
			foreach ( $superBuffer as $masterAttribName => $subAttribs ) {
				// Find one row with more then one entry
				foreach ( $subAttribs as $subAttribName => $Attribs ) {
					if (count ( $Attribs ) > 1) {
						foreach ( $Attribs as $attribName => $attribSku ) {
							// Take one, unset it, write it and break
							// If we dont find one then the super writing should not find any either.
							$outInfo = array ($superProductField => $attribSku ['sku'], $superAttributeCodeField => $subAttribName, $superAttributeOptionField => $attribSku ['SuperName'] );
							$tempArray = $firstLine + $outInfo;
							unset ( $superBuffer [$masterAttribName] [$subAttribName] [$attribName] ); // Need to reach back to the original Array.
							if (! isset ( $usedSuperAttribs [$subAttribName] [$attribSku ['SuperName']] ) || $subAttribName === $superAttribMaster) {
								fputcsv ( $outFileHandle, $tempArray );
							}
							$usedSuperAttribs [$subAttribName] [$attribSku ['SuperName']] = $attribSku ['sku']; // save used key value pairs
							break (3);
						}
					}
				}
			}
			
			// This is for writing the rest
			//TODO handle config that has only the Master Attrib in multiples
			// This supports 3 levels of config product => TODO add more layers !!!!
			foreach ( $superBuffer as $masterAttribName => $subAttribs ) {
				foreach ( $subAttribs as $subAttribName => $Attribs ) {
					if (count ( $Attribs ) > 1) {
						// Do twice becasue its two layers deep. This is the problem from data structure ish?
						foreach ( $Attribs as $attribName => $attribSku ) {
							$outInfo = array ($superProductField => $attribSku ['sku'], $superAttributeCodeField => $subAttribName, $superAttributeOptionField => $attribSku ['SuperName'] );
							$tempArray = $commaArray + $outInfo; // Arrays Copy
							if (! isset ( $usedSuperAttribs [$subAttribName] [$attribSku ['SuperName']] ) || $subAttribName === $superAttribMaster) {
								fputcsv ( $outFileHandle, $tempArray );
							}
							$usedSuperAttribs [$subAttribName] [$attribSku ['SuperName']] = $attribSku ['sku']; // save used key value pairs
						}
						//				if (count ( $superBuffer ) > 1) {
					//					// There is more then one Master, so write it out also
					//					foreach ( $Attribs as $attribName => $attribSku ) {
					//						$outInfo = array ($superProductField => $attribSku ['sku'], $superAttributeCodeField => $superAttribMaster, $superAttributeOptionField => $masterAttribName );
					//						$tempArray = $commaArray + $outInfo; // Arrays Copy
					//						fputcsv ( $outFileHandle, $tempArray );
					//					}
					//				}
					}
				}
			}
		}
		
		// === Reset Stuff & Start a New Buffer/SuperBuffer === // 
		$masterAtrribOffest = 0;
		$masterAttribSuffixMap = $masterAtrribOffest < 10 ? $rowData [$attribMasterIndex] . '_0' . $masterAtrribOffest : $rowData [$attribMasterIndex] . '_' . $masterAtrribOffest;
		$masterAttribOffsetMap [$rowData [$attribMapFieldIndex]] = $masterAttribSuffixMap;
		$oldRowId = $newRowId;
		$oldIdMap = $newIdMap;
		
		// This adds the first row and resets the fields
		$buffer = array ($rowData );
		$superBuffer = array ();
		foreach ( $superAttribs as $superAttrib ) {
			$tempSuperName = $superAttrib === $superAttribMaster ? $masterAttribSuffixMap : $rowData [$superAttribsIndexs [$superAttrib]];
			$superBuffer [$masterAttribSuffixMap] [$superAttrib] [] = array ('SuperName' => $tempSuperName, 'sku' => $rowData [$skuIndex] );
		}
	
	}
}

// SO FIX THIS BADDDD
// This is the first of $oldRowId == $newRowId. This is a new config Item?!
// Write it out
if ($buffer) {
	// We have what to write !
	// Lets check the size of the output file; 10485760 should be 10 meg
	clearstatcache ();
	if (file_exists ( $outFileLocation )) {
		if ($checkFileSize) {
			// We are checking file size : Else we will just write to the same file
			if (filesize ( $outFileLocation ) > $chunkSizeBytes) {
				// File is too big (For now)
				fclose ( $outFileHandle );
				
				// Incriment chunk counter AND Open new one
				$chunkCount ++;
				$outFileLocation = $outFilePrefix . $chunkCount . $outFileSuffix;
				$outFileHandle = fopen ( $outFilePrefix . $chunkCount . $outFileSuffix, 'w' );
				fputcsv ( $outFileHandle, $header );
			}
		}
	} else {
		// File does not exist
		$outFileHandle = fopen ( $outFilePrefix . $chunkCount . $outFileSuffix, 'w' );
		fputcsv ( $outFileHandle, $header );
	}
	
	// Write to the file
	foreach ( $buffer as $line ) {
		// This converts the Attribute from the regular name to the Master Attrib Name (Multi => Mulit_01)
		$line [$attribMasterIndex] = $masterAttribOffsetMap [$line [$attribMapFieldIndex]];
		
		foreach ( $unsetIndexs as $unsetIndex ) {
			// Unset some rows before printing. They are sanitized before?
			unset ( $line [$unsetIndex] );
		}
		
		// Add the type to the row & write it out
		array_push ( $line, $typeSimple, ',', ',', ',' );
		fputcsv ( $outFileHandle, $line );
	}
	
	// Write the super stuff
	// First we need to write the config product out. This is done here to reduce loop complexity. It shouldnt go past the first instance.
	// We need to use from buffer
	$firstLine = reset ( $buffer );
	
	// Change the SKU to the oldRowID
	$firstLine [$skuIndex] = $oldRowId;
	
	// This converts the Attribute from the regular name to the Master Attrib Name (Multi => Mulit_01)
	$firstLine [$attribMasterIndex] = $masterAttribOffsetMap [$firstLine [$attribMapFieldIndex]];
	$usedSuperAttribs = array ();
	
	foreach ( $unsetIndexs as $unsetIndex ) {
		// Unset some rows before printing. They are sanitized before?
		unset ( $firstLine [$unsetIndex] );
	}
	
	array_push ( $firstLine, $typeConfig );
	unset ( $outinfo );
	
	foreach ( $superBuffer as $masterAttribName => $subAttribs ) {
		// Find one row with more then one entry
		foreach ( $subAttribs as $subAttribName => $Attribs ) {
			if (count ( $Attribs ) > 1) {
				foreach ( $Attribs as $attribName => $attribSku ) {
					// Take one, unset it, write it and break
					// If we dont find one then the super writing should not find any either.
					$outInfo = array ($superProductField => $attribSku ['sku'], $superAttributeCodeField => $subAttribName, $superAttributeOptionField => $attribSku ['SuperName'] );
					$tempArray = $firstLine + $outInfo;
					unset ( $superBuffer [$masterAttribName] [$subAttribName] [$attribName] ); // Need to reach back to the original Array.
					if (! isset ( $usedSuperAttribs [$subAttribName] [$attribSku ['SuperName']] ) || $subAttribName === $superAttribMaster) {
						fputcsv ( $outFileHandle, $tempArray );
					}
					$usedSuperAttribs [$subAttribName] [$attribSku ['SuperName']] = $attribSku ['sku']; // save used key value pairs
					break (3);
				}
			}
		}
	}
	
	// This is for writing the rest
	//TODO handle config that has only the Master Attrib in multiples
	// This supports 3 levels of config product => TODO add more layers !!!!
	foreach ( $superBuffer as $masterAttribName => $subAttribs ) {
		foreach ( $subAttribs as $subAttribName => $Attribs ) {
			if (count ( $Attribs ) > 1) {
				// Do twice becasue its two layers deep. This is the problem from data structure ish?
				foreach ( $Attribs as $attribName => $attribSku ) {
					$outInfo = array ($superProductField => $attribSku ['sku'], $superAttributeCodeField => $subAttribName, $superAttributeOptionField => $attribSku ['SuperName'] );
					$tempArray = $commaArray + $outInfo; // Arrays Copy
					if (! isset ( $usedSuperAttribs [$subAttribName] [$attribSku ['SuperName']] ) || $subAttribName === $superAttribMaster) {
						fputcsv ( $outFileHandle, $tempArray );
					}
					$usedSuperAttribs [$subAttribName] [$attribSku ['SuperName']] = $attribSku ['sku']; // save used key value pairs
				}
				//				if (count ( $superBuffer ) > 1) {
			//					// There is more then one Master, so write it out also
			//					foreach ( $Attribs as $attribName => $attribSku ) {
			//						$outInfo = array ($superProductField => $attribSku ['sku'], $superAttributeCodeField => $superAttribMaster, $superAttributeOptionField => $masterAttribName );
			//						$tempArray = $commaArray + $outInfo; // Arrays Copy
			//						fputcsv ( $outFileHandle, $tempArray );
			//					}
			//				}
			}
		}
	}
}