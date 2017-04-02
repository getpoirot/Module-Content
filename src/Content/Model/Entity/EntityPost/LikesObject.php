<?php
namespace Module\Content\Model\Entity\EntityPost;

use Module\Content\Model\Entity\MemberObject;
use Module\MongoDriver\Model\aObject;


class LikesObject
    extends aObject
{
    /** @var int */
    protected $count;
    /** @var array */
    protected $members = array(

    );


    /**
     * Get Total Likes Count
     *
     * @return int
     */
    function getCount()
    {
        return $this->count;
    }

    /**
     * Set Total Likes Count
     *
     * @param int $count
     *
     * @return $this
     */
    function setCount($count)
    {
        $this->count = (int) $count;
        return $this;
    }


    /**
     * List Members
     *
     * @return array
     */
    function getMembers()
    {
        return $this->members;
    }

    /**
     * Set Members
     *
     * @param []MemberObject $members
     *
     * @return $this
     */
    function setMembers($members)
    {
        $this->members = array();

        /** @var MemberObject $m */
        foreach ($members as $m)
            $this->addMember($m);

        return $this;
    }

    /**
     * Add Member To Likes
     *
     * @param MemberObject $member
     *
     * @return $this
     */
    function addMember(MemberObject $member)
    {
        $this->members[] = $member;
        return $this;
    }


    // Implement Configurable

    /**
     * Build Object With Provided Options
     *
     * @param array $options        Associated Array
     * @param bool  $throwException Throw Exception On Wrong Option
     *
     * @return array Remained Options (if not throw exception)
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    function with(array $options, $throwException = false)
    {
        if (isset($options['members']) && $members = &$options['members']) {
            foreach ($members as $i => $m) {
                if (!$m instanceof MemberObject) {
                    $objMember   = new MemberObject;
                    $objMember->with($objMember::parseWith($m));
                    $members[$i] = $objMember;
                }
            }
        }

        parent::with($options);
    }
}
