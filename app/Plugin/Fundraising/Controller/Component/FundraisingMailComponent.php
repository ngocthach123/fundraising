<?php
/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
App::uses('MooMailComponent', 'Mail.Controller/Component');

class FundraisingMailComponent extends MooMailComponent
{
    public function __construct($request = null, $response = null)
    {
        parent::__construct($request, $response);
        $this->FundraisingMail = ClassRegistry::init('Fundraising.FundraisingMail');
    }

    public function send($recipient, $target_id, array $params = array())
    {
        return $this->sendRow($recipient,$target_id,$params);
    }

    function sendRow($recipient, $target_id, array $rParams = array())
    {
        $language = isset($rParams['lang']) ? $rParams['lang'] : '';
        if (is_string($recipient))
        {
            $user = $this->User->findByEmail($recipient);
            if ($user)
            {
                $recipient = $user;
            }
        }

        if (is_numeric($recipient))
        {
            $user = $this->User->findById($recipient);
            if ($user)
            {
                $recipient = $user;
            }
            else
                return;
        }

        $templete = $this->FundraisingMail->getMail($target_id);

        if (empty($templete)) {
            $templete = $this->FundraisingMail->initMail();
        }
        $templete = $templete['FundraisingMail'];

        $controller = new Controller();
        $controller->getEventManager()->dispatch(new CakeEvent('Mail.Controller.Component.MooMailComponent.BeforeSend', $this,array(
            'rParams' => &$rParams,
            'recipient' => &$recipient,
            'type' => '',
            'template' => &$templete,
            'language' => &$language
        )));

        $is_user = false;

        if (is_array($recipient))
        {
            $is_user = true;
        }

        $subjectTemplate = $templete['subject'];
        $bodyTemplate = $templete['content'];

        if ($is_user)
        {
            $recipientEmail = $recipient['User']['email'];
            $recipientName = $recipient['User']['name'];

            $rParams['email'] = $recipientEmail;
            $rParams['recipient_email'] = $recipientEmail;
            $rParams['recipient_title'] = $recipientName;
        }
        else
        {
            $recipientEmail = $recipient;
            $recipientName = $recipient;

            $rParams['email'] = $recipientEmail;
            $rParams['recipient_email'] = $recipientEmail;
            $rParams['recipient_title'] = $recipientName;
        }

        if (isset($rParams['subject']))
        {
            $subjectTemplate = $rParams['subject'];
        }

        if (isset($rParams['body']))
        {
            $bodyTemplate = $rParams['body'];
        }

        foreach( $rParams as $var => $val ) {
            if (is_string($val) && $var != 'element')
            {
                $raw = trim($var, '[]');
                $var = '[' . $var . ']';
                //if( !$val ) {
                //  $val = $var;
                //}
                // Fix nbsp
                $val = str_replace('&amp;nbsp;', ' ', $val);
                $val = str_replace('&nbsp;', ' ', $val);
                // Replace
                $subjectTemplate  = str_replace($var, $val, $subjectTemplate);
                $bodyTemplate  = str_replace($var, $val, $bodyTemplate);

            }
        }

        $current_language = Configure::read('Config.language');
        Configure::write('Config.language',$language);

        if (isset($rParams['element']))
        {
            $options = array();
            if (isset($rParams['plugin']))
                $options['plugin'] = $rParams['plugin'];

            $content = $this->_moo_view->element($rParams['element'], $rParams,$options);
            $bodyTemplate  = str_replace('[element]', $content, $bodyTemplate);
        }

        $email = $this->getMooCakeMail();
        $template = 'Mail.default';
        $layout = 'Mail.default';

        if (isset($rParams['template']))
        {
            $template = $rParams['template'];
        }

        if (isset($rParams['layout']))
        {
            $layout = $rParams['layout'];
        }

        if (isset($rParams['attachments']))
        {
            $email->attachments($rParams['attachments']);
        }

        if (isset($rParams['messageId']))
        {
            $email->messageId($rParams['messageId']);
        }

        if (isset($rParams['domain']))
        {
            $email->domain($rParams['domain']);
        }

        $email->to($recipientEmail,$recipientName)
            ->emailFormat('html')
            ->template($template,$layout)
            ->helpers(array('Moo'))
            ->subject($subjectTemplate)
            ->viewVars($rParams);

        try{
            $email->send($bodyTemplate);
        } catch (Exception $ex) {
            $this->log($ex->getMessage());
        }
        Configure::write('Config.language',$current_language);
    }
}
