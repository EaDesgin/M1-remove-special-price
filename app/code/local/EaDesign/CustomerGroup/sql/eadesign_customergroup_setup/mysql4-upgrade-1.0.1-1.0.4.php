<?php

$installer = $this;

$installer->getConnection()->addColumn($installer->getTable('customer/entity'), 'disable_auto_group_switch', array(
    'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
    'unsigned' => true,
    'nullable' => false,
    'default' => '0',
    'comment' => 'Disable automatic group change system'
));
