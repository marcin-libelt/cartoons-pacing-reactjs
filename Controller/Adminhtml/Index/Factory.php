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
     * Creator constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        $this->jsonFactory = $jsonFactory;
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
            'html' => 'test',
            'factory_code' => 'test',
        ]);

        return $jsonResponse;
    }
}
