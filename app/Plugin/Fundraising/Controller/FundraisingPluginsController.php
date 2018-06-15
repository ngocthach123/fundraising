<?php

/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
class FundraisingPluginsController extends FundraisingAppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->loadModel('Fundraising.Campaign');
        $this->loadModel('Category');
    }

    public function admin_index() {


        $cond = array();

        if (!empty($this->request->data['keyword']))
            $cond['Campaign.title LIKE'] = '%'.$this->request->data['keyword'].'%';

        $categories = $this->Category->getCategoriesList('Fundraising');
        $campaigns = $this->paginate('Campaign', $cond);

        $this->set('campaigns', $campaigns);
        $this->set('categories', $categories);
        
        $this->set('title_for_layout', __d('fundraising','Fundraising Manager'));
    }

    public function admin_delete() {
        $this->_checkPermission(array('super_admin' => 1));

        if (!empty($_POST['campaigns'])) {

            $campaigns = $this->Campaign->findAllById( $_POST['campaigns'] );
            
            foreach ($campaigns as $campaign){
                
                $this->Campaign->deleteCampaign($campaign);
                
                $cakeEvent = new CakeEvent('Plugin.Controller.Campaign.afterDeleteCampaign', $this, array('item' => $campaign));
                $this->getEventManager()->dispatch($cakeEvent);
            }

            $this->Session->setFlash( __d('fundraising', 'Campaigns have been deleted'), 'default', array('class' => 'Metronic-alerts alert alert-success fade in' ) );
        }

        $this->redirect( array(
            'plugin' => 'fundraising',
            'controller' => 'fundraising_plugins',
            'action' => 'admin_index'
        ) );
    }
    
    public function admin_move() {
        if (!empty($_POST['campaigns']) && !empty($this->request->data['category'])) {
            foreach ($_POST['campaigns'] as $campaign_id) {
                $this->Campaign->id = $campaign_id;
                $this->Campaign->save(array('category_id' => $this->request->data['category']));
            }

            $this->Session->setFlash( __d('fundraising', 'Campaigns has been moved'), 'default', array('class' => 'Metronic-alerts alert alert-success fade in' ) );
        }

        $this->redirect($this->referer());
    }

}
