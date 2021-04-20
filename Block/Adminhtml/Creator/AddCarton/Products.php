<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Block\Adminhtml\Creator\AddCarton;

/**
 * Class Products
 * @package ITvoice\AsnCreator\Block\Adminhtml\Creator\AddCarton
 */
class Products extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_blockGroup = 'ITvoice_AsnCreator';
        $this->_controller = 'adminhtml_creator_addCarton_products';
        $this->_headerText = __('Products');
        parent::_construct();
        $this->removeButton('add');
    }
}
