<?php
namespace Module\Content\Interfaces\Model\Entity;


interface iEntityMediaObject
{
    function getStorageType();

    function getHash();

    function getContentType();
}
