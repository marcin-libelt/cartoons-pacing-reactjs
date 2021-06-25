<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Plugin;

class AddEditAsnButtonPlugin
{
    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\View $view
     */
    public function beforeSetLayout(\ITvoice\Asn\Block\Adminhtml\Asn\View $view)
    {
        if ($view->getAsn()->canEdit()) {
            $message = __('Are You Sure?');
            $url = $view->getUrl(
                'itvoice_asn_creator/asn/edit',
                [
                    'asn_id' => $view->getAsn()->getId(),
                ]
            );
            $view->addButton(
                'edit_asn',
                [
                    'label' => __('Edit'),
                    'onclick' => "confirmSetLocation('{$message}', '{$url}')"
                ]
            );
        }
    }
}
