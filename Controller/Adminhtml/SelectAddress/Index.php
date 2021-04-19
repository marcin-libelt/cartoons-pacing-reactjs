<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Controller\Adminhtml\SelectAddress;

/**
 * Class Index
 * @package ITvoice\AsnCreator\Controller\Adminhtml\SelectAddress
 */
class Index extends \Magento\Backend\App\Action
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
     * Creator constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \ITvoice\Factory\Model\FactoryRepository $factoryRepository
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->factoryRepository = $factoryRepository;
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($context);
    }


    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $jsonResponse = $this->jsonFactory->create();
        $this->resultLayoutFactory->create()->renderResult($this->getResponse());

        $jsonResponse->setData([
            'html' => $this->getResponse()->getContent()
        ]);

        return $jsonResponse;
    }
}
