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
        $fundraising_enabled = Configure::read('Fundraising.fundraising_enabled');
        if (!$fundraising_enabled) {
            $fundraisings_menu = $this->CoreMenuItem->find('first', array(
                'conditions' => array('url' => '/fundraising', 'type' => 'page')
            ));
            if ($fundraisings_menu['CoreMenuItem']['id']) {
                $this->CoreMenuItem->id = $fundraisings_menu['CoreMenuItem']['id'];
                $this->CoreMenuItem->save(array('is_active' => 0));
            } else {
                $this->CoreMenuItem->set(array(
                    'name' => 'Fundraising',
                    'url' => '/fundraising',
                    'is_active' => 0,
                    'menu_id' => 1,
                    'type' => 'page',
                    'menu_order' => 999
                ));
                $this->CoreMenuItem->save();
            }

        } else {
            $fundraisings_menu = $this->CoreMenuItem->find('first', array(
                'conditions' => array('url' => '/fundraising', 'type' => 'page')
            ));
            if ($fundraisings_menu['CoreMenuItem']['id']) {
                $this->CoreMenuItem->id = $fundraisings_menu['CoreMenuItem']['id'];
                $this->CoreMenuItem->save(array('is_active' => 1));
            } else {
                $this->CoreMenuItem->set(array(
                    'name' => 'Fundraising',
                    'url' => '/fundraising',
                    'is_active' => 1,
                    'menu_id' => 1,
                    'type' => 'page',
                    'menu_order' => 999
                ));
                $this->CoreMenuItem->save();
            }
        }
        
        // clear cache menu
        Cache::clearGroup('menu', 'menu');

        $this->QuickSettings->run($this, array("Fundraising"), $id);
        $this->set('title_for_layout', __('Fundraising Setting'));
    }

}
