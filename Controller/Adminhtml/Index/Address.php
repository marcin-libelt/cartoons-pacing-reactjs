<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Controller\Adminhtml\Index;

/**
 * Class Address
 * @package ITvoice\AsnCreator\Controller\Adminhtml\Index
 */
class Address extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    /**
     * @var \ITvoice\Client\Model\AddressFactory
     */
    protected $addressFactory;

    /**
     * Creator constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \ITvoice\Client\Model\Client\AddressFactory $addressFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->coreRegistry = $coreRegistry;
        $this->addressFactory = $addressFactory;
        parent::__construct($context);
    }


    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $jsonResponse = $this->jsonFactory->create();
        $addressId = $this->getRequest()->getParam('address_id');

        $address = $this->addressFactory->create()->load($addressId);

        if ($address) {
            $this->coreRegistry->register('selected_address', $address);
            $this->resultLayoutFactory->create()->renderResult($this->getResponse());

            $jsonResponse->setData([
                'html' => $this->getResponse()->getContent(),
                'id' => $address->getId(),
            ]);
        } else {
            $jsonResponse->setData([
                'html' => '',
            ]);
        }

        return $jsonResponse;
    }
}
