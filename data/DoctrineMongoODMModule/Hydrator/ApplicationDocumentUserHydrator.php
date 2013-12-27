<?php

namespace DoctrineMongoODMModule\Hydrator;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Hydrator\HydratorInterface;
use Doctrine\ODM\MongoDB\UnitOfWork;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ODM. DO NOT EDIT THIS FILE.
 */
class ApplicationDocumentUserHydrator implements HydratorInterface
{
    private $dm;
    private $unitOfWork;
    private $class;

    public function __construct(DocumentManager $dm, UnitOfWork $uow, ClassMetadata $class)
    {
        $this->dm = $dm;
        $this->unitOfWork = $uow;
        $this->class = $class;
    }

    public function hydrate($document, $data, array $hints = array())
    {
        $hydratedData = array();

        /** @Field(type="id") */
        if (isset($data['_id'])) {
            $value = $data['_id'];
            $return = $value instanceof \MongoId ? (string) $value : $value;
            $this->class->reflFields['id']->setValue($document, $return);
            $hydratedData['id'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['firstname'])) {
            $value = $data['firstname'];
            $return = (string) $value;
            $this->class->reflFields['firstname']->setValue($document, $return);
            $hydratedData['firstname'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['lastname'])) {
            $value = $data['lastname'];
            $return = (string) $value;
            $this->class->reflFields['lastname']->setValue($document, $return);
            $hydratedData['lastname'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['email'])) {
            $value = $data['email'];
            $return = (string) $value;
            $this->class->reflFields['email']->setValue($document, $return);
            $hydratedData['email'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['password'])) {
            $value = $data['password'];
            $return = (string) $value;
            $this->class->reflFields['password']->setValue($document, $return);
            $hydratedData['password'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['createdtime'])) {
            $value = $data['createdtime'];
            $return = (string) $value;
            $this->class->reflFields['createdtime']->setValue($document, $return);
            $hydratedData['createdtime'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['userid'])) {
            $value = $data['userid'];
            $return = (string) $value;
            $this->class->reflFields['userid']->setValue($document, $return);
            $hydratedData['userid'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['dob'])) {
            $value = $data['dob'];
            $return = (string) $value;
            $this->class->reflFields['dob']->setValue($document, $return);
            $hydratedData['dob'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['address'])) {
            $value = $data['address'];
            $return = (string) $value;
            $this->class->reflFields['address']->setValue($document, $return);
            $hydratedData['address'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['quote'])) {
            $value = $data['quote'];
            $return = (string) $value;
            $this->class->reflFields['quote']->setValue($document, $return);
            $hydratedData['quote'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['relationship'])) {
            $value = $data['relationship'];
            $return = (string) $value;
            $this->class->reflFields['relationship']->setValue($document, $return);
            $hydratedData['relationship'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['school'])) {
            $value = $data['school'];
            $return = (string) $value;
            $this->class->reflFields['school']->setValue($document, $return);
            $hydratedData['school'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['work'])) {
            $value = $data['work'];
            $return = (string) $value;
            $this->class->reflFields['work']->setValue($document, $return);
            $hydratedData['work'] = $return;
        }
        return $hydratedData;
    }
}