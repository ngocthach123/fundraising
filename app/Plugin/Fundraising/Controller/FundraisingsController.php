<?php

/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
App::uses('Component', 'Fundraising.Controller/Component');

class FundraisingsController extends FundraisingAppController {

    
    
    public $paginate = array(
        'order' => array(
            'Topic.id' => 'desc'
        ),
        'findType' => 'translated',
    );
    
    public function beforeFilter() {
        parent::beforeFilter();
        $this->loadModel('Fundraising.Campaign');
    }

    public function index($cat_id = null) {
        $this->loadModel('Tag');
        $this->loadModel('Category');

        $cat_id = intval($cat_id);
        
        

        $tags = $this->Tag->getTags('Fundraising_Campaign', Configure::read('core.popular_interval'));
        $more_result = 0;
        if (!empty($cat_id)){
            $campaigns = $this->Campaign->getCampaigns('category', $cat_id);
            $more_campaigns = $this->Campaign->getCampaigns('category', $cat_id,2);
        }else{
            $campaigns = $this->Campaign->getCampaigns();
            $more_campaigns = $this->Campaign->getCampaigns(null,null,2);
        }
        if(!empty($more_campaigns)){
            $more_result = 1;
        }
        
        $this->set('tags', $tags);
        $this->set('campaigns', $campaigns);
        $this->set('cat_id', $cat_id);
        $this->set('title_for_layout', '');
        $this->set('more_result', $more_result);
    }

    /*
     * Browse albums based on $type
     * @param string $type - possible value: cats, my, home, friends
     * @param mixed $param - could be catid (category), uid (user) or a query string (search)
     */

    public function browse($type = null, $param = null) {
        $page = (!empty($this->request->named['page'])) ? $this->request->named['page'] : 1;
        $uid = $this->Auth->user('id');

        if (!empty($this->request->named['category_id'])) {
            $type = 'category';
            $param = $this->request->named['category_id'];
        }

        $url = (!empty($param)) ? $type . '/' . $param : $type;

        switch ($type) {
            case 'home':
            case 'my':
            case 'friends':
                $this->_checkPermission();
                $param = $uid;
                break;

            case 'search':
                $param = urldecode($param);

                if (!Configure::read('core.guest_search') && empty($uid))
                    $this->_checkPermission();

                break;

            default:
                if (!empty($param))
                    $this->Session->write('cat_id', $param);
        }
        
        $campaigns = $this->Campaign->getCampaigns($type, $param, $page);
        $more_result = 0;
        $more_campaigns = $this->Campaign->getCampaigns($type, $param, $page +1);
        if(!empty($more_campaigns))
            $more_result = 1;
            
        $this->set('campaigns', $campaigns);
        
        $this->set('more_url', '/fundraisings/browse/' . h($url) . '/page:' . ($page + 1));
        $this->set('page', $page);
        $this->set('more_result',$more_result);
        $data = array (
            'campaigns' => $campaigns,
            'more_url' => '/fundraisings/browse/' . h($url) . '/page:' . ($page + 1),
            'page' => $page,
            'type' => $type,
        );
               
        $this->set('data', $data);
        
        if ($page == 1 && $type == 'home'){
            $this->render('/Elements/ajax/home_campaign');
        }
        else{
            if ($this->request->is('ajax')){
                $this->render('/Elements/lists/campaigns_list');
            }
            else{
                $this->render('/Elements/lists/campaigns_list_m');
            }
        }
    }

    public function create($id = null) {
        $id = intval($id);
        $this->_checkPermission(array('confirm' => true));
        $this->_checkPermission(array('aco' => 'fundraising_create'));

        $this->loadModel('Category');
        $role_id = $this->_getUserRoleId();

        $cats = $this->Category->getCategoriesList('Fundraising', $role_id);
        
        if (!empty($id)) { // editing
            $campaign = $this->Campaign->findById($id);
            $this->_checkExistence($campaign);
            $this->_checkPermission(array('admins' => array($campaign['User']['id'])));

            $this->loadModel('Tag');
            $tags = $this->Tag->getContentTags($id, 'Fundraising_Campaign');


            $this->set('tags', $tags);
            $this->set('title_for_layout', __( 'Edit Campaign'));
        } else {
            $campaign = $this->Campaign->initFields();

            if ($this->Session->check('cat_id')) {
                $campaign['Campaign']['category_id'] = $this->Session->read('cat_id');
                $this->Session->delete('cat_id');
            }

            $this->set('title_for_layout', __( 'Create New Campaign'));
        }

        $this->set('campaign', $campaign);
        $this->set('cats', $cats);
    }

    /*
     * Save add/edit form
     */

    public function save() {
        $this->_checkPermission(array('confirm' => true));
        $this->autoRender = false;
        $uid = $this->Auth->user('id');

        if (!empty($this->request->data['id'])) {
            // check edit permission
            $campaign = $this->Campaign->findById($this->request->data['id']);
            $this->_checkCampaign($campaign, true);

            $this->Campaign->id = $this->request->data['id'];
        } else {
            $this->request->data['user_id'] = $uid;
        }

        $this->request->data['body'] = str_replace('../', '/', $this->request->data['body']);

        if(empty($this->request->data['paypal']) && empty($this->request->data['bank'])){
            $response['result'] = 0;
            $response['message'] = __('Please select payment method');
            echo json_encode($response);
            exit;
        }elseif(empty($this->request->data['paypal'])){
            unset($this->Campaign->validate['paypal_email']);
        }elseif(empty($this->request->data['bank'])){
            unset($this->Campaign->validate['bank_info']);
        }

        if(!empty($this->request->data['unlimited'])){
            $this->request->data['expire'] = '';
            unset($this->Campaign->validate['expire']);
        }

        $this->Campaign->set($this->request->data);
        $this->_validateData($this->Campaign);

        // todo: check if user has permission to post in category

        if ($this->Campaign->save()) {
            if (empty($this->request->data['id'])) { // add topic
                $type = APP_USER;
                $target_id = 0;
                $privacy = PRIVACY_EVERYONE;

                $this->loadModel('Activity');
                $this->Activity->save(array('type' => $type,
                        'target_id' =>$target_id,
                        'action' => 'campaign_create',
                        'user_id' => $uid,                       
                        'item_type' => 'Fundraising_Campaign',
                        'privacy' => $privacy,
                		'item_id' => $this->Campaign->id,
                        'query' => 1,
                    	'params' => 'item',
    					'plugin' => 'Fundraising'
                 ));
            }
            $event = new CakeEvent('Plugin.Controller.Fundraising.afterSaveCampaign', $this, array(
                'uid' => $uid, 
                'id' => $this->Campaign->id,
               
             ));

            $this->getEventManager()->dispatch($event);
            
            // update Campaign item_id for photo thumbnail
            if (!empty($this->request->data['campaign_photo_ids'])) {
            	$photos = explode(',', $this->request->data['campaign_photo_ids']);
            	if (count($photos))
            	{
		            $this->loadModel('Photo.Photo');
		            // Hacking for cdn
		            $result = $this->Photo->find("all",array(
		                'recursive'=>1,
		                'conditions' =>array(
		                    'Photo.type' => 'Campaign',
		                    'Photo.user_id' => $uid,
		                	'Photo.id' => $photos
		                )));
		            if($result){
		                $view = new View($this);
		                $mooHelper = $view->loadHelper('Moo');
		                foreach ($result as $iPhoto){
		                    $iPhoto["Photo"]['moo_thumb'] = 'thumbnail';
		                    $mooHelper->getImageUrl($iPhoto, array('prefix' => '450'));
		                    $mooHelper->getImageUrl($iPhoto, array('prefix' => '1500'));
		                }
		                // End hacking
		                $this->Photo->updateAll(array('Photo.target_id' => $this->Campaign->id), array(
		                		'Photo.type' => 'Campaign',
		                		'Photo.user_id' => $uid,
		                		'Photo.id' => $photos
		                ));
		            }
		            
            	}
	        }
            
            $this->loadModel('Tag');
            $this->Tag->saveTags($this->request->data['tags'], $this->Campaign->id, 'Fundraising_Campaign');

            $response['result'] = 1;
            $response['id'] = $this->Campaign->id;
            echo json_encode($response);
        }
    }

    public function view($id = null, $type = 'info') {
//        //send mail
//        $fundraisingMailComponent = MooCore::getInstance()->getComponent('Fundraising.FundraisingMail');
//        $fundraisingMailComponent->send('asd','fundraising_receive_donor',
//            array(
//            )
//        );

        $id = intval($id);
        
        $this->Campaign->recursive = 2;
        $campaign= $this->Campaign->findById($id);
        if ($campaign['Category']['id'])
        {
        	foreach ($campaign['Category']['nameTranslation'] as $translate)
        	{
        		if ($translate['locale'] == Configure::read('Config.language'))
        		{
        			$campaign['Category']['name'] = $translate['content'];
        			break;
        		}
        	}
        }
        $this->Campaign->recursive = 0;
        
        $this->_checkExistence($campaign);
        $this->_checkPermission(array('aco' => 'fundraising_view'));
        $this->_checkPermission( array('user_block' => $campaign['Campaign']['user_id']) );
        
        $uid = $this->Auth->user('id');

        switch ($type){
            case 'mail':
                $this->loadModel('Fundraising.FundraisingMail');
                $mail = $this->FundraisingMail->getMail($id);
                $this->set('mail', $mail);
                $this->set('campaign', $campaign);
                break;
            case 'donor':
                $this->_getCampaignDonor($campaign['Campaign']['id']);
                $this->set('campaign', $campaign);
                break;
            default:
                $this->_getCampaignDetail($campaign);
                break;
        }
        
        $this->loadModel('Tag');
        $tags = $this->Tag->getContentTags($id, 'Fundraising_Campaign');

        $areFriends = false;
        if (!empty($uid)) { //  check if user is a friend
            $this->loadModel('Friend');
            $areFriends = $this->Friend->areFriends($uid, $campaign['User']['id']);
        }
        MooCore::getInstance()->setSubject($campaign);
        $this->loadModel('Like');
        $likes = $this->Like->getLikes($id, 'Fundraising_Campaign');
        $dislikes = $this->Like->getDisLikes($id, 'Fundraising_Campaign');

        $this->set('areFriends', $areFriends);
        $this->set('tags', $tags);
        $this->set('likes', $likes);
        $this->set('dislikes', $dislikes);

        $this->set('title_for_layout', htmlspecialchars($campaign['Campaign']['title']));
        $this->set('type', $type);

    	$description = $this->getDescriptionForMeta($campaign['Campaign']['body']);
        if ($description) {
            $this->set('description_for_layout', $description);
            if (count($tags))
            {
            	$tags = implode(",", $tags).' ';
            }
            else
            {
            	$tags = '';
            }
            $this->set('mooPageKeyword', $this->getKeywordsForMeta($tags.$description));
        }

    }

    private function _getCampaignDonor($id){
        $this->loadModel('Fundraising.CampaignDonor');

        $page = (!empty($this->request->named['page'])) ? $this->request->named['page'] : 1;
        $donors = $this->CampaignDonor->getDonors($id, $page);
        $more_donors = $this->CampaignDonor->getDonors($id, $page+1);
        $more_result = 0;
        if (!empty($more_donors))
            $more_result = 1;

        $this->set('more_url', '/fundraisings/ajax_donor/' . $id . '/page:'.($page+1));
        $this->set('donors', $donors);
        $this->set('more_result', $more_result);
    }

    public function ajax_donor($id){
        $this->_getCampaignDonor($id);

        $this->render('/Elements/lists/donors_list');
    }

    private function _getCampaignDetail($campaign) {
        $uid = $this->Auth->user('id');
        $data = array ();

        $this->loadModel('Like');
        $this->loadModel('Comment');

        $comments = $this->Comment->getComments($campaign['Campaign']['id'], 'Fundraising_Campaign');
        
        // get comment likes
        if (!empty($uid)) {
            $comment_likes = $this->Like->getCommentLikes($comments, $uid);
            $this->set('comment_likes', $comment_likes);
            $data['comment_likes'] = $comment_likes ;
            $like = $this->Like->getUserLike($campaign['Campaign']['id'], $uid, 'Fundraising_Campaign');
            $this->set('like', $like);
        }

        $page = 1 ;
        $this->set('campaign', $campaign);
        $data['comments'] = $comments ;
        $data['bIsCommentloadMore'] = $campaign['Campaign']['comment_count'] - $page*RESULTS_LIMIT ;
        $data['more_comments'] = '/comments/browse/Fundraising_Campaign/' . $campaign['Campaign']['id'] . '/page:' . ($page + 1) ;
       
        $this->set('data', $data);
    }

    /*
     * Delete topic
     * @param int $id - topic id to delete
     */

    public function do_delete($id = null) {
        $id = intval($id);
        $this->ajax_delete($id);

        $this->Session->setFlash(__( 'Campaign has been deleted'));
        $this->redirect('/fundraisings');
    }

    public function ajax_delete($id = null) {
        $id = intval($id);
        $this->autoRender = false;

        $campaign = $this->Campaign->findById($id);
        $this->_checkCampaign($campaign, true);

        $this->Campaign->deleteCampaign($campaign);
        $cakeEvent = new CakeEvent('Plugin.Controller.Campaign.afterDeleteCampaign', $this, array('item' => $campaign));
        $this->getEventManager()->dispatch($cakeEvent);
    }

    private function _checkCampaign($campaign, $allow_author = false) {
        $this->_checkExistence($campaign);
        $admins = array();

        if ($allow_author)
            $admins = array($campaign['User']['id']); // campaign creator

        $this->_checkPermission(array('admins' => $admins));
    }

    public function popular() {
        if ($this->request->is('requested')) {
            $num_item_show = $this->request->named['num_item_show'];
            return $this->Topic->getPopularTopics($num_item_show, Configure::read('core.popular_interval'));
        }
    }
    
    public function categories_list(){
        if ($this->request->is('requested')){
            $this->loadModel('Category');
            $role_id = $this->_getUserRoleId();
            $categories = $this->Category->getCategories('Fundraising', $role_id);
            return $categories;
        }
    }

    public function ajax_invite($campaign_id = null) {
        $campaign_id = intval($campaign_id);
        $this->_checkPermission(array('confirm' => true));

        $this->set('campaign_id', $campaign_id);
    }

    public function ajax_sendInvite() {
        $this->autoRender = false;
        $this->_checkPermission(array('confirm' => true));
        $cuser = $this->_getUser();

        $campaign = $this->Campaign->findById($this->request->data['campaign_id']);

        if ($this->request->data['invite_type'] == 1)
        {
            if (!empty($this->request->data['friends'])) {
                $friends = explode(',', $this->request->data['friends']);

                $this->loadModel('Notification');
                $this->Notification->record(array('recipients' => $friends,
                    'sender_id' => $cuser['id'],
                    'action' => 'fundraising_campaign_invite',
                    'url' => '/fundraisings/view/' . $this->request->data['campaign_id'],
                    'params' => h($campaign['Campaign']['title']),
                    'plugin' => 'Fundraising'
                ));
            } else {
                return $this->_jsonError(__('Recipient is required'));
            }
        }
        else
        {
            if (!empty($this->request->data['emails'])) {
                // check captcha
                $checkRecaptcha = MooCore::getInstance()->isRecaptchaEnabled();
                $recaptcha_privatekey = Configure::read('core.recaptcha_privatekey');
                $is_mobile = $this->viewVars['isMobile'];
                if ( $checkRecaptcha && !$is_mobile)
                {
                    App::import('Vendor', 'recaptchalib');
                    $reCaptcha = new ReCaptcha($recaptcha_privatekey);
                    $resp = $reCaptcha->verifyResponse(
                        $_SERVER["REMOTE_ADDR"], $_POST["g-recaptcha-response"]
                    );

                    if ($resp != null && !$resp->success) {
                        return	$this->_jsonError(__('Invalid security code'));
                    }
                }
                $emails = explode(',', $this->request->data['emails']);

                $i = 1;


                foreach ($emails as $email) {
                    $invite_checksum = uniqid();
                    if ($i <= 10) {
                        if (Validation::email(trim($email))) {
                            $ssl_mode = Configure::read('core.ssl_mode');
                            $http = (!empty($ssl_mode)) ? 'https' :  'http';
                            $this->MooMail->send(trim($email),'fundraising_invite_none_member',
                                array(
                                    'campaign_title' => $campaign['Campaign']['moo_title'],
                                    'link' => $http.'://'.$_SERVER['SERVER_NAME'].$campaign['Campaign']['moo_href'].'/'.$invite_checksum,
                                    'email' => trim($email),
                                    'sender_title' => $cuser['name'],
                                    'sender_link' => $http.'://'.$_SERVER['SERVER_NAME'].$cuser['moo_href'],
                                )
                            );
                        }
                    }
                    $i++;
                }
            }
            else
            {
                return	$this->_jsonError(__d('forum', 'Recipient is required'));
            }
        }

        $response = array();
        $response['result'] = 1;
        $response['msg'] = __d('forum', 'Your invitations have been sent.') . ' <a href="javascript:void(0)" onclick="$(\'#themeModal .modal-content\').load(\''.$this->request->base.'/fundraisings/ajax_invite/'.$this->request->data['campaign_id'].'\');">' . __('Invite more friends') . '</a>';
        echo json_encode($response);
    }

    public function email_setting(){
        $this->autoRender = false;
        if($this->request->is('post')){
            $this->loadModel('Fundraising.FundraisingMail');
            $data = $this->request->data;

            $this->FundraisingMail->set($data);
            $this->_validateData($this->FundraisingMail);

            $mail = $this->FundraisingMail->find('first', array(
                'conditions' => array('target_id' => $data['target_id'])
            ));

            if(!empty($mail)){
                $data['id'] = $mail['FundraisingMail']['id'];
                $this->FundraisingMail->set($data);
            }

            if($this->FundraisingMail->save()){
                $response = array(
                    'result' => 1,
                );
                echo json_encode($response);exit;
            }
        }
        $response = array(
            'result' => 0,
            'message' => __('An error has occurred, please try again'),
        );
        echo json_encode($response);exit;
    }

    public function donate($id = null){
        $id = intval($id);
        $campaign= $this->Campaign->findById($id);
        $this->_checkExistence($campaign);

        $http = (!empty($ssl_mode)) ? 'https' :  'http';
        if (Configure::read("Fundraising.fundraising_test_mode")){
            $paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        }else{
            $paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
        }

        $cancel_return = $http."://".$_SERVER['SERVER_NAME'].$campaign['Campaign']['moo_href'];
        $success_return = $http."://".$_SERVER['SERVER_NAME'].$this->request->base.'/fundraisings/pay_success/'.$campaign['Campaign']['id'];

        $this->set(compact('paypal_url', 'cancel_return', 'success_return'));
        $this->set('campaign',$campaign);
    }

    public function pay_paypal(){
        if(!empty($this->request->data)){
            $this->loadModel('Fundraising.CampaignDonor');

            $data = $this->request->data;
            $uid = $this->Auth->user('id');
            $this->CampaignDonor->set($data);
            $this->_validateData($this->CampaignDonor);
            $cuser = $this->_getUser();

            //validate
            if(empty($data['accept_term'])){
                $response = array(
                    'result' => 0,
                    'message' => __('You must accept the terms and conditions'),
                );
                echo json_encode($response);exit;
            }

            if(empty($data['anonymous'])){
                $data['user_id'] = $uid;
            }else{
                $data['user_id'] = 0;
            }

            if(empty($data['hide_feed'])){
                $hide_feed = 0;
            }else{
                $hide_feed = 1;
            }

            $data['email'] = $cuser['email'];
            $data['name'] = $cuser['name'];
            $data['method'] = 'paypal';
            $data['status'] = 2;

            $this->CampaignDonor->set($data);
            if($this->CampaignDonor->save()){
                $http = (!empty($ssl_mode)) ? 'https' :  'http';
                $response = array(
                    'result' => 1,
                    'amount' => $data['amount'],
                    'notify_url' => $http."://".$_SERVER['SERVER_NAME'].$this->request->base.'/fundraisings/ipn?id='.$this->CampaignDonor->id.'&hide_feed='.$hide_feed,
                    'cancel_url' => $http."://".$_SERVER['SERVER_NAME'].$this->request->base.'/fundraisings/pay_cancel/'.$this->CampaignDonor->id,
                );
                echo json_encode($response);exit;

            }else{
                $response = array(
                    'result' => 0,
                    'message' => __('An error has occurred, please try again'),
                );
                echo json_encode($response);exit;
            }
        }
    }

    public function pay_offline($send = 0){
        if(!empty($this->request->data)){
            $this->loadModel('Fundraising.CampaignDonor');

            $data = $this->request->data;
            $uid = $this->Auth->user('id');
            $this->CampaignDonor->set($data);
            $this->_validateData($this->CampaignDonor);


            //validate
            if(empty($data['accept_term'])){
                $response = array(
                    'result' => 0,
                    'message' => __('You must accept the terms and conditions'),
                );
                echo json_encode($response);exit;
            }

            if($send){
                // check captcha
                $checkRecaptcha = MooCore::getInstance()->isRecaptchaEnabled();
                $recaptcha_privatekey = Configure::read('core.recaptcha_privatekey');
                if ( $checkRecaptcha)
                {
                    App::import('Vendor', 'recaptchalib');
                    $reCaptcha = new ReCaptcha($recaptcha_privatekey);
                    $resp = $reCaptcha->verifyResponse(
                        $_SERVER["REMOTE_ADDR"], $_POST["g-recaptcha-response"]
                    );

                    if ($resp != null && !$resp->success) {
                        echo __('Invalid security code');
                        return;
                    }
                }

                $data['user_id'] = empty($data['anonymous']) ? $uid : 0;
                $data['method'] = 'offline';
                $data['status'] = 0;
                $this->CampaignDonor->set($data);
                if($this->CampaignDonor->save()){
                    $this->Campaign->updateCounter($data['target_id'], 'donor_count', array('CampaignDonor.target_id' => $data['target_id'], 'CampaignDonor.status <>' => 2), 'CampaignDonor');
                    //create feed
                    if(empty($data['hide_feed'])) {
                        $type = APP_USER;
                        $target_id = 0;
                        $privacy = PRIVACY_EVERYONE;

                        $this->loadModel('Activity');
                        $this->Activity->save(array('type' => $type,
                            'target_id' => $target_id,
                            'action' => 'campaign_donate',
                            'user_id' => $uid,
                            'item_type' => 'Fundraising_Campaign',
                            'privacy' => $privacy,
                            'item_id' => $data['target_id'],
                            'query' => 1,
                            'params' => 'item',
                            'plugin' => 'Fundraising'
                        ));
                    }

                    $this->Session->setFlash(__('Successfully sent'), 'default', array('class' => 'Metronic-alerts alert alert-success fade in'));

                    $response = array(
                        'result' => 1,
                        'redirect' => $this->request->base.'/fundraisings/view/'.$data['target_id'],
                    );
                    echo json_encode($response);exit;

                }else{
                    $response = array(
                        'result' => 0,
                        'message' => __('An error has occurred, please try again'),
                    );
                    echo json_encode($response);exit;
                }

            }

            $response = array(
                'result' => 1,
            );
            echo json_encode($response);exit;
        }
    }

    public function delete_donor($id = null){
        if($this->request->is('post')){
            $this->loadModel('Fundraising.CampaignDonor');
            $this->CampaignDonor->bindModel(array(
                'belongsTo' => array(
                    'Campaign' => array(
                        'classname' => 'Fundraising.Campaign',
                        'foreignKey' => 'target_id'
                    )
                )
            ));

            $id = $this->request->data['item_id'];
            $donor = $this->CampaignDonor->findById($id);

            $this->_checkExistence($donor);
            $this->_checkPermission(array('admins' => array($donor['Campaign']['user_id'])));
            if(empty($this->request->data['message'])){
                $response = array(
                    'result' => 0,
                    'message' => __('Message is required'),
                );
                echo json_encode($response);exit;
            }

            $this->CampaignDonor->delete($id);
            $this->Campaign->updateCounter($donor['CampaignDonor']['target_id'], 'donor_count', array('CampaignDonor.target_id' => $donor['CampaignDonor']['target_id'], 'CampaignDonor.status <>' => 2), 'CampaignDonor');

            //send mail
            if(!empty($donor['CampaignDonor']['email'])) {
                $ssl_mode = Configure::read('core.ssl_mode');
                $http = (!empty($ssl_mode)) ? 'https' : 'http';
                $this->MooMail->send(trim($donor['CampaignDonor']['email']), 'fundraising_delete_donor',
                    array(
                        'receive_name' => $donor['CampaignDonor']['name'],
                        'message' => $this->request->data['message'],
                        'link' => $http . '://' . $_SERVER['SERVER_NAME'] . $donor['Campaign']['moo_href'] . '/',
                    )
                );
            }
            $this->Session->setFlash( __('Donor has been deleted'), 'default', array('class' => 'Metronic-alerts alert alert-success fade in') );

            $response = array(
                'result' => 1,
            );
            echo json_encode($response);exit;
        }
        $this->set('item_id', $id);
    }

    public function receive_donor($id = null){
        if($this->request->is('post')){
            $this->loadModel('Fundraising.CampaignDonor');
            $this->CampaignDonor->bindModel(array(
                'belongsTo' => array(
                    'Campaign' => array(
                        'classname' => 'Fundraising.Campaign',
                        'foreignKey' => 'target_id'
                    )
                )
            ));

            $id = $this->request->data['item_id'];
            $donor = $this->CampaignDonor->findById($id);

            $this->_checkExistence($donor);
            $this->_checkPermission(array('admins' => array($donor['Campaign']['user_id'])));
            if(empty($this->request->data['message'])){
                $response = array(
                    'result' => 0,
                    'message' => __('Message is required'),
                );
                echo json_encode($response);exit;
            }

            $this->CampaignDonor->updateStatus($id, 1);
            $this->Campaign->updateTotalRaised($donor['CampaignDonor']['target_id']);

            //send mail
            $ssl_mode = Configure::read('core.ssl_mode');
            $http = (!empty($ssl_mode)) ? 'https' :  'http';
            $this->MooMail->send(trim($donor['CampaignDonor']['email']),'fundraising_receive_donor',
                array(
                    'receive_name' => $donor['CampaignDonor']['name'],
                    'message' => $this->request->data['message'],
                    'link' => $http.'://'.$_SERVER['SERVER_NAME'].$donor['Campaign']['moo_href'].'/',
                )
            );
            $this->Session->setFlash( __('Status changed'), 'default', array('class' => 'Metronic-alerts alert alert-success fade in') );

            $response = array(
                'result' => 1,
            );
            echo json_encode($response);exit;
        }
        $this->set('item_id', $id);
    }

    public function ipn(){
        $this->autoRender = false;
        // STEP 1: read POST data
        // Reading POSTed data directly from $_POST causes serialization issues with array data in the POST.
        // Instead, read raw POST data from the input stream.
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode ('=', $keyval);
            if (count($keyval) == 2)
                $myPost[$keyval[0]] = urldecode($keyval[1]);
        }
        // read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
        $req = 'cmd=_notify-validate';
        if (function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        foreach ($myPost as $key => $value) {
            if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }

        if (Configure::read("Fundraising.fundraising_test_mode")){
            $ipn_url = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
        }else{
            $ipn_url = 'https://ipnpb.paypal.com/cgi-bin/webscr';
        }

        // Step 2: POST IPN data back to PayPal to validate
        $ch = curl_init($ipn_url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

        if ( !($res = curl_exec($ch)) ) {
            $this->log('Res invalid');
            curl_close($ch);
            exit;
        }
        curl_close($ch);

        // inspect IPN validation result and act accordingly
        if (strcmp ($res, "VERIFIED") == 0) {
//            $this->log('IPN success');
//            $this->log($_REQUEST);
            // The IPN is verified, process it
            $id = @$_REQUEST['id'];
            $hide_feed = @$_REQUEST['hide_feed'];
            if($id) {
                $this->loadModel('Fundraising.CampaignDonor');
                $donor = $this->CampaignDonor->findById($id);
                if(!empty($donor)) {
                    $this->CampaignDonor->updateStatus($id, 1);

                    $this->Campaign->updateCounter($donor['CampaignDonor']['target_id'], 'donor_count', array('CampaignDonor.target_id' => $donor['CampaignDonor']['target_id'], 'CampaignDonor.status <>' => 2), 'CampaignDonor');
                    $this->Campaign->updateTotalRaised($donor['CampaignDonor']['target_id']);

                    //create feed
                    if (!$hide_feed) {
                        $type = APP_USER;
                        $target_id = 0;
                        $privacy = PRIVACY_EVERYONE;

                        $this->loadModel('Activity');
                        $this->Activity->save(array('type' => $type,
                            'target_id' => $target_id,
                            'action' => 'campaign_donate',
                            'user_id' => $this->Auth->user('id'),
                            'item_type' => 'Fundraising_Campaign',
                            'privacy' => $privacy,
                            'item_id' => $donor['CampaignDonor']['target_id'],
                            'query' => 1,
                            'params' => 'item',
                            'plugin' => 'Fundraising'
                        ));
                    }
                }
            }
        } else if (strcmp ($res, "INVALID") == 0) {
            // IPN invalid, log for manual investigation
//            $this->log('IPN invalid, log for manual investigation');
        }
    }

    public function log($msg, $type = LOG_ERR, $scope = null)
    {
        if (!is_string($msg))
        {
            $msg = print_r($msg,true);
        }

        parent::log($msg,'fundraising');
    }

    public function pay_success($id){
        $this->Session->setFlash( __('Pay successfully'), 'default', array('class' => 'Metronic-alerts alert alert-success fade in') );
        $this->redirect('/fundraisings/view/'.$id);
    }

    public function pay_cancel($id = null){
        $this->loadModel('Fundraising.CampaignDonor');
        $this->CampaignDonor->bindModel(array(
            'belongsTo' => array(
                'Campaign' => array(
                    'classname' => 'Fundraising.Campaign',
                    'foreignKey' => 'target_id'
                )
            )
        ));

        $donor = $this->CampaignDonor->findById($id);

        $this->_checkExistence($donor);
        $this->_checkPermission(array('admins' => array($donor['Campaign']['user_id'])));

        $this->CampaignDonor->delete($id);

        $this->redirect('/fundraisings/view/'.$donor['Campaign']['id']);
    }
}
