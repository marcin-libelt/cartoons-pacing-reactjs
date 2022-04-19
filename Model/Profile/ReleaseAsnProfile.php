<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Model\Profile;

use ITvoice\Asn\Model\Asn;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ReleaseAnsProfile
 * @package ITvoice\AsnCreator\Model\Profile
 */
class ReleaseAsnProfile extends \Alekseon\Dataflows\Model\Profile implements \Alekseon\Dataflows\Model\ProfileInterface
{
    /**
     * @var \ITvoice\Asn\Model\AsnFactory
     */
    protected $asnFactory;
    /**
     * @var \ITvoice\Asn\Model\AsnCsv
     */
    protected $asnCsv;
    /**
     * @var \ITvoice\Ftp\Model\Connection
     */
    protected $ftpConnection;
    /**
     * @var
     */
    protected $asnReleasedCounter = 0;

    /**
     * ReleaseAsnProfile constructor.
     * @param \Alekseon\Dataflows\Model\Profile\DataReaderFactory $dataReaderFactory
     * @param \ITvoice\Asn\Model\AsnFactory $asnFactory
     * @param \ITvoice\AsnCreator\Model\AsnCsv $asnCsv
     * @param \ITvoice\Ftp\Model\ConnectionFactory $ftpConnectionFactory
     */
    public function __construct(
        \Alekseon\Dataflows\Model\Profile\DataReaderFactory $dataReaderFactory,
        \ITvoice\Asn\Model\AsnFactory $asnFactory,
        \ITvoice\AsnCreator\Model\AsnCsv $asnCsv,
        \ITvoice\Ftp\Model\ConnectionFactory $ftpConnectionFactory
    ) {
        $this->asnFactory = $asnFactory;
        $this->asnCsv = $asnCsv;
        $this->ftpConnection = $ftpConnectionFactory->create();
        parent::__construct($dataReaderFactory);
    }

    /**
     *
     */
    protected function getAsnCollectionToRelease()
    {
        $collection = $this->asnFactory->create()->getCollection();
        $collection->addFieldToFilter('type', Asn::ASN_TYPE_INTERNAL);
        $collection->addFieldToFilter('release_status', Asn::RELEASE_STATUS_READY_TO_RELEASE);
        return $collection;
    }

    /**
     * @return bool
     */
    public function canBeExecuted()
    {
        $collectionToRelease = $this->getAsnCollectionToRelease();
        if (!$collectionToRelease->getSize()) {
            return false;
        }
        return true;
    }

    /**
     *
     */
    public function execute()
    {
        $asnCollection = $this->getAsnCollectionToRelease();
        if ($asnCollection->count() > 0) {
            try {
                $this->exportAsn($asnCollection);
                $this->setResult($this->asnReleasedCounter . ' of ASNs has been released.');
            } catch (\Exception $e) {
                $this->addWarningLog($e->getMessage());
                $this->setResult($e->getMessage());
            }
        }
    }

    /**
     * @param $asn
     */
    protected function exportAsn($asnCollection)
    {
        $csvContent = $this->asnCsv->getCsv($asnCollection);

        $dirPaths = [
            $this->getParam('dir_path_1'),
            $this->getParam('dir_path_2'),
        ];

        foreach ($dirPaths as $dirPath) {
            $filePath = $dirPath . DIRECTORY_SEPARATOR . 'TWSIN' . '.' . date("dmy_His").".csv";
            if ($this->ftpConnection->uploadFile($filePath, $csvContent)) {
                $this->addInfoLog('File Uploaded: ' . $filePath);
            } else {
                throw new LocalizedException(
                    __('Unable to upload file: %1, %2', $filePath, $this->ftpConnection->getLastError())
                );
            }
        }

        foreach ($asnCollection as $asn) {
            $this->asnReleasedCounter++;
            $asn->setIsReleased();
            $asn->save();
            $this->addInfoLog('ASN ' . $asn->getAsnNumber() . ' has been released.');
        }
    }

    /**
     * @return \string[][]
     */
    public function getParametersFormConfig()
    {
        return [
            'ftp_connection' => [
                'type' => 'fieldset',
                'legend' => 'Ftp Connection',
            ],
            'dir_path_1' => [
                'type' => 'text',
                'label' => 'Directory Path to upload CSV File',
                'fieldset' => 'ftp_connection',
            ],
            'dir_path_2' => [
                'type' => 'text',
                'label' => 'Directory Path to upload CSV File',
                'fieldset' => 'ftp_connection',
            ],
            'csv_options' => [
                'type' => 'fieldset',
                'legend' => 'CSV Export Options',
            ],
            'delimiter' => [
                'type' => 'text',
                'label' => ' field delimiter (one character only)',
                'fieldset' => 'csv_options',
            ],
            'enclosure' => [
                'type' => 'text',
                'label' => 'field enclosure (one character only)',
                'fieldset' => 'csv_options',
            ],
        ];
    }
}
