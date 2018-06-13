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

    public function initMail(){
        $mail['FundraisingMail'] = array();
        $mail['FundraisingMail']['subject'] = 'Thank for your contribution';
        $mail['FundraisingMail']['content'] = <<<EOF
		    <p>Hello [donor_name],</p>
			<p>Thank you for contributing! We're so appreciated and will transfer the money to help people on behalf of you.
            <br/>Your donation has been updated at our campaign. You can visit our fundraising at the following link<br/>
            <a href="[donation_url]">[donation_url]</a>
			</p>
            <p>Thank you again and we look forward to your continued support.</p>
EOF;
        return $mail;
    }
}
