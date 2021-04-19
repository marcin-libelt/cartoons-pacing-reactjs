<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Block\Adminhtml\Creator\AddCarton;

/**
 * Class Address
 * @package ITvoice\AsnCreator\Block\Adminhtml\Creator\AddCarton
 */
class Address extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_blockGroup = 'ITvoice_AsnCreator';
        $this->_controller = 'adminhtml_creator_addCarton_address';
        $this->_headerText = __('Addresses');
        parent::_construct();
        $this->removeButton('add');
    }
}
