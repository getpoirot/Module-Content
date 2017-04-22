<?php
namespace Module\Content\Model\Entity;

use Poirot\Std\Struct\aValueObject;


class MemberObject
    extends aValueObject
{
    /** @var mixed */
    protected $uid;


    /**
     * Set Member Uid
     *
     * @param mixed $uid
     *
     * @return $this
     */
    function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }

    function getUid()
    {
        return $this->uid;
    }

    /**
     * Build Object With Provided Options
     *
     * @param array $options Associated Array
     * @param bool $throwException Throw Exception On Wrong Option
     *
     * @return array Remained Options (if not throw exception)
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    function with(array $options, $throwException = false)
    {
        if ($throwException) {
            if (!isset($options['uid']))
                throw new \InvalidArgumentException('UID is Required.');
        }


        parent::with($options);
    }
}
