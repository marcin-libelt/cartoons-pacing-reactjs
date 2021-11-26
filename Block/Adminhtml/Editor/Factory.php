<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Block\Adminhtml\Editor;

use ITvoice\Asn\Model\Asn;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Class Factory
 * @package ITvoice\AsnCreator\Block\Adminhtml\Editor
 */
class Factory extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * SelectedFactory constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * @return Asn
     */
    public function getAsn()
    {
        return $this->coreRegistry->registry('current_asn');
    }

    /**
     * @return \ITvoice\Factory\Model\Factory|string
     */
    public function getFactory()
    {
        return $this->getAsn()->getFactory(true);
    }
}
