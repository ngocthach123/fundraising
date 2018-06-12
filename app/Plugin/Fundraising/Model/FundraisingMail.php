<?php

/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
App::uses('FundraisingAppModel', 'Fundraising.Model');

class FundraisingMail extends FundraisingAppModel {

    public $validate = array(
        'content' => array(
            'rule' => 'notBlank',
            'message' => 'Content is required'
        ),
        'subject' => array(
            'rule' => 'notBlank',
            'message' => 'Subject is required'
        ),
    );

    public function getMail($id = null){
       return $this->find('first', array(
            'conditions' => array('FundraisingMail.target_id' => $id)
        ));
    }
}
