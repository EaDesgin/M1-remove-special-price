<?php

/*
 * The observer to change the user if all sale are greater then a certen number
 */

/**
 * We get the event and we make the change.
 *
 * @author Ea Design
 */
class EaDesign_CustomerGroup_Model_CreditMemoObserver extends EaDesign_CustomerGroup_Model_InvoiceObserver
{
    /*
     * Trigger the changes in the creditmemo area
     */

    public function changeCustomerGroupCreditMemo(Varien_Event_Observer $observer)
    {
        if (Mage::helper('eadesign_customergroup')->isEnable())
        {
            $creditMemo = $observer->getCreditmemo();
            $this->_currentBaseGrandTotal = $creditMemo->getBaseGrandTotal();
            $this->_customer = $creditMemo->getCustomerId();
            $this->_invoice = false;
            return $this->changeCustomerGroup();
        }
    }

    /*
     * Getting the orders data information to check the credit memo values
     */

    public function getTotalsForUser()
    {
        parent::getTotalsForUser();
        $this->totalValue = $this->totalInvoiced - $this->_currentBaseGrandTotal - $this->totalReturned;
    }

}
