<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminCustomerObeserver
 *
 * @author Ea Design
 */
class EaDesign_CustomerGroup_Model_AdminCustomerObeserver
{

    /*
     * Saving the group settings for auto move.
     */
    public function saveTheGroupSetting()
    {
        try
        {
            $customer = Mage::getModel('customer/customer')->load($this->getTheCustomer());
            $customer->setDisableAutoGroupSwitch($this->getDataFromuser());
            $customer->save();
            
        } catch (Exception $e)
        {
            Mage::log('customer setDisableAutoGroupSwitch saving ' . $e->getMessage());
        }
    }
    
    /*
     * Te the data from the controller post - the event ideaa
     */
    public function getDataFromuser()
    {
        $data = Mage::app()->getRequest()->getPost();
        if (isset($data))
        {
            return $data['account']['disable_auto_group_switch'];
        }
    }
    /*
     * Get the current customer to insert in the load - model
     */
    public function getTheCustomer()
    {
        if (Mage::registry('current_customer'))
        {
            return Mage::registry('current_customer')->getId();
        }
    }

}

?>
