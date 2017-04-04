<?php
namespace Module\Content\Interfaces\Model\Repo;

use Module\Content\Interfaces\Model\Entity\iEntityComment;


interface iRepoComments
{
    /**
     * Insert Comment Entity
     *
     * @param iEntityComment $entity
     *
     * @return iEntityComment Include persistence insert identifier
     */
    function insert(iEntityComment $entity);
}
