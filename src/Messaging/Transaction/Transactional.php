<?php
declare(strict_types=1);

namespace SimplyCodedSoftware\Messaging\Transaction;

use SimplyCodedSoftware\Messaging\Annotation\RequiredReferenceNameList;

/**
 * Class Transactional
 * @package SimplyCodedSoftware\Messaging\Transaction
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 * @Annotation
 */
class Transactional
{
    /**
     * @var array
     * @RequiredReferenceNameList()
     */
    private $factoryReferenceNameList;

    /**
     * Transactional constructor.
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->factoryReferenceNameList = $values['factoryReferenceNameList'];
    }

    /**
     * @return array
     */
    public function getFactoryReferenceNameList(): array
    {
        return $this->factoryReferenceNameList;
    }
}