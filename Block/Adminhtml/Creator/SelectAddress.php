<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Block\Adminhtml\Creator;

/**
 * Class Grid
 * @package ITvoice\AsnCreator\Block\Adminhtml\Creator\SelectAddress
 */
class SelectAddress extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_blockGroup = 'ITvoice_AsnCreator';
        $this->_controller = 'adminhtml_creator_selectAddress';
        parent::_construct();
        $this->removeButton('add');
    }
}
