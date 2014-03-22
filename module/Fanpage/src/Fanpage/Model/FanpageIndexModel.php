<?php
/**
 * Created by JetBrains PhpStorm.
 * User: FUHU
 * Date: 1/11/14
 * Time: 10:29 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Fanpage\Model;

use Zend\Config\Reader\Xml;
use Application\Document;

class FanpageIndexModel
{

    public function deleteCommentOnPostFP($data, $dm)
    {
        $commentID = $data['commentID'];

        $delAction = $dm->createQueryBuilder('Application\Document\Action')
            ->remove()
            ->field('actiontype')->equals($commentID)
            ->getQuery()
            ->execute();

        $delComment = $dm->createQueryBuilder('Application\Document\Comment')
            ->remove()
            ->field('commentid')->equals($commentID)
            ->getQuery()
            ->execute();

        if(isset($delAction) && isset($delComment))
        {
            return true;
        }
        return false;
    }

    public function deletePostOnWallFP($data, $dm)
    {
        $statusID = $data['statusid'];

        $delAction = $dm->createQueryBuilder('Application\Document\Action')
            ->remove()
            ->field('actiontype')->equals($statusID)
            ->getQuery()
            ->execute();

        $delStatus = $dm->createQueryBuilder('Application\Document\Status')
            ->remove()
            ->field('statusid')->equals($statusID)
            ->getQuery()
            ->execute();

        if(isset($delAction) && isset($delStatus))
        {
            return true;
        }
        return false;
    }

    public function deleteFPPost($data, $dm)
    {
        $pageID = $data['pageID'];
        $actionID = $data['actionID'];
        $act = $dm->createQueryBuilder('Application\Document\Action')
            ->select()
            ->field('actionid')->equals($actionID)
            ->getQuery()
            ->getSingleResult();

        if(isset($act))
        {
            $actionType = $act->getActionType();
            $type       = substr($actionType, 0, 3);
            $table      = "";
            $field      = "";

            if($type == 'STT'){
                $table = 'Application\Document\Status';
                $field = 'statusid';
            }elseif($type == 'PLA'){
                $table = 'Application\Document\Place';
                $field = 'placeid';
            }elseif($type == 'VID'){
                $table = 'Application\Document\Video';
                $field = 'videoid';
            }elseif($type == 'IMG'){
                $table = 'Application\Document\Image';
                $field = 'imageid';
            }

            //delete action
            $delAction = $dm->createQueryBuilder('Application\Document\Action')
                ->remove()
                ->field('actionid')->equals($actionID)
                ->getQuery()
                ->execute();

            $delActiontype = $dm->createQueryBuilder($table)
                ->remove()
                ->field($field)->equals($actionType)
                ->getQuery()
                ->execute();

            if(isset($delAction) && isset($delActiontype)){
                return true;
            }
        }

        return false;
    }

    public function unlikestatusofFanpage($data, $dm)
    {
        $likeID = $data['likeID'];

        $action = $dm->createQueryBuilder('Application\Document\Action')
            ->remove()
            ->field('actionid')->equals($likeID)
            ->getQuery()
            ->execute();

        $like = $dm->createQueryBuilder('Application\Document\Like')
            ->remove()
            ->field('likeid')->equals($likeID)
            ->getQuery()
            ->execute();

        if(isset($action) && isset($like)){
            return true;
        }
        return false;
    }

    public function likestatusofFanPage($data, $dm)
    {
        $typeAction = $data['action'];

        if($typeAction == 'status_Like')
        {
            $createdTime    = $data['timestamp'];
            $actionID       = 'ACT'.$createdTime;
            $actionUser     = $data['actionUser'];
            $actionLocation = $data['actionLocation'];
            $actionType     = 'LIK'.$actionUser.$createdTime;

            $likeID = 'LIK'.$actionUser.$createdTime;
            $likeACTID = $data['actionLike'];

            $action = $dm->createQueryBuilder('Application\Document\Action')
                ->insert()
                ->field('actionid')->set($actionID)
                ->field('actionuser')->set($actionUser)
                ->field('actionlocation')->set($actionLocation)
                ->field('actiontype')->set($actionType)
                ->field('createdtime')->set($createdTime)
                ->getQuery()
                ->execute();

            $like = $dm->createQueryBuilder('Application\Document\Like')
                ->insert()
                ->field('likeid')->set($likeID)
                ->field('actionid')->set($likeACTID)
                ->getQuery()
                ->execute();

            if(isset($action) and isset($like))
                return true;
        }
        else
        {
            $likeID = $data['likeID'];

            $action = $dm->createQueryBuilder('Application\Document\Action')
                ->remove()
                ->field('actionid')->equals($likeID)
                ->getQuery()
                ->execute();

            $like = $dm->createQueryBuilder('Application\Document\Like')
                ->remove()
                ->field('likeid')->equals($likeID)
                ->getQuery()
                ->execute();

            if(isset($action)){
                return true;
            }
        }

        return false;
    }

    public function checkLiked($userid, $pageid, $dm)
    {
        $fm = $dm->createQueryBuilder('Application\Document\FanpageManage')
            ->select()
            ->field('pageid')->equals($pageid)
            ->field('userid')->equals($userid)
            ->field('pageuserstatus')->equals('LIKE')
            ->getQuery()
            ->getSingleResult();

        if(isset($fm))
            return true;
        return false;
    }

    public function likepageUser($data, $dm)
    {
        $actionName = $data['action'];
        $pageID         = $data['pageID'];
        $userid         = $data['userID'];
        $pageuserstatus = 'LIKE';
        $createdtime    = $data['createdtime'];

        if($actionName == 'like')
        {
            $fm = $dm->createQueryBuilder('Application\Document\FanpageManage')
                ->insert()
                ->field('pageid')->set($pageID)
                ->field('userid')->set($userid)
                ->field('pageuserstatus')->set($pageuserstatus)
                ->field('createdtime')->set($createdtime)
                ->getQuery()
                ->execute();
            if(isset($fm)){
                return true;
            }
        }
        else
        {
            $fm = $dm->createQueryBuilder('Application\Document\FanpageManage')
                ->remove()
                ->field('pageid')->equals($pageID)
                ->field('userid')->equals($userid)
                ->field('pageuserstatus')->equals($pageuserstatus)
                ->getQuery()
                ->execute();
            if(isset($fm)){
                return true;
            }
        }
        return false;
    }

    public function checkExistsAlbum($albumID, $userID, $dm)
    {
        $album = $dm->createQueryBuilder('Application\Document\Album')
            ->select()
            ->field('albumid')->equals($albumID)
            ->field('userid')->equals($userID)
            ->getQuery()
            ->getSingleResult();
        if(isset($album))
        {
            return true;
        }

        return false;
    }

    public function saveNewSharePlace($data, $dm)
    {
        $createdTime = $data['timestamp'];
        $imagePath = $data['imagePath'];

        $actionID = 'ACT'.$createdTime;
        $actionUser = $actionLocation = $data['pageID'];
        $actionType = 'PLA'.$actionUser.$createdTime;

        $placeID = $actionType;
        $placeName = $data['placeName'];
        $placeDescipt = $data['placeDescript'];
        $hashtag = $data['hashtag'];

        $albumID = 'ALB'.$actionUser.'NOR';
        $imageID = substr($imagePath, 0, 30);
        $imageType = substr($imagePath, -3, 3);
        $imageStatus = "NOR";

        $action = $dm->createQueryBuilder('Application\Document\Action')
            ->insert()
            ->field('actionid')->set($actionID)
            ->field('actionuser')->set($actionUser)
            ->field('actionlocation')->set($actionLocation)
            ->field('actiontype')->set($actionType)
            ->field('createdtime')->set($createdTime)
            ->getQuery()
            ->execute();

        if(isset($action))
        {
            $rs = $this->checkExistsAlbum($albumID, $actionUser, $dm);
            if(!$rs)
            {
                $album = $dm->createQueryBuilder('Application\Document\Album')
                    ->insert()
                    ->field('albumid')->set($albumID)
                    ->field('userid')->set($actionUser)
                    ->getQuery()
                    ->execute();

                if(!isset($album))
                {
                    return false;
                }
            }
            $image = $dm->createQueryBuilder('Application\Document\Image')
                ->insert()
                ->field('imageid')->set($imageID)
                ->field('albumid')->set($albumID)
                ->field('imagestatus')->set($imageStatus)
                ->field('imagetype')->set($imageType)
                ->getQuery()
                ->execute();

            $place = $dm->createQueryBuilder('Application\Document\Place')
                ->insert()
                ->field('placeid')->set($placeID)
                ->field('placename')->set($placeName)
                ->field('placedescription')->set($placeDescipt)
                ->field('imageid')->set($imageID)
                ->field('hashtag')->set($hashtag)
                ->getQuery()
                ->execute();
            if(isset($place))
            {
                return true;
            }
        }
        return false;
    }

    public function saveNewVideo($data, $dm)
    {
        $createdTime = $data['timestamp'];
        $videoPath = $data['videoPath'];

        $actionID = 'ACT'.$createdTime;
        $actionUser = $actionLocation = $data['pageID'];
        $actionType = 'VID'.$actionUser.$createdTime;

        $videoID = $actionType;
        $videoDescipt = $data['descript'];
        $albumID = 'ALB'.$actionUser.'VID';
        $videoType = substr($videoPath, -3, 3);

        $action = $dm->createQueryBuilder('Application\Document\Action')
            ->insert()
            ->field('actionid')->set($actionID)
            ->field('actionuser')->set($actionUser)
            ->field('actionlocation')->set($actionLocation)
            ->field('actiontype')->set($actionType)
            ->field('createdtime')->set($createdTime)
            ->getQuery()
            ->execute();

        if(isset($action))
        {
            $rs = $this->checkExistsAlbum($albumID, $actionUser, $dm);
            if(!$rs)
            {
                $album = $dm->createQueryBuilder('Application\Document\Album')
                    ->insert()
                    ->field('albumid')->set($albumID)
                    ->field('userid')->set($actionUser)
                    ->getQuery()
                    ->execute();

                if(!isset($album))
                {
                    return false;
                }
            }
            $video = $dm->createQueryBuilder('Application\Document\Video')
                ->insert()
                ->field('videoid')->set($videoID)
                ->field('albumid')->set($albumID)
                ->field('videotype')->set($videoType)
                ->field('videodescription')->set($videoDescipt)
                ->getQuery()
                ->execute();

            if(isset($video))
            {
                return true;
            }
        }

        return false;
    }

    public function saveNewImagePage($data, $dm)
    {
        $createdTime = $data['timestamp'];
        $imagePath = $data['imagePath'];

        $actionID = 'ACT'.$createdTime;
        $actionUser = $actionLocation = $data['pageID'];
        $actionType = 'IMG'.$actionUser.$createdTime.'NOR';

        $imageID = $actionType;
        $imageDescipt = $data['descript'];
        $imageStatus = 'NOR';
        $albumID = 'ALB'.$actionUser.'NOR';
        $imageType = substr($imagePath, -3, 3);

        $action = $dm->createQueryBuilder('Application\Document\Action')
            ->insert()
            ->field('actionid')->set($actionID)
            ->field('actionuser')->set($actionUser)
            ->field('actionlocation')->set($actionLocation)
            ->field('actiontype')->set($actionType)
            ->field('createdtime')->set($createdTime)
            ->getQuery()
            ->execute();

        if(isset($action))
        {
            $rs = $this->checkExistsAlbum($albumID, $actionUser, $dm);
            if(!$rs)
            {
                $album = $dm->createQueryBuilder('Application\Document\Album')
                    ->insert()
                    ->field('albumid')->set($albumID)
                    ->field('userid')->set($actionUser)
                    ->getQuery()
                    ->execute();

                if(!isset($album))
                {
                    return false;
                }
            }

            $image = $dm->createQueryBuilder('Application\Document\Image')
                ->insert()
                ->field('imageid')->set($imageID)
                ->field('albumid')->set($albumID)
                ->field('imagestatus')->set($imageStatus)
                ->field('imagetype')->set($imageType)
                ->field('imagedescription')->set($imageDescipt)
                ->getQuery()
                ->execute();

            if(isset($image))
            {
                return true;
            }
        }

        return false;

    }

    public function loadStatusUseronPageWall($pageID, $dm)
    {
        $binding = null;

        $action = $dm->createQueryBuilder('Application\Document\Action')
            ->select()
            ->field('actionlocation')->equals($pageID)
            ->field('actionuser')->notEqual($pageID)
            ->getQuery()
            ->execute();

        if(isset($action))
        {
            foreach($action as $node)
            {
                $actionType = $node->getActiontype();
                if(substr($actionType, 0, 3) == "STT")
                {
                    $userID = $node->getActionuser();
                    $albumID = 'ALB'.$userID.'AVA';
                    $pathAva = 'ava-temp.png';
                    $status = $dm->createQueryBuilder('Application\Document\Status')
                        ->select()
                        ->field('statusid')->equals($actionType)
                        ->getQuery()
                        ->getSingleResult();
                    $user = $dm->createQueryBuilder('Application\Document\User')
                        ->select()
                        ->field('userid')->equals($userID)
                        ->getQuery()
                        ->getSingleResult();
                    $imageAva = $dm->createQueryBuilder('Application\Document\Image')
                        ->select()
                        ->field('albumid')->equals($albumID)
                        ->field('imagestatus')->equals('AVA_NOW')
                        ->getQuery()
                        ->getSingleResult();


                    if(isset($status) && isset($user))
                    {
                        $statusContent = $status->getStatuscontent();
                        $username = $user->getLastname().' '.$user->getFirstname();
                    }
                    if(isset($imageAva))
                    {
                        $pathAva = $imageAva->getImageid().'.'.$imageAva->getImagetype();
                    }

                    $binding[] = array(
                        'statusid'      => $actionType,
                        'statuscontent' => $statusContent,
                        'createdtime'   => $node->getCreatedtime(),
                        'actionuser'    => $node->getActionuser(),
                        'username'      => $username,
                        'pathava'       => $pathAva,
                    );
                }
            }
        }
        return $binding;
    }

    public function loadCommentbyActionID($data, $dm)
    {
        $binding = null;
        $listCMTID = null;
//        $pageID   = $data['actionLocation'];
        $actionID = $data['actionID'];
        $checkUser = $data['checkUser'];

        $comment = $dm->createQueryBuilder('Application\Document\Comment')
            ->select()
            ->field('actionid')->equals($actionID)
            ->getQuery()
            ->execute();

        if(isset($comment))
        {
            $i = 0;
            foreach($comment as $node)
            {
                $listCMTID[] = $node->getCommentid();
                $actionType = $node->getCommentid();

                $action = $dm->createQueryBuilder('Application\Document\Action')
                    ->select()
                    ->field('actiontype')->equals($actionType)
                    ->getQuery()
                    ->getSingleResult();

                if(isset($action))
                {
                    $actionUser = $action->getActionuser();
                    $albID = 'ALB'.$actionUser.'AVA';

                    $imgAva = $dm->createQueryBuilder('Application\Document\Image')
                        ->select()
                        ->field('albumid')->equals($albID)
                        ->field('imagestatus')->equals('AVA_NOW')
                        ->getQuery()
                        ->getSingleResult();

                    if(substr($actionUser, 0, 4) == 'user')
                    {
                        if(isset($imgAva))
                        {
                            $avaUser = $imgAva->getImageid().'.'.$imgAva->getImagetype();
                        }
                        else
                        {
                            $avaUser = 'ava-temp.png';
                        }

                        $user = $dm->createQueryBuilder('Application\Document\User')
                            ->select()
                            ->field('userid')->equals($actionUser)
                            ->getQuery()
                            ->getSingleResult();
                        if(isset($user))
                        {
                            $nameUser = $user->getLastname().' '.$user->getFirstname();
                        }
                    }
                    else
                    {
                        if(isset($imgAva))
                        {
                            $avaUser = $imgAva->getImageid().'.'.$imgAva->getImagetype();
                        }
                        else
                        {
                            $avaUser = 'ava-page-temp.png';
                        }

                        $page = $dm->createQueryBuilder('Application\Document\Fanpage')
                            ->select()
                            ->field('pageid')->equals($actionUser)
                            ->getQuery()
                            ->getSingleResult();
                        if(isset($page))
                        {
                            $nameUser = $page->getPagename();
                        }
                    }

                    $binding[] = array(
                        'count'          => $i,
                        'commentid'      => $actionType,
                        'commentcontent' => $node->getCommentcontent(),
                        'actionuser'     => $actionUser,
                        'nameuser'       => $nameUser,
                        'avauser'        => $avaUser,
                        'createdtime'    => substr($actionType, -10, 10),
                    );

                    $i++;

                }

            }
        }

        //load list like of status
        $like = $dm->createQueryBuilder('Application\Document\Like')
            ->select()
            ->field('actionid')->equals($actionID)
            ->getQuery()
            ->execute();

        $countLiked=0;
        $arrLike = array();
        $arrLike['userLiked'] = false;
        $arrLike['countLiked'] = 0;
        $arrLike['likeid'] = null;
        if(isset($like))
        {
            foreach($like as $node)
            {
                $countLiked++;
                $checkLiked = $dm->createQueryBuilder('Application\Document\Action')
                    ->select()
                    ->field('actiontype')->equals($node->getLikeid())
                    ->field('actionuser')->equals($checkUser)
                    ->getQuery()
                    ->getSingleResult();

                if(isset($checkLiked))
                {
                    $arrLike['userLiked'] = true;
                    $arrLike['likeid'] = $checkLiked->getActionType();
                }

                $arrLike['countLiked'] = $countLiked;
            }
        }

        return array(
            'binding' =>  $binding,
            'arrLike' => $arrLike,
        );
    }

    public function getPathAvatarofPageorUser($ID, $dm)
    {
        $albumID = 'ALB'.$ID.'AVA';
        $imageID = 'ava-temp.png';
        $arr = array();
        $imageAva = $dm->createQueryBuilder('Application\Document\Image')
            ->select()
            ->field('albumid')->equals($albumID)
            ->field('imagestatus')->equals('AVA_NOW')
            ->getQuery()
            ->getSingleResult();

        if(isset($imageAva))
        {
            $imageID = $imageAva->getImageid().'.'.$imageAva->getImagetype();

        }

        if(substr($ID, 0, 4) == 'user')
        {
            $user = $dm->createQueryBuilder('Application\Document\User')
                ->select()
                ->field('userid')->equals($ID)
                ->getQuery()
                ->getSingleResult();

            if(isset($user)){
                $arr['name'] = $user->getLastname().' '.$user->getFirstname();
            }

        }

        $arr['pathAva'] = $imageID;
        return $arr;
    }

    public function insertNewComment($data, $dm)
    {
        //action
        $createTime = $data['createdTime'];
        $actionID   = 'ACT'.$createTime;
        $actionUser   = $data['actionUser'];
        $actionLocation   = $data['actionLocation'];
        $actionType = 'CMT'.$actionUser.$createTime;

        //comment
        $cmtID       = $actionType;
        $cmtActionID = $data['actionID'];
        $cmtContent  = $data['cmtContent'];

        $action = $dm->createQueryBuilder('Application\Document\Action')
            ->insert()
            ->field('actionid')->set($actionID)
            ->field('actionuser')->set($actionUser)
            ->field('actionlocation')->set($actionLocation)
            ->field('actiontype')->set($actionType)
            ->field('createdtime')->set($createTime)
            ->getQuery()
            ->execute();

        $comment = $dm->createQueryBuilder('Application\Document\Comment')
            ->insert()
            ->field('commentid')->set($cmtID)
            ->field('actionid')->set($cmtActionID)
            ->field('commentcontent')->set($cmtContent)
            ->getQuery()
            ->execute();

        if(isset($action) && isset($comment)){
            return true;
        }else{
            return false;
        }
    }

    public function getDataIndexFanpage($pageID, $dm)
    {
        $bindingIndex = null;
        $action = $dm->createQueryBuilder('Application\Document\Action')
            ->select()
            ->field('actionuser')->equals($pageID)
            ->field('actionlocation')->equals($pageID)
            ->getQuery()
            ->execute();

        if(isset($action))
        {
            foreach($action as $node)
            {
                $arr = array();
                $actType = $node->getActionType();
                $check = substr($actType, 0, 3);
                if($check == "STT")
                {
                    $status = $dm->createQueryBuilder('Application\Document\Status')
                        ->select()
                        ->field('statusid')->equals($actType)
                        ->getQuery()
                        ->getSingleResult();
                    if(isset($status))
                    {
                        $arr = array(
                            'id'     => $status->getStatusid(),
                            'source' => $status->getStatusContent(),
                            'type'   => 'addStatus',
                        );
                    }

                }
                elseif($check == "IMG")
                {
                    $image = $dm->createQueryBuilder('Application\Document\Image')
                        ->select()
                        ->field('imageid')->equals($actType)
                        ->getQuery()
                        ->getSingleResult();

                    if(isset($image))
                    {
                        $imgID = $image->getImageid();
                        $typeImage = substr($imgID, -3, 3);
                        if($typeImage == 'SLI'){
                            $typeImage = "changeSlide";
                        }elseif($typeImage == "NOR"){
                            $typeImage = "addImage";
                        }elseif($typeImage == "OGO"){
                            $typeImage = "changeLogo";
                        }elseif($typeImage == "AVA"){
                            $typeImage = "changeAva";
                        }

                        $arr = array(
                            'id'     => $imgID,
                            'source' => $imgID.'.'.$image->getImagetype(),
                            'descript' => $image->getImagedescription(),
                            'type'   => $typeImage,
                        );
//                        var_dump($arr);die();
                    }
                }
                elseif($check == 'VID')
                {
                    $video = $dm->createQueryBuilder('Application\Document\Video')
                        ->select()
                        ->field('videoid')->equals($actType)
                        ->getQuery()
                        ->getSingleResult();
                    if(isset($video))
                    {
                        $vID = $video->getVideoid();
                        $arr = array(
                            'id'       => $vID,
                            'source'   => $vID.'.'.$video->getVideotype(),
                            'descript' => $video->getVideodescription(),
                            'type'     => 'addVideo',
                        );
                    }
                }
                elseif($check=='PLA')
                {
                    $place = $dm->createQueryBuilder('Application\Document\Place')
                        ->select()
                        ->field('placeid')->equals($actType)
                        ->getQuery()
                        ->getSingleResult();

                    if(isset($place))
                    {
                        $imageIDPlace = $place->getImageid();
                        $imgPla = $dm->createQueryBuilder('Application\Document\Image')
                            ->select()
                            ->field('imageid')->equals($imageIDPlace)
                            ->getQuery()
                            ->getSingleResult();
                        if(isset($imgPla))
                        {
                            $srcPlace = $imgPla->getImageid().'.'.$imgPla->getImageType();
                        }

                        $desPlace = $place->getPlacename().'<br>';
                        $desPlace .= $place->getHashtag().'<br>';
                        $desPlace .= $place->getPlacedescription().'<br>';

                        $arr = array(
                            'id'       => $place->getPlaceid(),
                            'source'   => $srcPlace,
                            'descript' => $desPlace,
                            'type'     => 'addSharePlace',
                        );
                    }
                }

                if($arr != null)
                {
                    $bindingIndex[]   =  array(
                        'actionid'    => $node->getActionid(),
                        'createdtime' => $node->getCreatedTime(),
                        'content'     => $arr,
                    );
                }

            }
        }

        return $bindingIndex;
    }

    public function insertStatusPage($data, $dm)
    {
        $createdTime = $data['createdTime'];
        $actionID = 'ACT'.$createdTime;
        $actionUser = $data['userNow'];
        $actionLocation = $data['pageID'];
        $actionType = 'STT'.$actionUser.$createdTime;
        //status
        $statusID = $actionType;
        $statusContent = $data['statusContent'];

        $action = $dm->createQueryBuilder('Application\Document\Action')
            ->insert()
            ->field('actionid')->set($actionID)
            ->field('actionuser')->set($actionUser)
            ->field('actionlocation')->set($actionLocation)
            ->field('actiontype')->set($actionType)
            ->field('createdtime')->set($createdTime)
            ->getQuery()
            ->execute();

        $status = $dm->createQueryBuilder('Application\Document\Status')
            ->insert()
            ->field('statusid')->set($statusID)
            ->field('statuscontent')->set($statusContent)
            ->getQuery()
            ->execute();

        if(isset($action) && isset($status)){
            return true;
        }else{
            return false;
        }
    }

    public function checkExistsPage($pageID, $dm)
    {
        $check = false;

        $page = $dm->createQueryBuilder('Application\Document\Fanpage')
            ->select()
            ->field('pageid')->equals($pageID)
            ->getQuery()
            ->getSingleResult();

        if(isset($page))
        {
            $check = true;
        }
        return $check;
    }

    public function checkUserOwnerPage($userID, $pageID, $dm)
    {
        $check = null;
        $page = $dm->createQueryBuilder('Application\Document\FanpageManage')
            ->select()
            ->field('pageid')->equals($pageID)
            ->field('userid')->equals($userID)
            ->getQuery()
            ->getSingleResult();

        if(isset($page))
        {
            $check = $page->getPageuserstatus();
        }

        return $check;
    }

    public function bindingDataPageIndex($userID, $pageID, $dm)
    {
        $bindingInfo = null;
        $bindingImage = null;

        $page = $dm->createQueryBuilder('Application\Document\Fanpage')
            ->select()
            ->field('pageid')->equals($pageID)
//            ->field('userid')->equals($userID)
            ->getQuery()
            ->getSingleResult();

        $countLike = $dm->createQueryBuilder('Application\Document\FanpageManage')
            ->select()
            ->field('pageid')->equals($pageID)
//            ->field('userid')->equals($userID)
            ->field('pageuserstatus')->equals('LIKE')
            ->getQuery()
            ->execute()->count();

        if(isset($page))
        {
            $bindingInfo = array(
                'pageName' => $page->getPagename(),
                'pageDes'  => $page->getPagedescription(),
                'pageMapInfo' => $page->getPagemapinfo(),
                'pageLongt'=> $page->getPagelongtitude(),
                'pageLat'  => $page->getPagelatitude(),
                'pageOwner'=> $page->getUserid(),
                'pageID'   => $page->getPageid(),
                'pageLike' => $countLike,
            );
        }

        $albumLogo = 'ALB'.$pageID.'LOGO';
        $albumAva  = 'ALB'.$pageID.'AVA';
        $albumSlide = 'ALB'.$pageID.'SLI';
        $avaTemp   = 'ava-page-temp.png';
        $logoTemp   = 'logo-temp.png';
        $slideShowTemp = array('slide-temp-1.jpg','slide-temp-2.jpg','slide-temp-3.jpg');

        $imageLogo = $dm->createQueryBuilder('Application\Document\Image')
            ->select()
            ->field('albumid')->equals($albumLogo)
            ->field('imagestatus')->equals('LOGO_NOW')
            ->getQuery()
            ->getSingleResult();

        if(isset($imageLogo))
        {
            $logoTemp = $imageLogo->getImageid().'.'.$imageLogo->getImagetype();
        }

        $imageAva = $dm->createQueryBuilder('Application\Document\Image')
            ->select()
            ->field('albumid')->equals($albumAva)
            ->field('imagestatus')->equals('AVA_NOW')
            ->getQuery()
            ->getSingleResult();

        if(isset($imageAva))
        {
            $avaTemp = $imageAva->getImageid().'.'.$imageAva->getImagetype();
        }

        $slideShow = $dm->createQueryBuilder('Application\Document\Image')
            ->select()
            ->field('albumid')->equals($albumSlide)
            ->field('imagestatus')->equals('SLIDESHOW')
            ->getQuery()
            ->execute();

        if(isset($slideShow))
        {
            foreach($slideShow as $node)
            {
                $slideShowTemp[] =  $node->getImageid().'.'.$node->getImagetype();
            }
        }

        $bindingImage = array(
            'imageAva'  => $avaTemp,
            'imageLogo' => $logoTemp,
            'imageSlideShow' => $slideShowTemp,
        );

//        var_dump($slideShowTemp);die();

        return array(
            'bindingInfo'  => $bindingInfo,
            'bindingImage' => $bindingImage,
        );
    }

    public static function getDataImageBindingEffect($userID, $pageID, $dm)
    {
        $albumMainID = 'ALB'.$pageID.'MAIN';

        $name = null;
        $description = null;
        $photosArr = null;
        $listAlbumID = null;
        $extraArray = null;

        $doc = $dm->createQueryBuilder('Application\Document\Album')
            ->select()
            ->field('albumid')->equals($albumMainID)
            ->getQuery()
            ->getSingleResult();
        if(isset($doc))
        {
            $name = $doc->getAlbumname();
            $description = $doc->getAlbumdescription();
        }
        else
            return $extraArray;

        $albums = $dm->createQueryBuilder('Application\Document\Album')
            ->select()
            ->field('userid')->equals($pageID)
            ->getQuery()
            ->execute();

        if(isset($albums))
        {
            foreach($albums as $node)
            {
                $idTemp = $node->getAlbumid();
                if(substr($idTemp, -17, 7) == 'SUBMAIN')
                {
                    $listAlbumID[] = array(
                        'id' => $idTemp,
                        'albumName'=> $node->getAlbumname(),
                        'albumDescript' =>$node->getAlbumdescription(),
                        'albumLat' => $node->getAlbumlatitude(),
                        'albumLong'=> $node->getAlbumlongtitude(),

                    );
                }
            }
        }

        foreach($listAlbumID as $node)
        {
            $image = $dm->createQueryBuilder('Application\Document\Image')
                ->select()
                ->field('albumid')->equals($node['id'])
                ->getQuery()
                ->execute();

            $photosArr = array();
            $photosArr['name'] = $node['albumName'];
            $photosArr['lat'] = floatval($node['albumLat']);
            $photosArr['lng'] = floatval($node['albumLong']);

            if(isset($image))
            {
                $i = 0;
                foreach($image as $img)
                {
                    $photosArr['photos'][] =  array(
                        'index'       => $i,
                        'thumb'       => 'uploads/'.$img->getImageid().'.'.$img->getImagetype(),
                        'source'      => 'uploads/'.$img->getImageid().'.'.$img->getImagetype(),
                        'description' => $img->getImagedescription(),
                        'lat'         => floatval($img->getImagelatitude()),
                        'lng'         => floatval($img->getImagelongtitude()),
                    );
                    $i++;
                }
            }
            $placesArr[] = $photosArr;
        }


        $extraArray = array(
            'name'        => $name,
            'description' => $description,
            'places'      => $placesArr,
        );

        return $extraArray;

    }
}