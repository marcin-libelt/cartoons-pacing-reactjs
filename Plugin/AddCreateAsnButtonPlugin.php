<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Plugin;

/**
 * Class AddCreateAsnButtonPlugin
 * @package ITvoice\AsnCreator\Plugin
 */
class AddCreateAsnButtonPlugin
{
    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\View $view
     */
    public function beforeSetLayout(\Magento\Backend\Block\Widget\Container $view)
    {
        $url = $view->getUrl('itvoice_asn_creator/index/index');
        $view->addButton(
            'create_new_asn',
            [
                'label' => __('Create ASN'),
                'onclick' => "setLocation('{$url}')",
                'class' => 'primary'
            ]
        );
    }
}
