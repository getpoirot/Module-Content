<?php
namespace Module\Content\Model;

use Poirot\Std\aValidator;
use Module\Content\Interfaces\Model\Entity\iEntityPost;
use Poirot\Std\Exceptions\exUnexpectedValue;
use Poirot\Std\Hydrator\HydrateGetters;


class PostValidate
    extends aValidator
{
    /** @var iEntityPost */
    protected $entity;


    /**
     * Construct
     *
     * @param iEntityPost $entity
     */
    function __construct(iEntityPost $entity = null)
    {
        $this->entity = $entity;
    }


    /**
     * Assert Validate Entity
     *
     * @throws exUnexpectedValue
     */
    function doAssertValidate()
    {
        $exceptions = [];

        $content = $this->entity->getContent();

        if (!$content)
            $exceptions[] = new exUnexpectedValue('Parameter %s is required.', 'content');
        else
        {

            /** @var HydrateGetters $value */
            $contentBody = $content->getIterator()->_getGetterProperties();
            $contentBody = $content->getIterator()->_getGetterProperties();

            ## replace more than 3 eneter two 1
            #
            $contentBody['title'] = preg_replace('/(\r\n|\n|\r){3,}/', "$1$1", $contentBody['title']);
            $contentBody['description'] = preg_replace('/(\r\n|\n|\r){3,}/', "$1$1", $contentBody['description']);

            #remove all tab and first white space and end white space
            #
            $contentBody['title'] = trim(preg_replace('/\t+/', '',  $contentBody['title']));
            $contentBody['description'] = trim(preg_replace('/\t+/', '',  $contentBody['description']));


            if (empty($contentBody['description']))
            {
                $exceptions[] = new exUnexpectedValue('description is required');
            }
            $content->with($contentBody);
            $this->entity->setContent($content);

        }

        return $exceptions;
    }


}
