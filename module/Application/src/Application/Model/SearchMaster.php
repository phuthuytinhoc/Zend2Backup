<?php
/**
 * Created by JetBrains PhpStorm.
 * User: FUHU
 * Date: 2/18/14
 * Time: 7:50 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Application\Model;

class SearchMaster
{
    public function utf8ToAscii($str) {
        $chars = array(
            'a'    =>    array('ấ','ầ','ẩ','ẫ','ậ','Ấ','Ầ','Ẩ','Ẫ','Ậ','ắ','ằ','ẳ','ẵ','ặ','Ắ','Ằ','Ẳ','Ẵ','Ặ','á','à','ả','ã','ạ','â','ă','Á','À','Ả','Ã','Ạ','Â','Ă'),
            'e' =>    array('ế','ề','ể','ễ','ệ','Ế','Ề','Ể','Ễ','Ệ','é','è','ẻ','ẽ','ẹ','ê','É','È','Ẻ','Ẽ','Ẹ','Ê'),
            'i'    =>    array('í','ì','ỉ','ĩ','ị','Í','Ì','Ỉ','Ĩ','Ị'),
            'o'    =>    array('ố','ồ','ổ','ỗ','ộ','Ố','Ồ','Ổ','Ô','Ộ','ớ','ờ','ở','ỡ','ợ','Ớ','Ờ','Ở','Ỡ','Ợ','ó','ò','ỏ','õ','ọ','ô','ơ','Ó','Ò','Ỏ','Õ','Ọ','Ô','Ơ'),
            'u'    =>    array('ứ','ừ','ử','ữ','ự','Ứ','Ừ','Ử','Ữ','Ự','ú','ù','ủ','ũ','ụ','ư','Ú','Ù','Ủ','Ũ','Ụ','Ư'),
            'y'    =>    array('ý','ỳ','ỷ','ỹ','ỵ','Ý','Ỳ','Ỷ','Ỹ','Ỵ'),
            'd'    =>    array( "đ" ,'Đ'),
        );
        foreach ($chars as $key => $arr)
            foreach ($arr as $val)
                $str = str_replace($val,$key,$str);
        return trim(strtolower($str));
    }

    public function searchEverything($data, $dm)
    {
        $keywords = $this->utf8ToAscii($data['searchContent']);

        if($keywords == "" or $keywords == null)
        {
            return null;
        }

//        $keywords = 'a';

        $arr = $dm->createQueryBuilder('Application\Document\User')
            ->getQuery()
            ->execute();

        $arr_page = $dm->createQueryBuilder('Application\Document\Fanpage')
            ->getQuery()
            ->execute();

        //mang chua danh sach ten cua nguoi dung + ID
        $arrFullUser = array();
        //mang chua danh sach ten cua page + ID page
        $arrFullPage = array();

        if(isset($arr))
        {
            foreach($arr as $user)
            {
                $arrFullUser[] = array(
                    'id'   => $user->getUserid(),
                    'name' => $user->getLastname().' '.$user->getFirstname(),
                );
            }
        }

        if(isset($arr_page))
        {
            foreach($arr_page as $page)
            {
                $arrFullPage[] = array(
                    'id'   => $page->getPageid(),
                    'name' => $page->getPagename(),
                );
            }
        }

        $page  = $this->collectResult($arrFullPage, $keywords, $dm);
        $user  = $this->collectResult($arrFullUser, $keywords, $dm);

//        var_dump($user);die();

        return array(
            'page' => $page,
            'user' => $user,
        );
    }

    ///////////////start collect result///////////////////
    public function collectResult($array, $keywords, $dm)
    {
        $result = array();
        foreach($array as $node)
        {
            $this->search($this->utf8ToAscii($node['name']), $keywords);
            $result[] = array(
                'id'   => $node['id'],
                'data' => explode(' ',$this->matches),
            );
        }

        $maxScore = 0;
        $scoreArr = array();
        $finalResult = array();

        //calculate ponts of each ID
        foreach($result as $node)
        {
            foreach($node['data'] as $value)
            {
                if(isset($node['score'])){
                    $score = $node['score'];
                }else{
                    $score = 0;
                }

                if(strlen($value) > 0)
                {
                    $score++;
                    if($score > $maxScore)
                        $maxScore = $score;
                    $node['score'] = $score;
                }
            }
            //count like
            $count = $dm->createQueryBuilder('Application\Document\FanpageManage')
                ->field('pageid')->equals($node['id'])
                ->field('pageuserstatus')->equals('LIKE')
                ->getQuery()
                ->execute()->count();
            if(!isset($count))
                $count = 0;
            $scoreArr[$node['id']] = array(
                'score' => $score,
                'count' => $count,
            );
        }
        //sort array by score
        //sap xep mang theo diem chinh va diem phu
        arsort($scoreArr);

        //find best result by scores
        if($maxScore > 0)
        {
            $count = 0;
            foreach($scoreArr as $key=>$point)
            {
                $count++;
                if($point['score'] == $maxScore)
                {
                    $finalResult['first'][] = array(
                        'id' => $key,
                        'data' => $this->getInfoResult($key, $dm),
                    );
                }
                elseif($point['score'] < $maxScore)
                {
                    $finalResult['second'][] = array(
                        'id' => $key,
                        'data' => $this->getInfoResult($key, $dm),
                    );
                }
            }
        }

        return $finalResult;
    }

    public function getInfoResult($id, $dm)
    {
        $array = array();
        if(substr($id, 0, 4) == 'user')
        {
            $user = $dm->createQueryBuilder('Application\Document\User')
                ->field('userid')->equals($id)
                ->getQuery()
                ->getSingleResult();
            $countSent = $dm->createQueryBuilder('Application\Document\Friend')
                ->field('friendusersend')->equals($id)
                ->field('friendstatus')->equals('ACCEPTED')
                ->getQuery()
                ->execute()->count();
            $countRecieve = $dm->createQueryBuilder('Application\Document\Friend')
                ->field('frienduserrecieve')->equals($id)
                ->field('friendstatus')->equals('ACCEPTED')
                ->getQuery()
                ->execute()->count();

            if(!isset($countSent))
                $countSent =0;
            if(!isset($countRecieve))
                $countRecieve =0;
            $countFriend = $countRecieve + $countSent;
            if(isset($user))
            {
                $array = array(
                    'fullname' => $user->getLastname().' '.$user->getFirstname(),
                    'link'     => '/success?user='.$user->getUserid(),
                    'ava'      => $this->getPathImage($user->getUserid(), 'user', $dm),
                    'count'    => $countFriend.' bạn bè',
                );
            }
        }
        else
        {
            $page = $dm->createQueryBuilder('Application\Document\Fanpage')
                ->field('pageid')->equals($id)
                ->getQuery()
                ->getSingleResult();
            $count = $dm->createQueryBuilder('Application\Document\FanpageManage')
                ->field('pageid')->equals($id)
                ->field('pageuserstatus')->equals('LIKE')
                ->getQuery()
                ->execute()->count();

            if(!isset($count))
                $count = 0;
            if(isset($page))
            {
                $array = array(
                    'fullname' => $page->getPagename(),
                    'link'     => '/fanpage?pageID='.$page->getPageid(),
                    'ava'      => $this->getPathImage($page->getPageid(), 'page', $dm),
                    'count'    => $count.' lượt thích',
                );
            }
        }

        return $array;

    }

    public function getPathImage($id, $type, $dm)
    {
        $albumid = 'ALB'.$id.'AVA';
        $image = $dm->createQueryBuilder('Application\Document\Image')
            ->field('albumid')->equals($albumid)
            ->field('imagestatus')->equals('AVA_NOW')
            ->getQuery()
            ->getSingleResult();
        if($type == 'user')
        {
            $string = 'ava-temp.png';
        }
        else
        {
            $string = 'ava-page-temp.png';
        }

        if(isset($image))
        {
            $string = $image->getImageid().'.'.$image->getImagetype();
        }

        return '/uploads/'.$string;

    }

    ///////////////end collect result/////////////////////



    //////////////////////////START KMP - ALGORITHRM/////////////////////////
    public $p;
    public $t;
    public $m, $n;
    public $matches;
    public $showmatches;
    public $b;

    /*
    * searches the text tt for the pattern pp
    */
    public function search($tt, $pp)
    {
        $this->setText($tt);
        $this->setPattern($pp);
        $this->kmpSearch();
    }

    /*
    * sets the text
    */
    public function setText($tt)
    {
        $this->n = strlen ($tt);
        $this->t = str_split($tt);
        $this->initmatches();
    }

    /*
    * sets the pattern
    */
    public function setPattern($pp)
    {

        $this->m = strlen($pp);
        $this->p = str_split($pp);
        $this->b = [];
        $this->kmpPreprocess();
    }

    /*
    * initializes match positions and the array showmatches
    */
    public function initmatches()
    {
        $this->matches="";
        $this->showmatches = array();
        for ($i = 0; $i < $this->n; $i++)
            $this->showmatches[$i]= "";
    }

    /*
    * preprocessing of the pattern
    */
    public function kmpPreprocess()
    {
        $i = 0;
        $j = -1;
        $this->b[$i] = $j;

        while ($i < $this->m)
        {
            while ($j >= 0 && $this->p[$i] != $this->p[$j])
                $j=$this->b[$j];
            $i++;
            $j++;
            $this->b[$i]=$j;
        }
    }

    /*
    * searches the text for all occurences of the pattern
    */
    public function kmpSearch()
    {
        $i=0;
        $j=0;
        while ($i < $this->n)
        {
            while ($j>=0 && $this->t[$i]!=$this->p[$j])
                $j=$this->b[$j];
            $i++;
            $j++;
            if ($j==$this->m) // a match is found
            {
                $this->report($i-$this->m);
                $j=$this->b[$j];
            }
        }
    }

    /*
    * reports a match
    */
    public function report($i)
    {
        $this->matches .= $i." ";
        $this->showmatches[$i]='+';
    }

    /*
    * returns the match positions after the search
    */
    public function getMatches()
    {
        return $this->matches;
    }

    //////////////////////////END KMP - ALGORITHRM/////////////////////////





}