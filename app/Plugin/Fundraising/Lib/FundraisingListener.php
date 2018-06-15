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
            'Plugin.View.Api.Search' => 'apiSearch',

            'StorageHelper.campaigns.getUrl.local' => 'storage_geturl_local',
            'StorageHelper.campaigns.getUrl.amazon' => 'storage_geturl_amazon',
            'StorageAmazon.campaigns.getFilePath' => 'storage_amazon_get_file_path',
            'StorageTaskAwsCronTransfer.execute' => 'storage_task_transfer',

            'ApiHelper.renderAFeed.campaign_create' => 'exportCampaignCreate',
            'ApiHelper.renderAFeed.campaign_item_detail_share' => 'exportCampaignItemDetailShare',
            'ApiHelper.renderAFeed.campaign_donate' => 'exportCampaignDonate',
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
                'are_you_sure_you_want_to_remove_this_campaign' => __d('fundraising',"Are you sure you want to remove this campaign?"),
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

    public function apiSearch($event)
    {
        $view = $event->subject();
        $items = &$event->data['items'];
        $type = $event->data['type'];
        $viewer = MooCore::getInstance()->getViewer();
        $utz = $viewer['User']['timezone'];
        if ($type == 'Fundraising' && isset($view->viewVars['campaigns']) && count($view->viewVars['campaigns']))
        {
            $helper = MooCore::getInstance()->getHelper('Fundraising_Fundraising');
            foreach ($view->viewVars['campaigns'] as $item){
                $items[] = array(
                    'id' => $item["Campaign"]['id'],
                    'url' => FULL_BASE_URL.$item['Campaign']['moo_href'],
                    'avatar' =>  $helper->getImage($item),
                    'owner_id' => $item["Campaign"]['user_id'],
                    'title_1' => $item["Campaign"]['moo_title'],
                    'title_2' => __( 'Posted by') . ' ' . $view->Moo->getNameWithoutUrl($item['User'], false) . ' ' .$view->Moo->getTime( $item["Campaign"]['created'], Configure::read('core.date_format'), $utz ),
                    'created' => $item["Campaign"]['created'],
                    'type' => "Fundraising"
                );
            }
        }
    }

    public function exportCampaignCreate($e)
    {
        $data = $e->data['data'];
        $actorHtml = $e->data['actorHtml'];

        $campaignModel = MooCore::getInstance()->getModel("Fundraising_Campaign");
        $campaign = $campaignModel->findById($data['Activity']['item_id']);
        $helper = MooCore::getInstance()->getHelper('Fundraising_Fundraising');

        list($title_tmp,$target) = $e->subject()->getActivityTarget($data,$actorHtml);
        if(!empty($title_tmp)){
            $title =  $title_tmp['title'];
            $titleHtml = $title_tmp['titleHtml'];
        }else{
            $title = __d('fundraising','created a new campaign');
            $titleHtml = $actorHtml . ' ' . __d('fundraising','created a new campaign');
        }
        $e->result['result'] = array(
            'type' => 'create',
            'title' => $title,
            'titleHtml' => $titleHtml,
            'objects' => array(
                'type' => 'Fundraising_Campaign',
                'id' => $campaign['Campaign']['id'],
                'url' => FULL_BASE_URL . str_replace('?','',mb_convert_encoding($campaign['Campaign']['moo_href'], 'UTF-8', 'UTF-8')),
                'description' => $e->subject()->Text->convert_clickable_links_for_hashtags($e->subject()->Text->truncate(strip_tags(str_replace(array('<br>', '&nbsp;'), array(' ', ''), $campaign['Campaign']['body'])), 200, array('eclipse' => '')), Configure::read('Fundraising.fundraising_hashtag_enabled')),
                'title' => h($campaign['Campaign']['moo_title']),
                'images' => array('850'=>$helper->getImage($campaign,array('prefix'=> ''))),
            ),
            'target' => $target,
        );
    }

    public function exportCampaignItemDetailShare($e)
    {
        $data = $e->data['data'];
        $actorHtml = $e->data['actorHtml'];

        $campaignModel = MooCore::getInstance()->getModel("Fundraising_Campaign");
        $campaign = $campaignModel->findById($data['Activity']['parent_id']);
        $helper = MooCore::getInstance()->getHelper('Fundraising_Fundraising');

        $target = array();

        if (isset($data['Activity']['parent_id']) && $data['Activity']['parent_id'])
        {
            $title = $data['User']['name'] . ' ' . __d('fundraising',"shared %s's campaign", $campaign['User']['name']);
            $titleHtml = $actorHtml . ' ' . __d('fundraising',"shared %s's campaign", $e->subject()->Html->link($campaign['User']['name'], FULL_BASE_URL . $campaign['User']['moo_href']));
            $target = array(
                'url' => FULL_BASE_URL . $campaign['User']['moo_href'],
                'id' => $campaign['User']['id'],
                'name' => $campaign['User']['name'],
                'type' => 'User',
            );
        }

        list($title_tmp,$target) = $e->subject()->getActivityTarget($data,$actorHtml,true);
        if(!empty($title_tmp)){
            $title .=  $title_tmp['title'];
            $titleHtml .= $title_tmp['titleHtml'];
        }

        $e->result['result'] = array(
            'type' => 'share',
            'title' => $title,
            'titleHtml' => $titleHtml,
            'objects' => array(
                'type' => 'Fundraising_Campaign',
                'id' => $campaign['Campaign']['id'],
                'url' => FULL_BASE_URL . str_replace('?','',mb_convert_encoding($campaign['Campaign']['moo_href'], 'UTF-8', 'UTF-8')),
                'description' => $e->subject()->Text->convert_clickable_links_for_hashtags($e->subject()->Text->truncate(strip_tags(str_replace(array('<br>', '&nbsp;'), array(' ', ''), $campaign['Campaign']['body'])), 200, array('eclipse' => '')), Configure::read('Fundraising.fundraising_hashtag_enabled')),
                'title' => h($campaign['Campaign']['moo_title']),
                'images' => array('850'=>$helper->getImage($campaign,array('prefix'=>''))),
            ),
            'target' => $target,
        );
    }

    public function exportCampaignDonate($e)
    {
        $data = $e->data['data'];
        $actorHtml = $e->data['actorHtml'];

        $campaignModel = MooCore::getInstance()->getModel("Fundraising_Campaign");
        $campaign = $campaignModel->findById($data['Activity']['item_id']);
        $helper = MooCore::getInstance()->getHelper('Fundraising_Fundraising');

        list($title_tmp,$target) = $e->subject()->getActivityTarget($data,$actorHtml);
        if(!empty($title_tmp)){
            $title =  $title_tmp['title'];
            $titleHtml = $title_tmp['titleHtml'];
        }else{
            $title = __d('fundraising','donated for a campaign');
            $titleHtml = $actorHtml . ' ' . __d('fundraising','donated for a campaign');
        }
        $e->result['result'] = array(
            'type' => 'create',
            'title' => $title,
            'titleHtml' => $titleHtml,
            'objects' => array(
                'type' => 'Fundraising_Campaign',
                'id' => $campaign['Campaign']['id'],
                'url' => FULL_BASE_URL . str_replace('?','',mb_convert_encoding($campaign['Campaign']['moo_href'], 'UTF-8', 'UTF-8')),
                'description' => $e->subject()->Text->convert_clickable_links_for_hashtags($e->subject()->Text->truncate(strip_tags(str_replace(array('<br>', '&nbsp;'), array(' ', ''), $campaign['Campaign']['body'])), 200, array('eclipse' => '')), Configure::read('Fundraising.fundraising_hashtag_enabled')),
                'title' => h($campaign['Campaign']['moo_title']),
                'images' => array('850'=>$helper->getImage($campaign,array('prefix'=> ''))),
            ),
            'target' => $target,
        );
    }
}
