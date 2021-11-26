<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Block\Adminhtml;

/**
 * Class SelectFactory
 * @package ITvoice\AsnCreator\Block\Adminhtml
 */
class SelectFactory extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_blockGroup = 'ITvoice_AsnCreator';
        $this->_controller = 'adminhtml_selectFactory';
        $this->_headerText = __('Select Factory');
        parent::_construct();
        $this->removeButton('add');
    }
}
