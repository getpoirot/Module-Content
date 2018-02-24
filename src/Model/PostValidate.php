<?php
namespace Module\Content\Model;

use Module\Content\Model\Entity\EntityPost\ContentObjectGeneral;
use Poirot\Std\aValidator;
use Module\Content\Interfaces\Model\Entity\iEntityPost;
use Poirot\Std\Exceptions\exUnexpectedValue;


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

        if (! $content )
            $exceptions[] = new exUnexpectedValue('Parameter %s is required.', 'content');


        if ($content instanceof ContentObjectGeneral) {

            // Title:
            //
            if ($title = $content->getTitle()) {
                $title = $this->_assertNewLine( $this->_assertTrim($title) );
                $content->setTitle($title);
            }


            // Description:
            //
            $description = $content->getDescription();

            // Validate Length Of Content When We Have No Media
            $medias = $content->getMedias();
            if (count($medias) == 0) {
                if (function_exists('mb_strlen'))
                    $len = mb_strlen($description);
                else
                    $len = strlen($description);


                if ($len < 15)
                    throw new exUnexpectedValue('Content Description Length Is Less Than Minimum.');

                if ($len > 1200)
                    throw new exUnexpectedValue('Content Description Length Is More Than Maximum.');
            }


            // Assert Content
            $description = $this->_assertNewLine($description);
            $description = $this->_assertTrim($description);

            $content->setDescription($description);
        }


        return $exceptions;
    }


    // ..

    private function _assertNewLine($description)
    {
        return preg_replace( '/\t+/', '',  preg_replace('/(\r\n|\n|\r){3,}/', "$1$1", $description) );
    }

    private function _assertTrim($description)
    {
        return trim($description);
    }
}
