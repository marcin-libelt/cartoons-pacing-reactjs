<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Block\Adminhtml;

/**
 * Class Creator
 * @package ITvoice\AsnCreator\Block\Adminhtml
 */
class Creator extends \Magento\Backend\Block\Template
{
    /**
     * @return false|string
     */
    public function getSelectFactoryButtonConfig()
    {
        $config = [
            'url' => $this->getUrl('itvoice_asn_creator/SelectFactory/Index'),
            'modal_id' => 'select_factory_modal',
            'selected_factory_box' => 'selected_factory',
            'select_address_box' => 'select_address_box',
            'refresh_factory_box_url' => $this->getUrl('itvoice_asn_creator/index/factory'),
        ];
        return json_encode($config);
    }
}
