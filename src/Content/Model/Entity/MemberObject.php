<?php
namespace Module\Content\Model\Entity;

use Module\MongoDriver\Model\aObject;


class MemberObject
    extends aObject
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
}
