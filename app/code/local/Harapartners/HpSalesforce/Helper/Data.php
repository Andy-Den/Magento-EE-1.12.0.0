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
class Harapartners_HpSalesforce_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function initSalesforceConnection()
    {
        $sfBaseDir = dirname(__FILE__) . '/../lib/soapclient/';
        $username = Mage::getStoreConfig('hpsalesforce/conection_config/username');
        $password = Mage::getStoreConfig('hpsalesforce/conection_config/password');
        $securityToken = Mage::getStoreConfig('hpsalesforce/conection_config/security_token');
        if (Mage::getStoreConfig('hpsalesforce/conection_config/is_sandbox')) {
            $wsdlName = 'partner_test.wsdl.xml';
        } else {
            $wsdlName = 'partner.wsdl.xml';
        }
        
        include_once $sfBaseDir . 'SforcePartnerClient.php';
        $mySforceConnection = new SforcePartnerClient();
        $mySforceConnection->createConnection($sfBaseDir . $wsdlName);
        try {
            $mySforceConnection->login($username, $password . $securityToken);
        } catch (SoapFault $sf) {
            Mage::logException($sf);
            return null;
        }
        
        //$this->_makeTestQuery($mySforceConnection);
        return $mySforceConnection;
    }

    protected function _makeTestQuery($mySforceConnection)
    {
        $query = "SELECT Name, name__c from productStub__c";
        
        $response = $mySforceConnection->query($query);
        echo "Results of query '$query'<br/><br/>\n";
        foreach ($response->records as $record) {
            $sRecord = new SObject($record);
            echo $sRecord->Id . ": " . $sRecord->fields->Name . " " . $sRecord->fields->Owner . " " . $sRecord->fields->name__c . "<br/>\n";
        }
        
        $records = array();
        
        $records[0] = new SObject();
        $records[0]->fields = array(
            'FirstName' => 'John' , 
            'LastName' => 'Smiths' , 
            'Phone' => '(510) 555-5555' , 
            'BirthDate' => '1957-01-25' , 
            'Email' => 'js@js.com'
        );
        $records[0]->type = 'Contact';
        
        $records[1] = new SObject();
        $records[1]->fields = array(
            'FirstName' => 'Mary' , 
            'LastName' => 'Joness' , 
            'Phone' => '(510) 486-9969' , 
            'BirthDate' => '1977-01-25' , 
            'Email' => 'mj@mj.com'
        );
        $records[1]->type = 'Contact';
        
        $response = $mySforceConnection->upsert('Email', $records);
        
        $ids = array();
        foreach ($response as $i => $result) {
            echo $records[$i]->fields["FirstName"] . " " . $records[$i]->fields["LastName"] . " " . $records[$i]->fields["Phone"] . " created with id " . $result->id . "<br/>\n";
            array_push($ids, $result->id);
        }
    }
}
