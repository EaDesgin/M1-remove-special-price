<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class EaDesign_CustomerGroup_Model_Source_Enable extends Mage_Core_Model_Abstract
{

    public function toOptionArray()
    {
        $data = array(
            array('value' => 0, 'label' => Mage::helper('eadesign_customergroup')->__('Disabled')),
            array('value' => 1, 'label' => Mage::helper('eadesign_customergroup')->__('Enabled'))
        );
        return $data; 
    }

}
?>
