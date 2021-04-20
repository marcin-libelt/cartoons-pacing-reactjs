<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Controller\Adminhtml\AddCarton\Products;
/**
 * Class Grid
 * @package ITvoice\AsnCreator\Controller\Adminhtml\AddCarton\Products
 */
class Grid extends \ITvoice\AsnCreator\Controller\Adminhtml\AddCarton\Products
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $this->initAddress();
        $this->initFactory();

        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
