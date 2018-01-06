<?php
namespace Module\Content\Events\CreateContent;

use Module\Content\Exception\exMaximumRepostsReached;
use Module\Content\Model\Entity\EntityPost;
use Module\Content\Interfaces\Model\Repo\iRepoPosts;
use Module\Content\Model\Entity\EntityPost\ContentObjectRepost;


class OnBeforeCreateContentValidateRepost
{
    const MAX_REPOSTS_LIMIT = 10;

    /** @var iRepoPosts */
    protected $repoPosts;


    /**
     * Construct
     *
     * @param iRepoPosts $repoPosts @IoC /module/content/services/repository/Posts
     */
    function __construct(iRepoPosts $repoPosts)
    {
        $this->repoPosts    = $repoPosts;
    }


    /**
     * @param EntityPost $entityPost
     * @param $me
     * @return array|null
     * @throws exMaximumRepostsReached
     */
    function __invoke($entityPost, $me)
    {
        if ( ! ($entityPost->getContent() instanceof ContentObjectRepost))
            return;


        $datetime = new \DateTime('now', new \DateTimeZone('UTC'));
        $datetime->add(\DateInterval::createFromDateString('yesterday'));

        $cnt = $this->repoPosts->countUserRepostsNewerThan($me, $datetime);

        if ($cnt > self::MAX_REPOSTS_LIMIT)
            throw new exMaximumRepostsReached;
    }
}
