<?php

/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
App::uses('CakeEventListener', 'Event');

class FundraisingListener implements CakeEventListener
{

    public function implementedEvents()
    {
        return array(
            'MooView.beforeRender' => 'beforeRender',
            'Controller.Comment.afterComment' => 'afterComment',
            'Controller.Search.search' => 'search',
            'Controller.Search.suggestion' => 'suggestion',
            'profile.afterRenderMenu'=> 'profileAfterRenderMenu',

            'StorageHelper.campaigns.getUrl.local' => 'storage_geturl_local',
            'StorageHelper.campaigns.getUrl.amazon' => 'storage_geturl_amazon',
            'StorageAmazon.campaigns.getFilePath' => 'storage_amazon_get_file_path',

            'StorageTaskAwsCronTransfer.execute' => 'storage_task_transfer',
        );
    }

    public function storage_geturl_local($e)
    {
        $v = $e->subject();
        $request = Router::getRequest();
        $oid = $e->data['oid'];
        $type = $e->data['type'];
        $thumb = $e->data['thumb'];
        $prefix = $e->data['prefix'];

        if ($e->data['thumb']) {
            $url = FULL_BASE_LOCAL_URL . $request->webroot . 'uploads/campaigns/thumbnail/' . $oid . '/' . $prefix . $thumb;
        } else {
            $url = $v->getImage("fundraising/img/noimage/campaign.png");
        }

        $e->result['url'] = $url;
    }

    public function storage_geturl_amazon($e)
    {
        $v = $e->subject();
        $type = $e->data['type'];
        $e->result['url'] = $v->getAwsURL($e->data['oid'], $type, $e->data['prefix'], $e->data['thumb']);
    }

    public function storage_amazon_get_file_path($e)
    {
        $objectId = $e->data['oid'];
        $name = $e->data['name'];
        $thumb = $e->data['thumb'];
        $type = $e->data['type'];
        $path = false;

        if (!empty($thumb)) {
            $path = WWW_ROOT . "uploads" . DS . "campaigns" . DS . "thumbnail" . DS . $objectId . DS . $name . $thumb;
        }

        $e->result['path'] = $path;
    }

    public function storage_task_transfer($e)
    {
        $v = $e->subject();
        $campaignModel = MooCore::getInstance()->getModel('Fundraising.Campaign');
        $campaigns = $campaignModel->find('all', array(
                'conditions' => array("Campaign.id > " => $v->getMaxTransferredItemId("fundraisings")),
                'limit' => 10,
                'order' => array('Campaign.id'),
            )
        );

        if($campaigns){
            foreach($campaigns as $campaign){
                if (!empty($campaign["Campaign"]["thumbnail"])) {
                    $v->transferObject($campaign["Campaign"]['id'],"campaigns",'',$campaign["Campaign"]["thumbnail"]);
                }
            }
        }
    }

    public function beforeRender($event)
    {
        if (Configure::read('Fundraising.fundraising_enabled')) {
            $e = $event->subject();

            $e->Helpers->Html->css(array(
                'Fundraising.main',
            ),
                array('block' => 'css')
            );

            if (Configure::read('debug') == 0) {
                $min = "min.";
            } else {
                $min = "";
            }
            $e->Helpers->MooRequirejs->addPath(array(
                "mooFundraising" => $e->Helpers->MooRequirejs->assetUrlJS("Fundraising.js/main.{$min}js"),
            ));

            $e->addPhraseJs(array(
                'are_you_sure_you_want_to_remove_this_campaign' => __("Are you sure you want to remove this campaign?"),
            ));
        }
    }

    public function afterComment($event){
        $data = $event->data['data'];
        $target_id = isset($data['target_id']) ? $data['target_id'] : null;
        $type = isset($data['type']) ? $data['type'] : '';
        if ($type == 'Fundraising_Campaign' && !empty($target_id)){
            $campaignModel = MooCore::getInstance()->getModel('Fundraising.Campaign');
            $campaignModel->updateCounter($target_id);
        }
    }

    public function search($event)
    {
        if(Configure::read('Fundraising.fundraising_enabled')){
            $e = $event->subject();
            $campaignModel = MooCore::getInstance()->getModel('Fundraising_Campaign');
            $results = $campaignModel->getCampaigns('search', $e->keyword, 1, 5);

            if(isset($e->plugin) && $e->plugin == 'Fundraising')
            {
                $e->set('campaigns', $results);
                $e->render("Fundraising.Elements/lists/campaigns_list");
                $e->set('no_list_id',true);
            }
            else
            {
                $event->result['Fundraising']['header'] = __d('fundraising',"Fundraising");
                $event->result['Fundraising']['icon_class'] = "monetization_on";
                $event->result['Fundraising']['view'] = "lists/campaigns_list";
                $e->set('no_list_id',true);
                if(!empty($results))
                    $event->result['Fundraising']['notEmpty'] = 1;
                $e->set('fundraising', $results);
            }
        }
    }

    public function suggestion($event)
    {
        if(Configure::read('Fundraising.fundraising_enabled')){
            $e = $event->subject();
            $campaignModel = MooCore::getInstance()->getModel('Fundraising_Campaign');

            $event->result['fundraising']['header'] = __d('fundraising',"Fundraising");
            $event->result['fundraising']['icon_class'] = 'monetization_on';

            if(isset($event->data['type']) && $event->data['type'] == 'fundraising')
            {
                $page = (!empty($e->request->named['page'])) ? $e->request->named['page'] : 1;
                $campaigns = $campaignModel->getCampaigns('search', $event->data['searchVal'], $page);
                $more_campaigns = $campaignModel->getCampaigns('search', $event->data['searchVal'], $page + 1);

                $e->set('campaigns', $campaigns);
                $e->set('result',1);
                $e->set('no_list_id',true);

                if ($more_campaigns && count($more_campaigns))
                    $e->set('is_view_more',true);

                $e->set('url_more','/search/suggestion/fundraising/'.$e->params['pass'][1]. '/page:' . ( $page + 1 ));
                $e->set('element_list_path',"Fundraising.lists/campaigns_list");
            }
            if(isset($event->data['type']) && $event->data['type'] == 'all')
            {
                $event->result['fundraising'] = null;
                $campaigns = $campaignModel->getCampaigns('search', $event->data['searchVal'], 1, 2);
                $helper = MooCore::getInstance()->getHelper('Fundraising_Fundraising');
                $mooHelper = MooCore::getInstance()->getHelper('Core_Moo');

                if(!empty($campaigns)){
                    $event->result['fundraising'] = array(__d('fundraising','Fundraising'));
                    foreach($campaigns as $index=>$detail){
                        $index++;
                        $event->result['fundraising'][$index]['id'] = $detail['Campaign']['id'];
                        $event->result['fundraising'][$index]['img'] = $helper->getImage($detail,array('prefix'=>'75_square'));

                        $event->result['fundraising'][$index]['title'] = $detail['Campaign']['title'];
                        $event->result['fundraising'][$index]['find_name'] = __d('fundraising','Find Fundraising');
                        $event->result['fundraising'][$index]['icon_class'] = 'monetization_on';
                        $event->result['fundraising'][$index]['view_link'] = 'fundraisings/view/';

                        $event->result['fundraising'][$index]['more_info'] = __d('fundraising','Posted by') . ' ' . $mooHelper->getNameWithoutUrl($detail['User'], false) . ' ' . $mooHelper->getTime( $detail['Campaign']['created'], Configure::read('core.date_format'), $e->viewVars['utz'] );
                    }
                }
            }
        }
    }

    public function profileAfterRenderMenu($event)
    {
        $view = $event->subject();
        $uid = MooCore::getInstance()->getViewer(true);
        if(Configure::read('Fundraising.fundraising_enabled')){
            $campaignModel = MooCore::getInstance()->getModel('Fundraising_Campaign');
            $subject = MooCore::getInstance()->getSubject();
            $total = $campaignModel->getTotalCampaigns(array('Campaign.user_id'=>$subject['User']['id']));
            echo $view->element('menu_profile',array('count'=>$total),array('plugin'=>'Fundraising'));
        }
    }
}
