<?php
/**
 * Created by JetBrains PhpStorm.
 * User: FUHU
 * Date: 12/28/13
 * Time: 10:41 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Fanpage\Model;
use Zend\Config\Reader\Xml;
use Application\Document;

class FanpageModel
{

    public function getFanpageInfoByUserID($userid, $dm)
    {
        $document = $dm->createQueryBuilder('Application\Document\Fanpage')
            ->select()
            ->field('userid')->equals($userid)
            ->getQuery()
            ->getSingleResult();

        $result = array();
        if(isset($document))
        {
            $result = array(
                'userid'          => $document->getUserid(),
                'pageid'          => $document->getPageid(),
                'pagetype'        => $document->getPagename(),
                'pagedescription' => $document->getPagedescription(),
            );
        }
        return $result;
    }

    //FUNCTION FOR SAVE NEW LOGO OR AVATAR OF PAGE

    public function checkExistsAlbumofPage($pageid, $albumType, $dm)
    {
        $albumID = 'ALB'.$pageid.$albumType;

        $document = $dm->getRepository('Application\Document\Album')->findOneBy(array(
            'albumid' => $albumID,
            'userid'  => $pageid
        ));

        if(isset($document) && $document!= null){
            return true;
        }else{
            return false;
        }
    }

    public function resetLogoAndAvatarPage($pageID, $albumType,$dm)
    {
        $albumID = 'ALB'.$pageID.$albumType;
        $newImageStatus = "";
        if($albumType == "LOGO"){
            $newImageStatus = "LOGO_OLD";
        }elseif($albumType == "AVA"){
            $newImageStatus = "AVA_OLD";
        }

        $document = $dm->createQueryBuilder('Application\Document\Image')
            ->update()
            ->multiple(true)
            ->field('imagestatus')->set($newImageStatus)
            ->field('albumid')->equals($albumID)
            ->getQuery()
            ->execute();

        if(isset($document)){
            return true;
        }else{
            return false;
        }
    }

    public function savePageImage($data, $dm)
    {
        $pageID      = $data['pageid'];
        $createdTime = $data['createdTime'];
        $imageType   = substr($data['imageType'], -3, 3);
        $albumType   = $data['albumType'];
        $albumID     = "ALB".$pageID.$albumType;
        $imageID     = "IMG".$pageID.$createdTime.$albumType;
        $imageStatus = "";
        //Bang Action
        $actionID = "ACT".$createdTime;
        $actionUser = $actionLocation = $pageID;
        $actionType = $imageID;

        if($albumType == "LOGO")
        {
            $imageStatus = "LOGO_NOW";
        }
        elseif($albumType == "AVA")
        {
            $imageStatus = "AVA_NOW";
        }

        $check = $this->checkExistsAlbumofPage($pageID, $albumType, $dm);

        if($check == false)
        {
            $document = $dm->createQueryBuilder('Application\Document\Album')
                ->insert()
                ->field('albumid')->set($albumID)
                ->field('userid')->set($pageID)
                ->getQuery()
                ->execute();
        }

        $checkReset = $this->resetLogoAndAvatarPage($pageID, $albumType, $dm);

        if($checkReset){
            $doc = $dm->createQueryBuilder('Application\Document\Image')
                ->insert()
                ->field('imageid')->set($imageID)
                ->field('albumid')->set($albumID)
                ->field('imagestatus')->set($imageStatus)
                ->field('imagetype')->set($imageType)
                ->getQuery()
                ->execute();

            $doc = $dm->createQueryBuilder('Application\Document\Action')
                ->insert()
                ->field('actionid')->set($actionID)
                ->field('actionuser')->set($actionUser)
                ->field('actionlocation')->set($actionLocation)
                ->field('actiontype')->set($actionType)
                ->field('createdtime')->set($createdTime)
                ->getQuery()
                ->execute();
        }

        if(isset($doc)){
            return true;
        }else{
            return false;
        }
    }


    public function getBindingInfo($pageID, $dm)
    {
        $bindingInfo = null;
        $albumLogoID = 'ALB'.$pageID."LOGO";
        $albumAvatarID = 'ALB'.$pageID."AVA";

        $imageLogo = $dm->createQueryBuilder('Application\Document\Image')
            ->select()
            ->field('albumid')->equals($albumLogoID)
            ->field('imagestatus')->equals("LOGO_NOW")
            ->getQuery()
            ->getSingleResult();

        $imageAvatar = $dm->createQueryBuilder('Application\Document\Image')
            ->select()
            ->field('albumid')->equals($albumAvatarID)
            ->field('imagestatus')->equals("AVA_NOW")
            ->getQuery()
            ->getSingleResult();

        if(isset($imageLogo)){
            $imageLogoPath = $imageLogo->getImageid().'.'.$imageLogo->getImagetype();
            $bindingInfo['bindingImageLogo'] = $imageLogoPath;
        }

        if(isset($imageAvatar)){
            $imageAvatarPath = $imageAvatar->getImageid().'.'.$imageAvatar->getImagetype();
            $bindingInfo['bindingImageAvatar'] = $imageAvatarPath;
        }

        return $bindingInfo;
    }
}