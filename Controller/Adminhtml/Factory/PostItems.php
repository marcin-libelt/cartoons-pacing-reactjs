<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Controller\Adminhtml\Factory;

use Magento\Backend\App\Action\Context;

/**
 * Class GetItems
 * @package ITvoice\AsnCreator\Controller\Adminhtml\Factory
 */
class PostItems extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * GetItems constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory

    ) {
        $this->jsonFactory = $jsonFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {

/*        if (!$this->getRequest()->isAjax()) {
            $this->_forward('denied');
        }*/

        $jsonResponse = $this->jsonFactory->create();
        $data = [
            'status' => 'OK'
        ];
        $jsonResponse->setData($data);
        return $jsonResponse;
    }
}
