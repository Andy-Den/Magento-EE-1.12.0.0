<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of rpro9_rdi_pos
 *
 * @author PMBliss <pmbliss@retaildimensions.com>
 * @copyright (c) 2005-2016 Retail Dimensions Inc.
 * @package Core/Rpro9
 */
class rdi_pos extends rdi_general {

    /**
     * Can convert sids.
     * @return boolean
     */
    static public function can_convert()
    {
        return PHP_INT_SIZE == 8;
    }
	
	static public function isV8($sid)
	{
		return !is_numeric($sid) && strlen($sid) == '16';
	}
	
	static public function isV9($sid)
	{
		return !isV8($sid);
	}
	
	static public function convert_sid($sid)
	{
		if(self::isV8($sid))
		{
			return array('v8'=>$sid,'v9'=>self::StringSIDtoInt64SID($sid));
		}
		else
		{
			return array('v8'=>self::Int64SIDtoStringSID($sid),'v9'=>$sid);
		}
	}
    
    /**
     * Converts v8 sids to v9 sids
     * @param string $sSID
     * @return boolean|int64
     */
    static public function StringSIDtoInt64SID($sSID)
    {
        if (!self::can_convert())
        {
            return false;
        }

        $intSID = 0;

        try
        {
            $ch = "";

            $ch2 = "";

            $a = ord('A');

            for ($i = strlen($sSID) - 1; $i >= 0; $i -= 2)
            {
                $ch = substr($sSID, $i, 1);
                $ch2 = substr($sSID, $i - 1, 1);

                $intSID = ($intSID << 8) + ((ord($ch2)) - $a) * 16 + ((ord($ch)) - $a);
            }
        } catch (Exception $ex) {
            $this->echo_message("Error converting SID:{$sSID} {$ex->getMessage()}");
        }

        return $intSID;
    }
    
    /**
     * Converts V9 SID to V8 SID
     * @param int $iSID
     * @return boolean|string
     */
    static public function Int64SIDtoStringSID($iSID)
    {
		if (!self::can_convert())
        {
            return false;
        }
	
        $sbSID = "";

        $iMASK = 0x00000000000000ff;

        $iByte = 0x0000000000000000;

        $bMask = 0x000f;

        for ($i = 0; $i < 8; $i++, $iSID >>= 8)
        {

            $iByte = ($iSID & $iMASK);

            $lowByte = ($iByte & $bMask);

            $hiByte = ($iByte >> 4);

            $sbSID .= chr($hiByte + ord('A'));

            $sbSID .= chr($lowByte + ord('A'));
        }

        return $sbSID;
    }

}
