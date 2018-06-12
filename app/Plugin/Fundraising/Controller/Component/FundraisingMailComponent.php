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
    public function send($recipient, $type, array $params = array())
    {
        if (!isset($params['mail_queueing']))
        {
            if (!$this->_settings['mail_queueing'] || (isset($params['queue']) && $params['queue'] === false))
            {
                return $this->sendRow($recipient,$type,$params);
            }
        }
        if (is_array($recipient))
        {
            $recipient = $recipient['User']['email'];
        }

        $this->Mailrecipient->clear();
        $this->Mailrecipient->save(array(
            'type' => $type,
            'recipient'	=> $recipient,
            'params' => serialize($params),
            'creation_time' => date('Y-m-d H:i:s')
        ));
    }

    function sendRow($recipient, $type, array $rParams = array())
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

        if (!$language)
        {
            if (is_array($recipient))
            {
                if (isset($recipient['User']['lang']) && $recipient['User']['lang'])
                {
                    $language = $recipient['User']['lang'];
                }
            }
        }

        $language = ($language ? $language : $this->_language_default);

        $templete = $this->getTranslateByType($type,$language);

        if (!$templete)
            return;

        $controller = new Controller();
        $controller->getEventManager()->dispatch(new CakeEvent('Mail.Controller.Component.MooMailComponent.BeforeSend', $this,array(
            'rParams' => &$rParams,
            'recipient' => &$recipient,
            'type' => &$type,
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
            $headerPrefixTranslate = $this->getTranslateByType('header_member',$language);
            $bodyHeader = $headerPrefixTranslate['content'];
            $subjectHeader = $headerPrefixTranslate['subject'];

            $footerPrefixTranslate = $this->getTranslateByType('footer_member',$language);
            $bodyFooter = $footerPrefixTranslate['content'];
            $subjectFooter = $footerPrefixTranslate['subject'];

            $recipientEmail = $recipient['User']['email'];
            $recipientName = $recipient['User']['name'];

            $rParams['email'] = $recipientEmail;
            $rParams['recipient_email'] = $recipientEmail;
            $rParams['recipient_title'] = $recipientName;
        }
        else
        {
            $headerPrefixTranslate = $this->getTranslateByType('header',$language);
            $bodyHeader = $headerPrefixTranslate['content'];
            $subjectHeader = $headerPrefixTranslate['subject'];

            $footerPrefixTranslate = $this->getTranslateByType('footer',$language);
            $bodyFooter = $footerPrefixTranslate['content'];
            $subjectFooter = $footerPrefixTranslate['subject'];

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

                $bodyHeader = str_replace($var, $val, $bodyHeader);
                $subjectHeader = str_replace($var, $val, $subjectHeader);

                $bodyFooter = str_replace($var, $val, $bodyFooter);
                $subjectFooter = str_replace($var, $val, $subjectFooter);
            }
        }
        $subjectTemplate  = str_replace('[header]', $subjectHeader, $subjectTemplate);
        $subjectTemplate  = str_replace('[footer]', $subjectFooter, $subjectTemplate);

        $bodyTemplate  = str_replace('[header]', $bodyHeader, $bodyTemplate);
        $bodyTemplate  = str_replace('[footer]', $bodyFooter, $bodyTemplate);

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
            $this->log(sprintf('Send mail (%s) to %s with param %s', $type, $recipientEmail,print_r($rParams,true)));
        } catch (Exception $ex) {
            $this->log($ex->getMessage());
        }
        Configure::write('Config.language',$current_language);
    }
}
