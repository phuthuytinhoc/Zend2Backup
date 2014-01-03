<?php
/**
 * Created by JetBrains PhpStorm.
 * User: FUHU
 * Date: 1/2/14
 * Time: 11:37 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Userpage\Model;

use Application\Document;

class CreatePageModel
{
    public function getTimestampNow()
    {
        $date = new \DateTime(null, new \DateTimeZone('Asia/Ho_Chi_Minh'));
        return $date->getTimestamp();
    }

    public function getListPageofUser($userid, $dm)
    {

    }

    public function createNewFanpage($data, $userid, $dm)
    {
        //FANPAGE
        $createdTime = $this->getTimestampNow();
        $pageID = 'page'.$createdTime;
        $pageName =  $data['pageName'];
        $pageType = $data['pageType'];
        $pageDescription =  $data['pageDescription'];

        //FANPAGE MANAGE
        $pageuserstatus = "ADMIN";

        $document = $dm->createQueryBuilder('Application\Document\Fanpage')
            ->insert()
            ->field('pageid')->set($pageID)
            ->field('pagetype')->set($pageType)
            ->field('pagename')->set($pageName)
            ->field('pagedescription')->set($pageDescription)
            ->field('userid')->set($userid)
            ->field('createdtime')->set($createdTime)
            ->getQuery()
            ->execute();

        $doc = $dm->createQueryBuilder('Application\Document\FanpageManage')
            ->insert()
            ->field('pageid')->set($pageID)
            ->field('userid')->set($userid)
            ->field('pageuserstatus')->set($pageuserstatus)
            ->getQuery()
            ->execute();

        if(isset($document) && isset($doc))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}