<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Block\Adminhtml\Creator\SelectAddress\Grid\Renderer;

/**
 * Class SelectButton
 * @package ITvoice\AsnCreator\Block\Adminhtml\Creator\SelectFactory\Grid\Renderer
 */
class SelectButton extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param DataObject $row
     * @return string|void
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget::class
        )->getButtonHtml(
            'Select',
            'window.selectedAddressId = "' . $row->getId() . '"; window.selectAddressModal.modal(\'closeModal\')',
            '',
            ''
        );
    }
}
