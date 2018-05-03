<?php

$installer = $this;

$disableAGCAttributeCode = 'disable_auto_group_switch';

$installer->addAttribute('customer', $disableAGCAttributeCode, array(
    'type'      => 'static',
    'label'     => 'Disable Auto Group Switch',
    'input'     => 'boolean',
    'backend'   => 'customer/attribute_backend_data_boolean',
    'position'  => 29,
    'required'  => false
));

$disableAGCAttribute = Mage::getSingleton('eav/config')
    ->getAttribute('customer', $disableAGCAttributeCode);
$disableAGCAttribute->setData('used_in_forms', array(
    'adminhtml_customer'
));
$disableAGCAttribute->save();
