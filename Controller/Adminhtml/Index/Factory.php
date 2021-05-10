<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Controller\Adminhtml\Index;

/**
 * Class Factory
 * @package ITvoice\AsnCreator\Controller\Adminhtml\Index
 */
class Factory extends \Magento\Backend\App\Action
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
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    /**
     * @var \ITvoice\AsnCreator\Model\Creator
     */
    protected $creator;

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
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->factoryRepository = $factoryRepository;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }


    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $jsonResponse = $this->jsonFactory->create();
        $factoryId = $this->getRequest()->getParam('factory_id');
        $factory = $this->factoryRepository->getById($factoryId);

        if ($factory) {
            $this->coreRegistry->register('selected_factory', $factory);
            $this->resultLayoutFactory->create()->renderResult($this->getResponse());
            $jsonResponse->setData([
                'html' => $this->getResponse()->getContent(),
                'id' => $factory->getId(),
            ]);
        } else {
            $jsonResponse->setData([
                'html' => '',
            ]);
        }

        return $jsonResponse;
    }
}
