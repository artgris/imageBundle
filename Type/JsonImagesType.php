<?php

namespace Arkounay\ImageBundle\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonArrayType;
use Arkounay\ImageBundle\Entity\Image;

class JsonImagesType extends JsonArrayType
{
    const TYPE = 'json_images';

    public function getName()
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        if (is_array($value)) {
            $value = new ArrayCollection($value);
        }

        /** @var ArrayCollection|Image[] $value */
        foreach ($value as $item) {
            if ($item === null || $item->getPath() === null) {
                $value->removeElement($item);
            }
        }

        return json_encode($value->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return new ArrayCollection([new Image()]);
        }

        $value = (is_resource($value)) ? stream_get_contents($value) : $value;

        $json = json_decode($value, true);

        $res = new ArrayCollection();
        foreach ($json as $el) {
            $image = new Image();

            if (isset($el['alt'])) {
                $image->setAlt($el['alt']);
            }

            if (isset($el['path'])) {
                $image->setPath($el['path']);
            }

            $res->add($image);
        }

        if ($res->isEmpty()) {
            $res->add(new Image());
        }

        return $res;
    }

}