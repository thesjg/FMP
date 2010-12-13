<?php

require_once 'Zend/Controller/Action.php';

class MediaController extends Zend_Controller_Action {
    
    private $identity;
    private $formData;
    
    public function init() {
        $auth = Zend_Auth::getInstance();
        $this->identity = $auth->getIdentity();
        $this->userid = $this->identity['id'];
        if (!$this->identity)
            $this->_redirect('/auth/login');

        $this->view->BaseUrl = $this->_request->getBaseUrl();
        $this->view->Controller = $this->_request->getParam('controller');
        $this->view->Path = $this->_request->getPathInfo();
        
        $this->formData = $this->_request->getPost();
    }
    
    public function indexAction() {

        $user_id = $this->identity['id'];
        
        $rowsPer = 15;
        $page = 1;
        
        // used as an enum - indexed in the url numerically, these reference the
        // actual sort options
        $order_options = array('', 'id', 'count', 'duration', 'size', 'timestamp');
        
        $sort = $this->_request->getParam('sort');

        if(!array_search($sort, $order_options))
            $sort = "timestamp";
        
        $asc_ind = $this->_request->getParam('asc');
    
        // boy I think you might be retarded!
        if ($asc_ind != 1)
            $asc = "DESC";
        else
            $asc = "ASC";
        
        if($this->_request->getParam('page') && $this->_request->getParam('page') > 0)
            $page = $this->_request->getParam('page');
        else
            $page = 1;
        
        $conn_string = "host='db.flixn.com' user='flixn' dbname='fmp_dev'";
        $db = pg_connect($conn_string);
        
        $count_query = sprintf("SELECT COUNT(*) AS counted FROM (SELECT m.id
                                FROM media m
                                LEFT JOIN media_video mv
                                ON (m.id = mv.media_id)
                                LEFT JOIN media_metadata_creation mmc
                                ON (m.id = mmc.media_id)
                                WHERE m.user_id = 1
                                AND mmc.timestamp IS NOT NULL
                                AND mv.size IS NOT NULL
                                GROUP BY m.media_id,
                                mmc.timestamp, m.id) AS count;");
        
        $result = pg_query($db, $count_query);
        
        $count = pg_fetch_result($result, 0, 0);
        
        if ($page > ceil($count/$rowsPer))
            $this->_redirect('/media/index/page/1/');
        
        $query_string = sprintf("SELECT m.media_id AS id, COUNT(mv) AS count,
                                (SUM(mv.duration)/COUNT(mv)) AS duration,
                                SUM(mv.size) AS size,
                                date_trunc('second', mmc.timestamp) AS timestamp
                                FROM media m
                                LEFT JOIN media_video mv
                                ON (m.id = mv.media_id)
                                LEFT JOIN media_metadata_creation mmc
                                ON (m.id = mmc.media_id)
                                WHERE m.user_id = %d
                                AND mmc.timestamp IS NOT NULL
                                AND mv.size IS NOT NULL
                                GROUP BY m.media_id, timestamp
                                ORDER BY %s %s
                                LIMIT %d
                                OFFSET %d;", $user_id, $sort, $asc, $rowsPer, (($page - 1) * $rowsPer));
        
        $result = pg_query($db, $query_string);
        
        $this->view->mediaRecords = pg_fetch_all($result);
        
        $this->view->urlData = array('count' => $count, 'rowsPer' => $rowsPer, 'page' => $page, 'sort' => $sort, 'asc' => $asc);

    }
    
    public function searchAction() {
        
        $user_id = $this->identity['id'];
        
        if($this->_request->isPost()) {
            
            
            
            
        }
        
    }
    
    public function viewAction()
    {
        
        $media_id = $this->_checkMedia();
        
        $this->view->media = $media = Doctrine::getTable('Medium')
                    ->findOneByMediaId($media_id);
                    
        $this->view->media_metadata = Doctrine::getTable('MediumMetadatum')
                        ->findByMediaId($media['id']);
                        
        $this->view->media_metadata_creation = Doctrine::getTable('MediumMetadatumCreation')
                        ->findOneByMediaId($media['id']);
                        
        $this->view->media_metadata_internal = Doctrine::getTable('MediumMetadatumInternal')
                        ->findByMediaId($media['id']);
                        
        $name_ex = Doctrine_Query::create()
                            ->select("value")
                            ->from("MediumMetadatum mm")
                            ->where("mm.media_id = ?", $media['id'])
                            ->addWhere("key = 'name'")
                            ->execute();
                            
        $desc_ex = Doctrine_Query::create()
                            ->select("value")
                            ->from("MediumMetadatum mm")
                            ->where("mm.media_id = ?", $media['id'])
                            ->addWhere("key = 'desc'")
                            ->execute();
                        
        $tag_ex = Doctrine_Query::create()
                            ->select("value")
                            ->from("MediumMetadatum mm")
                            ->where("mm.media_id = ?", $media['id'])
                            ->addWhere("key = 'tags'")
                            ->execute();
                        
        $this->view->name = $name_ex[0]['value'];
        $this->view->tags = $tag_ex[0]['value'];
        $this->view->desc = $desc_ex[0]['value'];
            $change = false;            
        if($this->_request->isPost()) {

        
        // check if any of the rows exist
            
            if (strlen($this->formData['name']) > 0){
                if(!$name_ex[0]['id']){
                    $name = new MediumMetadatum();
                            $name['key'] = "name";
                            $name['value'] = $this->formData['name'];
                            $name['media_id'] = $media['id'];
                            $name->save();
                            $change = true;
                } else {
                    $name_up = Doctrine_Query::create()
                        ->update("MediumMetadatum")
                        ->set("value", "'". $this->formData['name']. "'")
                        ->where("media_id = ?", $media['id'])
                        ->addWhere("key = 'name'")
                        ->execute();
                        $change = true;
                }
                
                $this->view->name = $this->formData['name'];
                
            }
            
            if (strlen($this->formData['tags']) > 0){
                if(!$tag_ex[0]['id']){
                    $tags = new MediumMetadatum();
                            $tags['key'] = "tags";
                            $tags['value'] = $this->formData['tags'];
                            $tags['media_id'] = $media['id'];
                            $tags->save();
                            $change = true;
                } else {
                    $tag_up = Doctrine_Query::create()
                        ->update("MediumMetadatum")
                        ->set("value", "'" . $this->formData['tags'] ."'")
                        ->where("media_id = ?", $media['id'])
                        ->addWhere("key = 'tags'")
                        ->execute();
                        $change = true;
                }
                
                $this->view->tags = $this->formData['tags'];
            }
            
            if (strlen($this->formData['desc']) > 0){
                if(!$desc_ex[0]['id']){
                    $name = new MediumMetadatum();
                            $name['key'] = "desc";
                            $name['value'] = $this->formData['desc'];
                            $name['media_id'] = $media['id'];
                            $name->save();
                            $change = true;

                } else {
                    $desc_up = Doctrine_Query::create()
                        ->update("MediumMetadatum")
                        ->set("value", "'".$this->formData['desc']."'")
                        ->where("media_id = ?", $media['id'])
                        ->addWhere("key = 'desc'")
                        ->execute();
                        $change = true;
                }
                $this->view->desc = $this->formData['desc'];
            }
        }
        $this->view->media_id = $media_id;
        
        $this->view->media = $media = Doctrine::getTable('Medium')
                        ->findOneByMediaId($media_id);

        $conn_string = "host='db.flixn.com' user='flixn' dbname='fmp_dev'";
        $db = pg_connect($conn_string);
        
        $user_id = $this->identity['id'];
        
        $stats_query_string = sprintf("SELECT m.media_id AS id, COUNT(mv) AS count,
                                (SUM(mv.duration)/COUNT(mv)) AS duration,
                                SUM(mv.size) AS size,
                                date_trunc('second', mmc.timestamp) AS timestamp
                                FROM media m
                                LEFT JOIN media_video mv
                                ON (m.id = mv.media_id)
                                LEFT JOIN media_metadata_creation mmc
                                ON (m.id = mmc.media_id)
                                WHERE m.user_id = %d
                                AND mmc.timestamp IS NOT NULL
                                AND mv.size IS NOT NULL AND mv.media_id = %s
                                GROUP BY m.media_id, timestamp;", $user_id, $media['id']);
    
    $temp = pg_fetch_row(pg_query($db, $stats_query_string));
    
    $time = $temp[2] / 1000;
    $hr = floor($time / (60 * 60));
    $min = floor(($time - ($hr * 60 * 60)) / 60);
    $sec = ceil($time - ($hr * 60 * 60) - ($min * 60));
    
    $time = sprintf("%01d:%02d:%02d", $hr, $min, $sec);
    
    $size = sprintf("%.2fmb", $temp[3] / 1024 / 1024);
    
    $this->view->media_stats = array('copies' => $temp[1], 'duration' => $time, 'size' => $size, 'timestamp' => $temp[4]);
    
    
    if ($change) {
        $this->view->notice = "Your changes have been saved.";
    }
    
    // stupid mapping

    
    }
    
    public function ajaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $id = $this->_request->getParam('id');
        $media_id = $this->_checkMedia();
        
        $this->view->media_id = $media_id;
        
        $this->view->media = $media = Doctrine::getTable('Medium')
                        ->findOneByMediaId($media_id);
        
        if ($this->_request->isXmlHttpRequest()) {
            
            if(isset($id))
            {
            //$queries = Doctrine_Query::create()->delete('MediumMetadatum')->where('media_id = ' . $this->_request->getParam('id'))->execute();
                foreach($_POST as $p => $q)
                {
                    
                    //if(str_replace("me@1d#ia_", "",$p))
                    //{
                        $meta = new MediumMetadatum();
                        $meta['key'] = $p;
                        $meta['value'] = $q;
                        $meta['media_id'] = $media['id'];
                        $meta->save();
                        unset($meta);
                   // }
                }
            }
            
        }
    }
    
    
    private function _checkMedia()
    {
        $media_id = $this->_request->getParam('id');
        if (!isset($media_id))
                $this->_redirect('/');
            /// XXX - redirects are bad at present
        
        $media = Doctrine::getTable('Medium')->findOneByMediaId($media_id);
                        
        if($media['user_id'] != $this->userid)
            $this->_redirect('/' . $this->_request->getParam('controller') . '/index');
        else
            return $media_id;
    }

}