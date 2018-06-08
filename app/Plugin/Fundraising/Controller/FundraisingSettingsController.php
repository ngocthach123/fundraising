<?php

/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
class FundraisingSettingsController extends FundraisingAppController {

    public $components = array('QuickSettings');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->loadModel('Setting');
        $this->loadModel('SettingGroup');
        $this->loadModel('Plugin');
        $this->loadModel('Menu.CoreMenuItem');
    }

    public function admin_index($id = null) {
        // clear cache menu
        Cache::clearGroup('menu', 'menu');

        $this->QuickSettings->run($this, array("Fundraising"), $id);
        $this->set('title_for_layout', __('Fundraising Setting'));
    }

}
