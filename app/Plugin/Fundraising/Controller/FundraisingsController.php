<?php

/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
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
            $this->_checkTopic($campaign, true);

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

    public function view($id = null) {
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
        $this->_checkPermission(array('aco' => 'topic_view'));
        $this->_checkPermission( array('user_block' => $campaign['Campaign']['user_id']) );
        
        $uid = $this->Auth->user('id');

        $this->_getCampaignDetail($campaign);
        
        $this->loadModel('Tag');
        $tags = $this->Tag->getContentTags($id, 'Fundraising_Campaign');

        $areFriends = false;
        if (!empty($uid)) { //  check if user is a friend
            $this->loadModel('Friend');
            $areFriends = $this->Friend->areFriends($uid, $campaign['User']['id']);
        }
        MooCore::getInstance()->setSubject($campaign);
        $likes = $this->Like->getLikes($id, 'Fundraising_Campaign');
        $dislikes = $this->Like->getDisLikes($id, 'Fundraising_Campaign');

        $this->loadModel('NotificationStop');
        $notification_stop = $this->NotificationStop->find('count', array('conditions' => array('item_type' => 'campaign',
                        'item_id' => $id,
                        'user_id' => $uid)
                        ));
        $this->set('notification_stop', $notification_stop);
        
        $this->set('areFriends', $areFriends);
        $this->set('tags', $tags);
        $this->set('likes', $likes);
        $this->set('dislikes', $dislikes);

        $this->set('title_for_layout', htmlspecialchars($campaign['Campaign']['title']));
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

        // set og:image
        if ($campaign['Campaign']['thumbnail']) {
            $mooHelper = MooCore::getInstance()->getHelper('Core_Moo');
            $this->set('og_image', $mooHelper->getImageUrl($campaign, array('prefix' => '850')));
            
        }

    }
    
    public function profile_user_topic($uid = null){
        $uid = intval($uid);
        $this->loadModel('Topic.Topic');
        $page = (!empty($this->request->named['page'])) ? $this->request->named['page'] : 1;	

        $topics = $this->Topic->getTopics( 'user', $uid, $page );
        $more_result = 0;
        $more_topics = $this->Topic->getTopics( 'user', $uid, $page  + 1);
        if(!empty($more_topics))
            $more_result = 1;
        $this->set('topics', $topics);
        $this->set('more_url', '/topics/profile_user_topic/' . $uid . '/page:' . ( $page + 1 ));
        $this->set('user_id', $uid);
        $this->set('more_result', $more_result);
        if ($page > 1)
            $this->render('/Elements/lists/topics_list');
        else
            $this->render('Topic.Topics/profile_user_topic');
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
        $topic = $this->Topic->findById($id);
        $this->ajax_delete($id);

        $this->Session->setFlash(__( 'Topic has been deleted'));
        if ($topic['Topic']['group_id'])
        {
        	$this->redirect('/groups/view/'.$topic['Topic']['group_id'].'/tab:topics');
        	return;
        }
        $this->redirect('/topics');
    }

    public function ajax_delete($id = null) {
        $id = intval($id);
        $this->autoRender = false;

        $topic = $this->Topic->findById($id);
        $this->_checkTopic($topic, true);

        $this->Topic->deleteTopic($topic);
        $this->Topic->deleteTopic($topic);
        $cakeEvent = new CakeEvent('Plugin.Controller.Topic.afterDeleteTopic', $this, array('item' => $topic));
        $this->getEventManager()->dispatch($cakeEvent);
    }

    public function do_pin($id = null) {
        $id = intval($id);
        $topic = $this->Topic->findById($id);
        $this->_checkTopic($topic);

        $this->Topic->id = $id;
        $this->Topic->save(array('pinned' => 1));
        
        // event
        $cakeEvent = new CakeEvent('Plugin.Controller.Topic.afterPin', $this, array('item' => $topic));
        $this->getEventManager()->dispatch($cakeEvent);

        $this->Session->setFlash(__( 'Topic has been pinned'));

        if (!empty($topic['Topic']['group_id']))
            $this->redirect('/groups/view/' . $topic['Topic']['group_id'] . '/topic_id:' . $id);
        else
            $this->redirect('/topics/view/' . $id);
    }

    public function do_unpin($id = null) {
        $id = intval($id);
        $topic = $this->Topic->findById($id);
        $this->_checkTopic($topic);

        $this->Topic->id = $id;
        $this->Topic->save(array('pinned' => 0));
        
        // event
        $cakeEvent = new CakeEvent('Plugin.Controller.Topic.afterUnPin', $this, array('item' => $topic));
        $this->getEventManager()->dispatch($cakeEvent);

        $this->Session->setFlash(__( 'Topic has been unpinned'));

        if (!empty($topic['Topic']['group_id']))
            $this->redirect('/groups/view/' . $topic['Topic']['group_id'] . '/topic_id:' . $id);
        else
            $this->redirect('/topics/view/' . $id);
    }

    public function do_lock($id = null) {
        $id = intval($id);
        $topic = $this->Topic->findById($id);
        $this->_checkTopic($topic);

        $this->Topic->id = $id;
        $this->Topic->save(array('locked' => 1));
        
        // event
        $cakeEvent = new CakeEvent('Plugin.Controller.Topic.afterLock', $this, array('item' => $topic));
        $this->getEventManager()->dispatch($cakeEvent);

        $this->Session->setFlash(__( 'Topic has been locked'));

        if (!empty($topic['Topic']['group_id']))
            $this->redirect('/groups/view/' . $topic['Topic']['group_id'] . '/topic_id:' . $id);
        else
            $this->redirect('/topics/view/' . $id);
    }

    public function do_unlock($id = null) {
        $id = intval($id);
        $topic = $this->Topic->findById($id);
        $this->_checkTopic($topic);

        $this->Topic->id = $id;
        $this->Topic->save(array('locked' => 0));

        $this->Session->setFlash(__( 'Topic has been unlocked'));

        if (!empty($topic['Topic']['group_id']))
            $this->redirect('/groups/view/' . $topic['Topic']['group_id'] . '/topic_id:' . $id);
        else
            $this->redirect('/topics/view/' . $id);
    }

    private function _checkTopic($topic, $allow_author = false) {
        $this->_checkExistence($topic);
        $admins = array();

        if ($allow_author)
            $admins = array($topic['User']['id']); // topic creator

            
// if it's a group topic then group admins can do it
        if (!empty($topic['Topic']['group_id'])) {
            $this->loadModel('Group.GroupUser');

            $group_admins = $this->GroupUser->getUsersList($topic['Topic']['group_id'], GROUP_USER_ADMIN);
            $admins = array_merge($admins, $group_admins);
        }

        $this->_checkPermission(array('admins' => $admins));
    }

    public function admin_index() {
        if (!empty($this->request->data['keyword']))
            $this->redirect('/admin/topics/index/keyword:' . $this->request->data['keyword']);

        $cond = array();
        if (!empty($this->request->named['keyword']))
            $cond['MATCH(Topic.title) AGAINST(? IN BOOLEAN MODE)'] = $this->request->named['keyword'];

        $topics = $this->paginate('Topic', $cond);

        $this->loadModel('Category');
        $categories = $this->Category->getCategoriesList('Topic');

        $this->set('topics', $topics);
        $this->set('categories', $categories);
        $this->set('title_for_layout', 'Topics Manager');
    }

    public function admin_move() {
        if (!empty($_POST['topics']) && !empty($this->request->data['category'])) {
            foreach ($_POST['topics'] as $topic_id) {
                $this->Topic->id = $topic_id;
                $this->Topic->save(array('category_id' => $this->request->data['category']));
            }

            $this->Session->setFlash(__('Topic has been moved'));
        }

        $this->redirect($this->referer());
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

}
