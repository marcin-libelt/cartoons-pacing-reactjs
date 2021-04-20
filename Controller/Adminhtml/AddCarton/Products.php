<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Controller\Adminhtml\AddCarton;

/**
 * Class Products
 * @package ITvoice\AsnCreator\Controller\Adminhtml\AddCarton
 */
class Products extends \Magento\Backend\App\Action
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
     * @var \ITvoice\Factory\Model\FactoryRepository
     */
    protected $factoryRepository;
    /**
     * @var \ITvoice\Client\Model\Client\AddressFactory
     */
    protected $addressFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

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
        \ITvoice\Factory\Model\FactoryRepository $factoryRepository,
        \ITvoice\Client\Model\Client\AddressFactory $addressFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->factoryRepository = $factoryRepository;
        $this->addressFactory = $addressFactory;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     *
     */
    protected function initAddress()
    {
        $addressId = $this->getRequest()->getParam('address_id');
        $address = $this->addressFactory->create()->load($addressId);
        $this->registry->register('current_address', $address);
    }

    /**
     *
     */
    protected function initFactory()
    {
        $factoryId = $this->getRequest()->getParam('factory_id');
        $factory = $this->factoryRepository->getById($factoryId);
        $this->registry->register('current_factory', $factory);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->initAddress();
        $this->initFactory();

        $jsonResponse = $this->jsonFactory->create();
        $this->resultLayoutFactory->create()->renderResult($this->getResponse());

        $jsonResponse->setData([
            'html' => $this->getResponse()->getContent()
        ]);

        return $jsonResponse;
    }
}
