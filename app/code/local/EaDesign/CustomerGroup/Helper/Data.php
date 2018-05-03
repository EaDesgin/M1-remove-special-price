<?php

/**
 * The customer auto group system
 * @author Ea Design
 */
class EaDesign_CustomerGroup_Helper_Data extends Mage_Core_Helper_Abstract
{
    /*
     * The system config options
     */

    protected $optiones = array();
    /*
     * Create the group values array
     * 
     */
    protected $_theTotals = 0;
    protected $_topGroupId;
    protected $_bottomGroupId;

    public function isEnable()
    {
        $option = Mage::getStoreConfig('customergroupstwitch_optiones/autenable/eadesign_autoge');
        if ($option !== '')
        {
            return $option;
        }
        return false;
    }

    public function getTheSettings()
    {
        $this->optiones = Mage::getStoreConfig('customergroupstwitch_optiones/auttog');
        if (isset($this->optiones))
        {
            return $this->optiones;
        }
        return false;
    }

    /*
     * Adding validations and processing the saved setings
     */

    public function processSettings()
    {
        $res = array();
        foreach ($this->getTheSettings() as $k => $v)
        {
            $res[] = abs($v);
        }
        $res = array_chunk($res, 2);
        foreach ($res as $result)
        {
            if (!in_array('0', $result))
            {
                $resss[] = $result;
            }
        }
        $reAssoc = array();
        $i = 0;
        foreach ($resss as $value)
        {
            foreach ($value as $val)
            {
                $i++;
                if ($i != 0 && $i % 2 == 0)
                {
                    $sd = $val;
                } else
                {
                    $ss = $val;
                }
            }
            $reAssoc[$ss] = $sd;
        }
        asort($reAssoc);
        return $reAssoc;
    }

    /*
     * Add the total bougth the user has done and start the processing
     * @number the total sale done by users
     * @return obj
     */

    public function checkTheValue($number)
    {
        $this->_theTotals = $number;
        return $this;
    }

    /*
     * Get the next user group (for check)
     */

    public function getBottomGroupId()
    {
        $bottomGroup = array_search($this->getMin(), $this->processSettings());
        $this->_bottomGroupId = $bottomGroup;
        return $this->_bottomGroupId;
    }

    public function getTopGroupId()
    {
        $topGroup = array_search($this->getMax(), $this->processSettings());
        $this->_topGroupId = $topGroup;
        return $this->_topGroupId;
    }

    public function groupId()
    {
        if ($this->getBottomGroupId())
        {
            return $this->_bottomGroupId;
        }
        if ($this->getTopGroupId())
        {
            return $this->_topGroupId;
        }
        return false;
    }

    /*
     * Get the next bought step value
     */

    public function getMax()
    {
        return $this->_getMax();
    }

    /*
     * Get the prev bought step value
     */

    public function getMin()
    {
        return $this->_getMin();
    }

    private function _getMax()
    {
        $array = $this->processSettings();
        sort($array);
        foreach ($array as $a)
        {
            if ($a >= $this->_theTotals)
                return $a;
        }
        return false;
    }

    private function _getMin()
    {
        $array = $this->processSettings();
        rsort($array);
        foreach ($array as $a)
        {
            if ($a < $this->_theTotals)
                return $a;
        }
        return false;
    }

    public function getTheGroupName($group)
    {
        $group = Mage::getModel('customer/group')->load($group);
        return $group->getCustomerGroupCode();
    }

    public function getSmallestGroupId()
    {
        $group = Mage::getModel('customer/group')->getCollection();
        $ids = $group->addFieldToSelect('customer_group_id')->getData();
        $id = array_keys(array_values($ids));
        sort($id);
        return $id[1];
    }

    public function checkGroup($min, $max)
    {
        if ($min == $max)
        {
            return true;
        }
        return false;
    }

}
