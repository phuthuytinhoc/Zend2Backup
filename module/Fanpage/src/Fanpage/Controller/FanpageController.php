<?php
/**
 * Created by JetBrains PhpStorm.
 * User: FUHU
 * Date: 12/28/13
 * Time: 10:40 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Fanpage\Controller;

use Fanpage\Model\FanpageModel;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Helper\Json;

class FanpageController extends AbstractActionController
{
    public function getAuthenService()
    {
        $authen = $this->getServiceLocator()->get('doctrine.authenticationservice.odm_default');
        return $authen;
    }

    public function getDocumentService()
    {
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
        return $dm;
    }

    public function getUserIdentity()
    {
        $result = $this->getAuthenService();
        if($result->hasIdentity())
            return $result->getIdentity();

        return null;
    }

    public function indexAction()
    {
        $result = $this->getAuthenService();
        if(!$result->hasIdentity())
        {
            return $this->redirect()->toRoute('home');
        }

        $layout = $this->layout();
        $layout->setTemplate('layout/fanpage');

        $fanpageModel = new FanpageModel();
        $urlAlbum = $this->url()->fromRoute('home').'xml/album.xml';
//        $result = $fanpageModel->getPlaces($urlAlbum);

//        var_dump($result);die();

        return array();
    }

    public function editinfoAction()
    {
        $result = $this->getAuthenService();
        if(!$result->hasIdentity())
        {
            return $this->redirect()->toRoute('home');
        }

        $layout = $this->layout();
        $layout->setTemplate('layout/fanpage-config');

        $result = new ViewModel();
        $result->setTemplate('fanpage/fanpage/editinfo');

        $dm = $this->getDocumentService();
        $userid = $this->getUserIdentity()->getUserid();
        $fanpageModel = new FanpageModel();

        //get PageID && get UserID owner
        $values = $fanpageModel->getFanpageInfoByUserID($userid, $dm);
        //get image path
        $imagePath = $fanpageModel->getBindingInfo($values['pageid'], $dm);
//        var_dump($values);die();

        $result->setVariables(array(
            'pageInfo' => $values,
            'bindingInfo' => $imagePath,
        ));

//        var_dump($fanpageModel->checkExistsAlbumofPage('page1388716637', 'LOGO', $dm));die();

        return $result;
    }

    //FUNCTION FOR AJAX

    public function getalbumpageAction()
    {
        $response = $this->getResponse();

        return $response->setContent(\Zend\Json\Json::encode(array(
            'success' => 1,
            )));
    }

    public function savepageimageAction()
    {
        $response = $this->getResponse();

        $data = $this->params()->fromPost();
        $dm = $this->getDocumentService();
        $fanpageModel = new FanpageModel();

        $result = $fanpageModel->savePageImage($data, $dm);

        if($result){
            return $response->setContent(\Zend\Json\Json::encode(array(
                'success' => 1,
            )));
        }else{
            return $response->setContent(\Zend\Json\Json::encode(array(
                'success' => 0,
            )));
        }
    }
}