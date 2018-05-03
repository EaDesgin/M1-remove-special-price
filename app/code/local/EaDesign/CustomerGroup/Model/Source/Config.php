<?php
/**
 * The system config values
 *
 * @author Ea Design
 */
class EaDesign_CustomerGroup_Model_Source_Config extends Mage_Core_Model_Abstract
{
    /*
     * Addig the optiones array to the config to get the 
     */

    public function toOptionArray()
    {
        //return $this->getTheGroups();
        $data = array(
            array('value' => 0, 'label' => Mage::helper('eadesign_customergroup')->__('Select')),
        );
        return array_merge($data, $this->getTheGroups());
    }

    /*
     * Get the groups except the not loged in
     */

    public function getTheGroups()
    {
        $collection = Mage::getResourceModel('customer/group_collection');
        $optionesArray = $collection->toOptionArray();
        if (is_array($optionesArray))
        {
            unset($optionesArray[0]);
        }
        return $optionesArray;
    }

}
