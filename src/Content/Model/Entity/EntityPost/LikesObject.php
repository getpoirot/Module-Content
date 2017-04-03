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
    protected $members;
    /** @var array */
    protected $latestMembers = array(

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
     * Members UID List
     *
     * @param array|\Traversable $members
     *
     * @return $this
     */
    function setTotalMembers($members)
    {
        if ($members instanceof \Traversable)
            $members = \Poirot\Std\cast($members)->toArray();

        if ($members !== null & !is_array($members))
            throw new \InvalidArgumentException(sprintf(
                'Members must instance of Traversable or array; given (%s).'
                , \Poirot\Std\flatten($members)
            ));


        $this->members = $members;
        return $this;
    }

    function getTotalMembers()
    {
        return $this->members;
    }

    /**
     * List Members
     *
     * @return array
     */
    function getLatestMembers()
    {
        return $this->latestMembers;
    }

    /**
     * Set Members
     *
     * @param []MemberObject $members
     *
     * @return $this
     */
    function setLatestMembers($latestMembers)
    {
        $this->latestMembers = array();

        /** @var MemberObject $m */
        foreach ($latestMembers as $m)
            $this->addLatestMember($m);

        return $this;
    }

    /**
     * Add Member To Likes
     *
     * @param MemberObject $member
     *
     * @return $this
     */
    function addLatestMember(MemberObject $member)
    {
        $this->latestMembers[] = $member;
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
        if (isset($options['latest_members']) && $members = &$options['latest_members']) {
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
