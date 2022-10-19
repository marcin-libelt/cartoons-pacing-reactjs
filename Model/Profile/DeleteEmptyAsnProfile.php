<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Model\Profile;

use ITvoice\Asn\Model\Asn;
use ITvoice\Asn\Model\AsnFactory;

/**
 * Class DeleteEmptyAsnProfile
 * @package ITvoice\AsnCreator\Model\Profile
 */
class DeleteEmptyAsnProfile extends \Alekseon\Dataflows\Model\Profile implements \Alekseon\Dataflows\Model\ProfileInterface
{
    /**
     * @var AsnFactory
     */
    protected $asnFactory;

    /**
     * DeleteEmptyAsnProfile constructor.
     * @param \Alekseon\Dataflows\Model\Profile\DataReaderFactory $dataReaderFactory
     */
    public function __construct(
        \Alekseon\Dataflows\Model\Profile\DataReaderFactory $dataReaderFactory,
        AsnFactory $asnFactory
    )
    {
        $this->asnFactory = $asnFactory;
        parent::__construct($dataReaderFactory);
    }

    /**
     *
     */
    public function execute()
    {
        $olderThan  = (int) $this->getParam('older_than');
        if ($olderThan > 0) {
            $to = date('Y-m-d H:i:s', strtotime('-' . $olderThan .  ' hours'));
            $asnCollection = $this->asnFactory->create()->getCollection()
                ->addFieldToFilter('status', Asn::STATUS_EMPTY)
                ->addFieldToFilter('created_at', ['lt' => $to]);

            foreach ($asnCollection as $asn) {
                $this->addInfoLog('Deleting empty ASN #' . $asn->getAsnNumber());
                $asn->delete();
            }
        }
    }

    /**
     * @return \string[][]
     */
    public function getParametersFormConfig()
    {
        return [
            'older_than' => [
                'label' => 'Remove older than X hours',
            ],
        ];
    }
}
