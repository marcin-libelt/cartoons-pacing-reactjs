<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Block\Adminhtml;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Class Editor
 * @package ITvoice\AsnCreator\Block\Adminhtml
 */
class Editor extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Editor constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCloseButton()
    {
        $url = $this->getUrl('itvoice_asn/asn/view', ['id' => $this->getAsn()->getId()]);
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'label' => __('Close Edit View'),
                'class' => 'action-save action-secondary',
                'onclick' => "setLocation('{$url}')",
            ]
        );
        return $button->toHtml();
    }

    /**
     * @return mixed|null
     */
    public function getAsn()
    {
        return $this->registry->registry('current_asn');
    }
}
