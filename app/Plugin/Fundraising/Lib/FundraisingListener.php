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

            'StorageHelper.fundraisings.getUrl.local' => 'storage_geturl_local',
        );
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
            $url = $v->getImage("campaign/img/noimage/campaign.png");
        }

        $e->result['url'] = $url;
    }
}
