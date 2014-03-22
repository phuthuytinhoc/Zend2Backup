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
    public function getTimestampNow()
    {
        $date = new \DateTime(null, new \DateTimeZone('Asia/Ho_Chi_Minh'));
        return $date->getTimestamp();
    }

    public function getValueByDate($arr, $type, $action_month)
    {
//        $action_month = 'thang '.$action_month;
        $newValue = 0;
        if(isset($arr[$type][$action_month])){
            $newValue = $arr[$type][$action_month] + 1;
        }
        else{
            $newValue++;
        }
        $arr[$type][$action_month] = $newValue;

        return $arr;

    }

    public function getChartStatusandComment($data, $dm)
    {
        $arr = array();
        $stts = 0;
        $likes = 0;
        $cmts = 0;
        $pageid = $data['pageid'];
        $action = $dm->createQueryBuilder('Application\Document\Action')
            ->select()
//            ->field('actionuser')->equals($pageid)
            ->field('actionlocation')->equals($pageid)
            ->getQuery()
            ->execute();

        if(isset($action))
        {
            $curr_year = intval(date('Y'));
            foreach($action as $node)
            {
                $actionType = $node->getActionType();
                $check = substr($actionType, 0, 3);
                $time = $node->getCreatedtime();
                $action_year  = intval(date('Y', intval($time)));
                $action_month = intval(date('m', intval($time)));
                $checkActUser = $node->getActionUser();

                if($check == 'LIK')
                {
                    if($action_year == $curr_year)
                    {
                        $arr = $this->getValueByDate($arr, 'like', $action_month);
                    }

                    $likes++;
                }
                elseif($check == 'CMT')
                {
                    if($action_year == $curr_year)
                    {
                        $arr = $this->getValueByDate($arr, 'comment', $action_month);
                    }
                    $cmts++;
                }
                else
                {
                    if($checkActUser == $pageid)
                    {
                        if($action_year == $curr_year)
                        {
                            $arr = $this->getValueByDate($arr, 'status', $action_month);
                        }
                        $stts++;
                    }
                }
            }
            $arr['total_status'] = $stts;
            $arr['total_likes']  = $likes;
            $arr['total_cmts']   = $cmts;
        }

        return $arr;
    }

    public function getChartLikePage($pageid, $dm)
    {
        $arr = array();
        $pre_arr = array();
        $cur_month = intval(date('m'));
        $cur_year  = intval(date('Y'));
        $pre_year = $cur_year-1;

        $fm = $dm->createQueryBuilder('Application\Document\FanpageManage')
            ->select()
            ->field('pageid')->equals($pageid)
            ->field('pageuserstatus')->equals('LIKE')
            ->getQuery()
            ->execute();

        if(isset($fm))
        {
            $total_like = $fm->count();
            $arr['total_like']    = $total_like;
            $arr['current_year']  = $cur_year;
            $arr['current_month'] = $cur_month;
            foreach($fm as $node)
            {
                $newValue=0;
                $timestamp = $node->getCreatedtime();
                $thang = intval(date('m', intval($timestamp)));
                $nam = date('Y',intval($timestamp));

                if($nam == $cur_year && $thang <= $cur_month)
                {
                    if(isset($arr['data'][$thang])){
                        $newValue = $arr['data'][$thang] + 1;
                    }
                    else{
                        $newValue++;
                    }
                    $arr['data'][$thang] = $newValue;
                }

                $valuePre = 0;
                if($nam == $pre_year)
                {

                     if(isset($pre_arr['data'][$thang])){
                         $valuePre = $pre_arr['data'][$thang] + 1;
                     }
                     else{
                         $valuePre++;
                     }
                    $pre_arr['data'][$thang] = $valuePre;
                }

            }
        }
        return array(
            'arr' => $arr,
            'pre_arr' => $pre_arr,
        );
    }

    public function deleteManagePage($data, $dm)
    {
        $userid = $data['userid'];
        $pageid = $data['pageid'];

        $fm = $dm->createQueryBuilder('Application\Document\FanpageManage')
            ->remove()
            ->field('userid')->equals($userid)
            ->field('pageid')->equals($pageid)
            ->field('pageuserstatus')->equals('MOD')
            ->getQuery()
            ->execute();

        if(isset($fm))
            return true;
        return false;
    }

    public function checkExistsMod($userid, $pageid, $dm)
    {
        $document = $dm->createQueryBuilder('Application\Document\FanpageManage')
            ->select()
            ->field('userid')->equals($userid)
            ->field('pageid')->equals($pageid)
            ->field('pageuserstatus')->equals('MOD')
            ->getQuery()
            ->getSingleResult();

        if(isset($document)){
            return true;
        }
        return false;
    }

    public function addPageManager($data, $dm)
    {
        $createdtime = $data['createdtime'];
        $pageid      = $data['pageid'];
        $userid      = $data['userid'];

        $result = $this->checkOwnerPage($userid, $pageid, $dm);
        $chkMode = $this->checkExistsMod($userid, $pageid, $dm);

        if(!$result)
        {
            if(!$chkMode)
            {
                $fm = $dm->createQueryBuilder('Application\Document\FanpageManage')
                    ->insert()
                    ->field('userid')->set($userid)
                    ->field('pageid')->set($pageid)
                    ->field('pageuserstatus')->set('MOD')
                    ->field('createdtime')->set($createdtime)
                    ->getQuery()
                    ->execute();

                if(isset($fm))
                    return 'success_query';
                else
                    return 'wrong_query';
            }
            else
            {
                return 'exists_user';
            }
        }
        else
            return 'owner';
    }

    public function getListOwnersofPage($pageID, $dm)
    {
        $listArr =null;
        $count = 0;

        $admin = $dm->createQueryBuilder('Application\Document\Fanpage')
            ->select()
            ->field('pageid')->equals($pageID)
            ->getQuery()
            ->getSingleResult();
        $infoAd = $dm->createQueryBuilder('Application\Document\User')
            ->select()
            ->field('userid')->equals($admin->getUserid())
            ->getQuery()
            ->getSingleResult();

        if(isset($infoAd))
        {
            $count=1;
        }

        $listArr[0] = array(
            'stt'   => $count,
            'hoten' => $infoAd->getLastname().' '.$infoAd->getFirstname(),
            'link'  => '/success?user='.$infoAd->getUserid(),
            'role'  => 'ADMIN',
            'date'  => date('d/m/Y', $admin->getCreatedtime()),
            'userid'=> $infoAd->getUserid(),
        );

        $fm = $dm->createQueryBuilder('Application\Document\FanpageManage')
            ->select()
            ->field('pageuserstatus')->equals('MOD')
            ->field('pageid')->equals($pageID)
            ->getQuery()
            ->execute();

        if(isset($fm)){
            foreach($fm as $node)
            {
                $count++;

                $user = $dm->createQueryBuilder('Application\Document\User')
                    ->select()
                    ->field('userid')->equals($node->getUserid())
                    ->getQuery()
                    ->getSingleResult();

                if(isset($user))
                {
                    $listArr[] = array(
                        'stt'   => $count,
                        'hoten' => $user->getLastname().' '.$user->getFirstname(),
                        'link'  => '/success?user='.$user->getUserid(),
                        'role'  => $node->getPageuserstatus(),
                        'date'  => date('d/m/Y', $node->getCreatedtime()),
                        'userid'=> $node->getUserid(),
                    );
                }
            }
        }

        return $listArr;
    }

    public function getBacsicInfoUserManage($data, $dm)
    {
        $userID = $data['userid'];
//        $userID = 'user1386567908';
        $albumid = 'ALB'.$userID.'AVA';
        $biding = array();

        $user = $dm->createQueryBuilder('Application\Document\User')
            ->select()
            ->field('userid')->equals($userID)
            ->getQuery()
            ->getSingleResult();
        $image = $dm->createQueryBuilder('Application\Document\Image')
            ->select()
            ->field('albumid')->equals($albumid)
            ->field('imagestatus')->equals('AVA_NOW')
            ->getQuery()
            ->getSingleResult();

        if(isset($user))
        {
            $biding['name'] =  $user->getLastname().' '.$user->getFirstname();
            $biding['path'] =  '/success?user='.$userID;

            if(isset($image))
            {
                $biding['ava'] = '/uploads/'.$image->getImageid().'.'.$image->getImagetype();
            }
            else
            {
                $biding['ava'] = '/uploads/ava-temp.png';
            }

            return array(
                'check' => true,
                'data'  => $biding,
            );
        }

        return array(
            'check' => false,
        );

    }

    public function checkOwnerPage($userID, $pageID, $dm)
    {
        $document = $dm->createQueryBuilder('Application\Document\Fanpage')
            ->select()
            ->field('userid')->equals($userID)
            ->field('pageid')->equals($pageID)
            ->getQuery()
            ->getSingleResult();

        if(isset($document)){
            return true;
        }else{
            return false;
        }
    }

    public function getFanpageInfoByUserID($pageID, $dm)
    {
        $document = $dm->createQueryBuilder('Application\Document\Fanpage')
            ->select()
            ->field('pageid')->equals($pageID)
            ->getQuery()
            ->getSingleResult();

        $result = null;
        if(isset($document))
        {
            $result = array(
                'userid'          => $document->getUserid(),
                'pageid'          => $document->getPageid(),
                'pagetype'        => $document->getPagetype(),
                'pagename'        => $document->getPagename(),
                'pagedescription' => $document->getPagedescription(),
                'pagelongtitude'  => $document->getPagelongtitude(),
                'pagelatitude'    => $document->getPagelatitude(),
                'pagemapinfo'     => $document->getPagemapinfo(),
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
        }elseif($albumType == "SLI"){
            return true;
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
        elseif($albumType == "SLI")
        {
            $imageStatus = "SLIDESHOW";
            $imageType = "png";
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
        $albumSlideShow = 'ALB'.$pageID.'SLI';

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

        $slideShow = $dm->createQueryBuilder('Application\Document\Image')
            ->select()
            ->field('albumid')->equals($albumSlideShow)
            ->field('imagestatus')->equals("SLIDESHOW")
            ->getQuery()
            ->execute();

        if(isset($slideShow))
        {
            foreach($slideShow as $ss)
            {
                $imageSS = $ss->getImageid().'.png';
                $bindingInfo['bindingSlideShow'][] = $imageSS;
            }
        }

        $listAlbumMain = $dm->createQueryBuilder('Application\Document\Album')
            ->select()
            ->field('userid')->equals($pageID)
            ->getQuery()
            ->execute();

        if(isset($listAlbumMain))
        {
            foreach($listAlbumMain as $albMain)
            {
                $albumType = $albMain->getAlbumid();
                if(substr($albumType, -17, 7) == "SUBMAIN")
                {
                    $bindingInfo['bindingAlbumMain'][] = array(
                        'albumid' => $albumType,
                        'albumName' => $albMain->getAlbumname(),
                    );
                }
            }
        }

//        var_dump($bindingInfo);die();

        return $bindingInfo;
    }

    public function getInfoAlbumSubMain($data, $dm)
    {

    }

    public function updatePageMapInfo($data, $dm)
    {
        $pageID = $data['pageID'];
        $pageMapInfo = $data['pageMapDescription'];
        $pageLongtitude = $data['pageLongtitude'];
        $pageLatitude = $data['pageLatitude'];

        $doc = $dm->createQueryBuilder('Application\Document\Fanpage')
            ->update()
            ->multiple(true)
            ->field('pagelongtitude')->set($pageLongtitude)
            ->field('pagelatitude')->set($pageLatitude)
            ->field('pagemapinfo')->set($pageMapInfo)
            ->field('pageid')->equals($pageID)
            ->getQuery()
            ->execute();

        if(isset($doc)){
            return true;
        }else{
            return false;
        }

    }

    public function updateBasicInfoPage($data,$dm)
    {
        $pageID = $data['pageID'];
        $pageDescription = $data['pageDescription'];

        $doc = $dm->createQueryBuilder('Application\Document\Fanpage')
            ->update()
            ->multiple(true)
            ->field('pagedescription')->set($pageDescription)
            ->field('pageid')->equals($pageID)
            ->getQuery()
            ->execute();

        if(isset($doc)){
            return true;
        }else{
            return false;
        }
    }

    public function createAlbumLocationPage($data, $dm)
    {
        $pageID           = $data['pageID'];
        $albumName        = $data['nAlbumName'];
        $albumDescription = $data['nMotaAlbum'];
        $albumLong        = $data['albumLong'];
        $albumLat         = $data['albumLat'];
        $albumType        = 'MAIN';
        $albumID          = 'ALB'.$pageID.$albumType;

        $checkAlbum = $this->checkExistsAlbumofPage($pageID, $albumType, $dm);
        if($checkAlbum == false)
        {
            $doc = $dm->createQueryBuilder('Application\Document\Album')
            ->insert()
            ->field('albumid')->set($albumID)
            ->field('userid')->set($pageID)
            ->field('albumdescription')->set($albumDescription)
            ->field('albumname')->set($albumName)
            ->field('albumlatitude')->set($albumLat)
            ->field('albumlongtitude')->set($albumLong)
            ->getQuery()
            ->execute();
        }

        if(isset($doc)){
            return true;
        }else{
            return false;
        }
    }

    public function createSubAlbum($data, $dm)
    {
        $createTime = $this->getTimestampNow();
        $pageID           = $data['pageID'];
        $albumName        = $data['sub_albumName'];
        $albumDescription = $data['sub_albumDescript'];
        $albumLong        = $data['long_subAlbum'];
        $albumLat         = $data['lat_subAlbum'];
        $albumType        = 'SUBMAIN';
        $albumID          = 'ALB'.$pageID.$albumType.$createTime;

        $checkAlbum = $this->checkExistsAlbumofPage($pageID, 'MAIN', $dm);

        if($checkAlbum)
        {
            $doc = $dm->createQueryBuilder('Application\Document\Album')
                ->insert()
                ->field('albumid')->set($albumID)
                ->field('userid')->set($pageID)
                ->field('albumdescription')->set($albumDescription)
                ->field('albumname')->set($albumName)
                ->field('albumlatitude')->set($albumLat)
                ->field('albumlongtitude')->set($albumLong)
                ->getQuery()
                ->execute();

            if(isset($doc))
                return true;
            else
                return false;
        }
        else
            return false;
    }

    public function bindingAlbumData($data, $dm)
    {
        $albumID = $data['albumID'];
        $bindAlbum = null;
        $doc = $dm->createQueryBuilder('Application\Document\Album')
            ->select()
            ->field('albumid')->equals($albumID)
            ->getQuery()
            ->getSingleResult();

        if(isset($doc))
        {
            $bindAlbum = array(
                'albumname'        => $doc->getAlbumname(),
                'albumdescription' => $doc->getAlbumdescription(),
                'albumlongtitude'  => $doc->getAlbumlongtitude(),
                'albumlatitude'    => $doc->getAlbumlatitude(),
            );
        }

        return $bindAlbum;
    }

    public function getListNewAlbums($data, $dm)
    {
        $pageID = $data['pageID'];
        $arr = array();
        $doc = $dm->createQueryBuilder('Application\Document\Album')
            ->select()
            ->field('userid')->equals($pageID)
            ->getQuery()
            ->execute();
        if(isset($doc))
        {
            foreach($doc as $node)
            {
                if(strpos($node->getAlbumid(), 'SUBMAIN'))
                {
                    $arr[] = array(
                        'albumid'   => $node->getAlbumid(),
                        'albumname' => $node->getAlbumname(),
                    );
                }
            }
        }
        return $arr;
    }

    public function updateAlbumInfobyAlbumID($data, $dm)
    {
        $pageID           = $data['pageID'];
        $albumID          = $data['albumID'];
        $albumName        = $data['nAlbumName'];
        $albumDescription = $data['nMotaAlbum'];
        $albumLong        = $data['albumLong'];
        $albumLat         = $data['albumLat'];

        $doc = $dm->createQueryBuilder('Application\Document\Album')
            ->update()
            ->multiple(true)
            ->field('albumid')->equals($albumID)
            ->field('userid')->equals($pageID)
            ->field('albumdescription')->set($albumDescription)
            ->field('albumname')->set($albumName)
            ->field('albumlatitude')->set($albumLat)
            ->field('albumlongtitude')->set($albumLong)
            ->getQuery()
            ->execute();

        if(isset($doc)){
            return true;
        }else{
            return false;
        }
    }

    public function deleteAlbumImage($data, $dm)
    {
        $pageID           = $data['pageID'];
        $albumID          = $data['albumID'];

        $doc = $dm->createQueryBuilder('Application\Document\Album')
            ->remove()
            ->field('albumid')->equals($albumID)
            ->field('userid')->equals($pageID)
            ->getQuery()
            ->execute();
        if(isset($doc))
        {
            $doc = $dm->createQueryBuilder('Application\Document\Image')
                ->remove()
                ->multiple(true)
                ->field('albumid')->equals($albumID)
                ->getQuery()
                ->execute();
            return true;
        }
        else{
            return false;
        }
    }

    public function insertNewImageofAlbum($data, $dm)
    {
        $pageID     = $data['pageID'];
        $albumID    = $data['albumID'];
        $dataImage  = $data['dataImage'];

        $imageType = substr($dataImage, -3, 3);
        $imageID   = substr($dataImage, 0, 30);
        $imageStatus = "IMG_EFFECT";
        $imageLong = $data['imageLong'];
        $imageLat  = $data['imageLat'];
        $imageDescript = $data['imageDescript'];

        $createdTime = substr($dataImage,17,10);
        $actionID = "ACT".$createdTime;
        $actionLocation = $actionUser = $pageID;
        $actionType = $imageID;


        $doc = $dm->createQueryBuilder('Application\Document\Image')
            ->insert()
            ->field('albumid')->set($albumID)
            ->field('imageid')->set($imageID)
            ->field('imagetype')->set($imageType)
            ->field('imagestatus')->set($imageStatus)
            ->field('imagelongtitude')->set($imageLong)
            ->field('imagelatitude')->set($imageLat)
            ->field('imagedescription')->set($imageDescript)
            ->getQuery()
            ->execute();

        if(isset($doc))
        {
            $doc = $dm->createQueryBuilder('Application\Document\Action')
                ->insert()
                ->field('actionid')->set($actionID)
                ->field('actionuser')->set($actionUser)
                ->field('actionlocation')->set($actionLocation)
                ->field('actiontype')->set($actionType)
                ->field('createdtime')->set($createdTime)
                ->getQuery()
                ->execute();
            if(isset($doc))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }

    }

    public function bindingAllImageofAlbum($data, $dm)
    {
        $albumID = $data['albumID'];

        $arr = null;

        $doc = $dm->createQueryBuilder('Application\Document\Image')
            ->select()
            ->field('albumid')->equals($albumID)
            ->getQuery()
            ->execute();

        if(isset($doc)){
            $i = 0;
            foreach($doc as $node)
            {
                $imageid = $node->getImageid();
                $arr[$i] = array(
                    'imageid'   => $imageid,
                    'pathImage' => $imageid.'.'.$node->getImagetype(),
                    'imageDes'  => $node->getImagedescription(),
                    'lat'       => $node->getImagelatitude(),
                    'long'      => $node->getImagelongtitude(),
                );
                $i++;
            }
        }

        return $arr;
    }

    public function bindingInfoOfImage($data, $dm)
    {
        $imageID = $data['imageID'];

        $arr = null;

        $doc = $dm->createQueryBuilder('Application\Document\Image')
            ->select()
            ->field('imageid')->equals($imageID)
            ->getQuery()
            ->getSingleResult();

        if(isset($doc))
        {
            $arr = array(
                'imageid'   => $imageID,
                'path'      => $imageID.'.'.$doc->getImagetype(),
                'imageDes'  => $doc->getImagedescription(),
                'imageLong' => $doc->getImagelongtitude(),
                'imageLat'  => $doc->getImagelatitude(),
            );
        }
        return $arr;
    }

    public function updateInfoImageofAlbum($data, $dm)
    {
        $imageID         = $data['imageID'];
        $imageLong       = $data['imageLong'];
        $imageLat        = $data['imageLat'];
        $imageDescript   = $data['imageDescript'];

        $doc =$dm->createQueryBuilder('Application\Document\Image')
            ->update()
            ->multiple(true)
            ->field('imagelongtitude')->set($imageLong)
            ->field('imagelatitude')->set($imageLat)
            ->field('imagedescription')->set($imageDescript)
            ->field('imageid')->equals($imageID)
            ->getQuery()
            ->execute();

        if(isset($doc)){
            return true;
        }else{
            return false;
        }
    }

    public function deleteImageofAlbum($data, $dm)
    {
        $imageID = $data['imageID'];

         $doc = $dm->createQueryBuilder('Application\Document\Image')
                ->remove()
                ->field('imageid')->equals($imageID)
                ->getQuery()
                ->execute();

        $doc2 = $dm->createQueryBuilder('Application\Document\Action')
            ->remove()
            ->field('actiontype')->equals($imageID)
            ->getQuery()
            ->execute();

        if(isset($doc) and isset($doc2)){
            return true;
        }else{
            return false;
        }
    }

    public function updateSlideShowPage($data, $dm)
    {
        $pageID = $data['pageID'];
        $albumID = 'ALB'.$pageID.'SLI';
        $arr = array();

        $image =$dm->createQueryBuilder('Application\Document\Image')
            ->select()
            ->field('albumid')->equals($albumID)
            ->field('imagestatus')->equals('SLIDESHOW')
            ->getQuery()
            ->execute();

        if(isset($image))
        {
            foreach($image as $node)
            {
                $arr[] = '/uploads/'.$node->getImageid().'.'.$node->getImagetype();
            }
        }
        return $arr;
    }

}