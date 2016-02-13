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
class Harapartners_HpSalesforce_Model_Export_Note
{

    public function createSfNoteObject($sfParentId, $title, $comment)
    {
        $recordNote = new SObject();
        $recordNote->fields = $this->_mapIntoFields($sfParentId, $title, $comment);
        $recordNote->type = 'Note';
        
        return $recordNote;
    }

    protected function _mapIntoFields($sfParentId, $title, $comment)
    {
        $soFields = array();
        
        // Extra Stuff
        $soFields['ParentId'] = $sfParentId;
        $soFields['Title'] = $title;
        $soFields['Body'] = $comment;
        return $soFields;
    }
}




