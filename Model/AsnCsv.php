<?php
/**
 * Copyright © Alekseon sp. z o.o.
 * http://www.alekseon.com/
 */
namespace ITvoice\AsnCreator\Model;

use ITvoice\Asn\Model\Asn;

/**
 * Class AsnCsv
 * @package ITvoice\AsnCreator\Model
 */
class AsnCsv
{
    /**
     * @param Asn $asn
     * @param string $delimiter
     * @param string $enclosure
     * @return array
     */
    public function getCsv(Asn $asn, $delimiter = ",", $enclosure = '"')
    {
        $csvContent = [];
        return $csvContent;
    }
}
