<?php

/*
 * The observer to change the user if all sale are greater then a certen number
 */

/**
 * We get the event and we make the change.
 *
 * @author Ea Design
 */
class EaDesign_CustomerGroup_Model_InvoiceObserver
{
    /*
     * The email templates
     */

    const THE_USER_EMAIL_TEMPLATE = 'eadesigncustomergroup_email_config_email_template_sent_to_customer';
    const THE_ADMIN_EMAIL_TEMPLATE = 'eadesigncustomergroup_email_config_email_template_sent_to_admin';

    /*
     * The current group name
     */

    protected $_nextLevelClientGroupName;

    /*
     * The current group id
     */
    protected $_nextLevelGroupId = false;

    /*
     * The current group name
     */
    protected $_currentClientGroupName;

    /*
     * The current group id
     */
    protected $_currentClientGroupId;
    /*
     * The vurent base grand total in the object
     */
    protected $_currentBaseGrandTotal;
    /*
     * Total invoiced for current client
     */
    public $totalInvoiced;
    /*
     * Total retunred for current client
     */
    public $totalReturned;
    /*
     * The total value actualy bought
     */
    public $totalValue;
    /*
     * it is alowed - boolean
     */
    protected $_isAllowedCahnge = false;
    /*
     * The customer id we need to adjust
     */
    protected $_customer;
    /*
     * Totals actual sales made by user
     */
    protected $_totals = 0;

    /*
     * Invoce var just for invoice to select mail
     */
    protected $_invoice = true;

    /*
     * The change to the user group as requested.
     */

    public function changeCustomerGroupInvoice(Varien_Event_Observer $observer)
    {
        if (Mage::helper('eadesign_customergroup')->isEnable())
        {
            $invoice = $observer->getInvoice();
            $this->_customer = $invoice->getCustomerId();
            $this->_currentBaseGrandTotal = $invoice->getBaseGrandTotal();
            return $this->changeCustomerGroup();
        }
    }

    /*
     * The actual group change
     */

    protected function changeCustomerGroup()
    {
        try
        {

            $customer = $this->getTheCustomerObject();
            $this->_isAllowedCahnge = $customer->getDisableAutoGroupSwitch();
            $this->_currentClientGroupId = $this->getTheCustomerObject()->getGroupId();
            $customer->setGroupId($this->changeGroupRules());
            $weDoNotNeed = Mage::helper('eadesign_customergroup')->checkGroup($this->_nextLevelGroupId, $this->_currentClientGroupId);
            if ($this->_nextLevelGroupId)
            {
                if (!$weDoNotNeed)
                {
                    $customer->save();
                    if ($customer->save())
                    {
                        $this->sendTheMailToUser();
                        $this->sendTheMailToAdmin();
                    }
                }
            }
        } catch (Exception $e)
        {
            Mage::log('customer session moving ' . $e->getMessage());
        }
    }

    /*
     * Getting the orders data information to check the credit memo values
     */

    public function getTotalsForUser()
    {
        $salesOrder = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToSelect('total_invoiced')
                ->addAttributeToSelect('total_refunded')
                ->addAttributeToSelect('customer_id')
                ->addAttributeToFilter('state', array('neq' => Mage_Sales_Model_Order::STATE_CLOSED))
                ->addAttributeToFilter('customer_id', $this->_customer);
        foreach ($salesOrder as $order)
        {
            $this->totalInvoiced += $order->getTotalInvoiced();
            $this->totalReturned += $order->getTotalRefunded();
        }
        $this->totalValue = $this->totalInvoiced + $this->_currentBaseGrandTotal - $this->totalReturned;
    }

    /*
     * Change group based on rules available.
     */

    public function changeGroupRules()
    {
        if (!$this->_isAllowedCahnge)
        {
            $this->getTotalsForUser();
            $helper = Mage::helper('eadesign_customergroup')->checkTheValue($this->totalValue);

            if (!$helper->getMin())
            {
                if (!$this->_invoice)
                {
                    $this->_nextLevelGroupId = $helper->getSmallestGroupId();
                    $this->_nextLevelClientGroupName = $helper->getTheGroupName($this->_nextLevelGroupId);
                    return $this->_nextLevelGroupId;
                }
                return null;
            }
            if (!$helper->getMax())
            {
                if ($this->totalValue >= $helper->getMin())
                {
                    $this->_nextLevelGroupId = $helper->groupId();
                    $this->_nextLevelClientGroupName = $helper->getTheGroupName($this->_nextLevelGroupId);
                    return $this->_nextLevelGroupId;
                }
                return null;
            }

            if ($helper->getMin() && $helper->getMax())
            {
                if ($this->totalValue >= $helper->getMin() && $this->totalValue < $helper->getMax())
                {

                    $this->_nextLevelGroupId = $helper->groupId();
                    $this->_nextLevelClientGroupName = $helper->getTheGroupName($this->_nextLevelGroupId);
                    return $this->_nextLevelGroupId;
                }
                return null;
            }
        }
    }

    /*
     * Sending the email to users and the admin - new need to move the helper soon
     */

    public function sendTheMailToUser()
    {
        $translateInline = Mage::getSingleton('core/translate');
        $translateInline->setTranslateInline(false);

        if ($this->_invoice == 1)
        {
            $template = self::THE_USER_EMAIL_TEMPLATE;
        }
        if ($this->_invoice == 0)
        {
            $template = self::THE_USER_EMAIL_TEMPLATE;
        }

        $mailTmeplate = Mage::getModel('core/email_template');
        $senderName = Mage::getStoreConfig('trans_email/ident_support/name');
        $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');
        $sender = array('name' => $senderName, 'email' => $senderEmail);
        $recepientName = $this->getTheCustomerObject()->getName();
        $recepientEmail = $this->getTheCustomerObject()->getEmail();
        $store = Mage::app()->getStore()->getId();
        $vars = array(
            'customerName' => $recepientName,
            'customerNewGroup' => $this->_nextLevelClientGroupName
        );
        $processedTemplate = $mailTmeplate->getProcessedTemplate($vars);
        $mailTmeplate->sendTransactional($template, $sender, $recepientEmail, $recepientName, $processedTemplate, $store);

        $translateInline->setTranslateInline(true);
    }

    /*
     * Send e-mail to admin - this realy not the corent way - need to change on new versions
     */

    public function sendTheMailToAdmin()
    {
        $translateInline = Mage::getSingleton('core/translate');
        $translateInline->setTranslateInline(false);
        $template = self::THE_ADMIN_EMAIL_TEMPLATE;
        $mailTmeplate = Mage::getModel('core/email_template');
        $senderName = Mage::getStoreConfig('trans_email/ident_support/name');
        $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');
        $sender = array('name' => $senderName, 'email' => $senderEmail);

        $recepientEmail = Mage::getStoreConfig('trans_email/ident_general/email');
        $recepientName = Mage::getStoreConfig('trans_email/ident_general/name');

        $store = Mage::app()->getStore()->getId();
        $vars = array(
            'customerName' => $recepientName,
            'customerNewGroup' => $this->_nextLevelClientGroupName
        );
        $processedTemplate = $mailTmeplate->getProcessedTemplate($vars);
        $mailTmeplate->sendTransactional($template, $sender, $recepientEmail, $recepientName, $processedTemplate, $store);

        $translateInline->setTranslateInline(true);
    }

    /*
     * Get the customer object
     */

    public function getTheCustomerObject()
    {
        try
        {
            return Mage::getModel('customer/customer')->load($this->_customer);
        } catch (Exception $e)
        {
            Mage::log('customer session moving ' . $e->getMessage());
        }
    }

}

?>
