<?php

/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
App::uses('FundraisingAppModel', 'Fundraising.Model');

class CampaignDonor extends FundraisingAppModel {


    public $belongsTo = array('User');

    public $order = "CampaignDonor.id desc";
    public $validate = array(
        'amount' =>array(
            'required'  => array(
                'rule' => 'notBlank',
                'message' => 'Your donation is required'
                ),
            'number'  => array(
                'rule' => 'numeric',
                'message' => 'Your donation is not valid'
            ),
        ),
        'name' => array(
            'rule' => 'notBlank',
            'message' => 'Your name is required'
        ),
        'email' => array(
            'rule' => 'notBlank',
            'message' => 'Your email is required'
        ),
    );

    public function getDonors($id, $page = 1, $limit = RESULTS_LIMIT){
        $results = $this->find('all', array(
            'conditions' => array(
                'CampaignDonor.target_id' => $id,
                'CampaignDonor.status <>' => 2,
            ),
            'limit' => $limit,
            'page' => $page
        ));

        return $results;
    }

    public function updateStatus($id, $status = 0){
        $this->id = $id;
        $this->saveField('status', $status);
    }
}
