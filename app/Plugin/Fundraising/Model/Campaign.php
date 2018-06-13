<?php

/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
App::uses('FundraisingAppModel', 'Fundraising.Model');

class Campaign extends FundraisingAppModel {
    public $mooFields = array('title','href','plugin','type','url', 'thumb');
    
    public $actsAs = array(
        'MooUpload.Upload' => array(
            'thumbnail' => array(
                'path' => '{ROOT}webroot{DS}uploads{DS}campaigns{DS}{field}{DS}',
                'thumbnailSizes' => array(
                    
                )
            )
        ),
        'Hashtag' ,
        'Storage.Storage' => array(
            'type'=>array('campaigns'=>'thumbnail'),
        ),
    );
    
    public $recursive = 2;

    public $belongsTo = array('User' => array('counterCache' => true,
        ),
        'LastDonor' => array(
            'className' => 'User',
            'foreignKey' => 'lastdonor_id'
        ),
        'Category' => array(
            'counterCache' => 'item_count',
            'counterScope' => array('Category.type' => 'Fundraising')
        )
    );
    public $hasMany = array('Comment' => array(
            'className' => 'Comment',
            'foreignKey' => 'target_id',
            'conditions' => array('Comment.type' => 'Fundraising_Campaign'),
            'dependent' => true
        ),
        'Like' => array(
            'className' => 'Like',
            'foreignKey' => 'target_id',
            'conditions' => array('Like.type' => 'Fundraising_Campaign'),
            'dependent' => true
        ),
        'Tag' => array(
            'className' => 'Tag',
            'foreignKey' => 'target_id',
            'conditions' => array('Tag.type' => 'Fundraising_Campaign'),
            'dependent' => true
        )
    );
    public $order = "Campaign.id desc";
    public $validate = array(
        'title' => array(
            'rule' => 'notBlank',
            'message' => 'Title is required'
        ),
        'category_id' => array(
            'rule' => 'notBlank',
            'message' => 'Category is required'
        ),
        'body' => array(
            'rule' => 'notBlank',
            'message' => 'Body is required'
        ),
        'target_amount' => array(
            'isNumber' =>	array(
                'rule' => 'numeric',
                'message' => 'Target amount only allow numbers'
            ),
            'require' => array(
                'rule' => 'notBlank',
                'message' => 'Target amount is required'
            )
        ),
        'expire' => array(
            'rule' => 'notBlank',
            'message' => 'Expire date is required'
        ),
        'predefined' => array(
            'valid' => array(
                'rule' => 'checkPredefined'
            )
        ),
        'location' => array(
            'rule' => 'notBlank',
            'message' => 'Location is required'
        ),
        'paypal_email' => 	array(
            'rule' => 'notBlank',
            'message' => 'Paypal email is required'
        ),
        'bank_info' => 	array(
            'rule' => 'notBlank',
            'message' => 'Bank details is required'
        ),
        'term' => array(
            'rule' => 'notBlank',
            'message' => 'Term and conditions is required'
        ),
        'tags' => array(
        	'validateTag' => array(
        		'rule' => array('validateTag'),
        		'message' => 'No special characters ( /,?,#,%,...) allowed in Tags',
        	)
        )
    );

    public function getCampaigns($type = null, $param = null, $page = 1, $limit = 0) {
        $cond = array();
        $order = null;
        if(!$limit) {
            $limit = Configure::read('Fundraising.fundraising_item_per_pages');
        }
        switch ($type) {
            case 'category':
                if (!empty($param)) {
                    $cond = array('Campaign.category_id' => $param, 'Category.type' => 'Fundraising');
                    $order = 'Campaign.id desc';
                }

                break;

            case 'friends':
                if ($param) {
                    App::import('Model', 'Friend');
                    $friend = new Friend();
                    $friends = $friend->getFriends($param);
                    $cond = array('Campaign.user_id' => $friends);
                }
                break;

            case 'home':
            case 'my':
                if (!empty($param))
                    $cond = array('Campaign.user_id' => $param);

                break;

            case 'user':
                if ($param)
                    $cond = array('Campaign.user_id' => $param);

                break;

            case 'search':
                if ($param)
                    $cond['AND'] = array(
                        'OR' => array('Campaign.title LIKE '=>'%'.urldecode($param).'%','Campaign.body LIKE '=>'%'.urldecode($param).'%')
                    );

                break;

            default:
                $order = 'Campaign.id desc';
        }

        //only get campaigns of active user
        $cond['User.active'] = 1;
        $cond = $this->addBlockCondition($cond);
        $campaigns = $this->find('all', array('conditions' => $cond, 'order' => $order, 'limit' => $limit, 'page' => $page));
        $uid = CakeSession::read('uid');
        App::import('Model', 'NotificationStop');
        $notificationStop = new NotificationStop();
        foreach ($campaigns as $key => $campaign){
            $notification_stop = $notificationStop->find('count', array('conditions' => array('item_type' => APP_TOPIC,
                    'item_id' => $campaign['Campaign']['id'],
                    'user_id' => $uid)
                    ));
            $campaigns[$key]['Campaign']['notification_stop'] = $notification_stop;
        }
        
        return $campaigns;
    }

    public function deleteCampaign($campaign) {
        
        // delete activity
        $activityModel = MooCore::getInstance()->getModel('Activity');
        $parentActivity = $activityModel->find('list', array('fields' => array('Activity.id') , 'conditions' => 
            array('Activity.item_type' => 'Fundraising_Campaign', 'Activity.item_id' => $campaign['Campaign']['id'])));
        
        $activityModel->deleteAll(array('Activity.item_type' => 'Fundraising_Campaign', 'Activity.item_id' => $campaign['Campaign']['id']), true, true);
        
        // delete child activity
        $activityModel->deleteAll(array('Activity.item_type' => 'Fundraising_Campaign', 'Activity.parent_id' => $parentActivity));
        
        $this->delete($campaign['Campaign']['id']);
    }

    public function afterDelete() {
        // delete attached images in campaign
        $photoModel = MooCore::getInstance()->getModel('Photo.Photo');
        $photos = $photoModel->find('all', array('conditions' => array('Photo.type' => 'Campaign',
            'Photo.target_id' => $this->id)));
        foreach ($photos as $p){
            $photoModel->delete($p['Photo']['id']);
        }
    }
    
    public function getHref($row)
    {
    	$request = Router::getRequest();
    	if (isset($row['title']) && isset($row['id']))
    		return $request->base.'/fundraisings/view/'.$row['id'].'/'.seoUrl($row['title']);
    	else 
    		return '';
    }
    
    public function getThumb($row){

        return 'thumbnail';
    }

    public function getTopicHashtags($qid, $limit = RESULTS_LIMIT,$page = 1){
        $cond = array(
            'Topic.id' => $qid,
        );

        //only get topics of active user
        $cond['User.active'] = 1;
        $cond = $this->addBlockCondition($cond);
        $topics = $this->find( 'all', array( 'conditions' => $cond, 'limit' => $limit, 'page' => $page ) );
        return $topics;
    }

    public function updateCounter($id, $field = 'comment_count',$conditions = '',$model = 'Comment') {
        Cache::clearGroup('campaign', 'campaign');
                
        if(empty($conditions)){
            $conditions = array('Comment.type' => 'Fundraising_Campaign', 'Comment.target_id' => $id);
        }
        
        parent::updateCounter($id, $field, $conditions, $model);
    }

    public function updateTotalRaised($id) {
        $modelObj = ClassRegistry::init('CampaignDonor');
        $modelObj->virtualFields['total'] = 'SUM(CampaignDonor.amount)';
        $totalAmount = $modelObj->find('list', array('conditions' => array('CampaignDonor.target_id' => $id, 'CampaignDonor.status = 1'),'fields' => array('total')));
        $this->id = $id;
        $this->saveField('raised_amount', $totalAmount[key($totalAmount)]);
    }

    public function getTotalCampaigns($conditions = array())
    {
        return $this->find('count',array('conditions'=>$conditions));
    }

    public function checkPredefined($check) {
        $value = trim($check['predefined']);
        if(!preg_match('/^\d+(?:,\d+)*$/', $value))
        {
           return __('Predefined only allow numbers and commas');
        }

        return true;
    }
}
