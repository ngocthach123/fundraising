<?php 
/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
App::uses('MooPlugin','Lib');
class FundraisingPlugin implements MooPlugin{
    public function install(){
        //Permission
        $roleModel = MooCore::getInstance()->getModel('Role');
        $roles = $roleModel->find('all');
        $role_ids = array();
        foreach ($roles as $role)
        {
            $role_ids[] = $role['Role']['id'];
            $params = explode(',',$role['Role']['params']);
            $params = array_unique(array_merge($params,array('fundraising_create','fundraising_view')));
            $roleModel->id = $role['Role']['id'];
            $roleModel->save(array('params'=>implode(',', $params)));
        }

        //Setting
        $settingModel = MooCore::getInstance()->getModel('Setting');
        $setting = $settingModel->findByName('fundraising_enabled');
        if ($setting)
        {
            $settingModel->id = $setting['Setting']['id'];
            $settingModel->save(array('is_boot'=>1));
        }

        //Add Menu
        $languageModel = MooCore::getInstance()->getModel('Language');
        $menuModel = MooCore::getInstance()->getModel('Menu.CoreMenuItem');
        $menu = $menuModel->findByUrl('/fundraisings');
        if (!$menu)
        {
            $menuModel->clear();
            $menuModel->save(array(
                'role_access'=>json_encode($role_ids),
                'name' => 'Fundraising',
                'original_name' => 'Fundraising',
                'url' => '/fundraisings',
                'type' => 'plugin',
                'is_active' => 1,
                'menu_order'=> 999,
                'menu_id' => 1
            ));

            $menu = $menuModel->read();
            $langs = array_keys($languageModel->getLanguages());
            foreach ($langs as $lKey) {
                $menuModel->locale = $lKey;
                $menuModel->id = $menu['CoreMenuItem']['id'];
                $menuModel->saveField('name', $menu['CoreMenuItem']['name']);
            }
        }

        //add translate page
        $pageModel = MooCore::getInstance()->getModel('Page.Page');
        $blockModel = MooCore::getInstance()->getModel('CoreBlock');
        $i18nModel = MooCore::getInstance()->getModel('I18nModel');

        $languages = $languageModel->find('all');

        $pageModel->Behaviors->unload('Translate');
        $pages = $pageModel->find('all',array(
            'conditions' => array(
                'uri' => array('fundraisings.index','fundraisings.view')
            )
        ));

        foreach ($pages as $page)
        {
            foreach ($languages as $language)
            {
                $i18nModel->clear();
                $i18nModel->save(array(
                    'locale' => $language['Language']['key'],
                    'model' => 'Page',
                    'foreign_key' => $page['Page']['id'],
                    'field' => 'title',
                    'content' => $page['Page']['title']
                ));

                $i18nModel->clear();
                $i18nModel->save(array(
                    'locale' => $language['Language']['key'],
                    'model' => 'Page',
                    'foreign_key' => $page['Page']['id'],
                    'field' => 'content',
                    'content' => $page['Page']['content']
                ));
            }
        }

        $tmp = array();
        foreach ($languages as $language)
        {
            if ($language['Language']['key'] == Configure::read('Config.language'))
                continue;

            $tmp[$language['Language']['key']] = $language;
        }
        $languages = $tmp;
        //add block
        $this->_languages = $languages;
//        $block = $blockModel->find('first',array(
//            'conditions' => array('CoreBlock.path_view' => 'forum.my_contribution')
//        ));
//        $block_contribution_id = $block['CoreBlock']['id'];

        //add block index
        $index_page = $pageModel->findByUri('fundraisings.index');
        if ($index_page)
        {
            $page_id = $index_page['Page']['id'];
            //insert west
            $this->insertPostion($page_id, array(
                'west' => array(
                    array(
                        'page_id' => $page_id,
                        'type' => 'widget',
                        'name' => 'invisiblecontent',
                        'params' => '{"title":"Fundraising menu","maincontent":"1","role_access":"all"}',
                        'plugin' => 'Fundraising',
                        'order' => 0,
                        'core_block_id' => 0,
                        'core_block_title' => 'Fundraising menu'
                    ),
                ),
                'center'=>array(
                    array(
                        'page_id' => $page_id,
                        'type' => 'widget',
                        'name' => 'invisiblecontent',
                        'params' => '{"title":"Fundraising Browse","maincontent":"1","role_access":"all"}',
                        'plugin' => 'Fundraising',
                        'order' => 1,
                        'core_block_id' => 0,
                        'core_block_title' => 'Fundraising Browse'
                    )
                )
            ));

            // update core content count
            $contentModel = MooCore::getInstance()->getModel('CoreContent');
            $contentModel->updateAll(
                array('CoreContent.core_content_count' => '1'),
                array('CoreContent.page_id' => $page_id,'CoreContent.type'=>'container','CoreContent.name'=>'west')
            );
        }

        //add block detail
        $detail_page = $pageModel->findByUri('fundraisings.view');
        if ($detail_page)
        {
            $page_id = $detail_page['Page']['id'];
            //insert west
            $this->insertPostion($page_id, array(
                'west' => array(
                    array(
                        'page_id' => $page_id,
                        'type' => 'widget',
                        'name' => 'invisiblecontent',
                        'params' => '{"title":"Campaign menu","maincontent":"1","role_access":"all"}',
                        'plugin' => 'Fundraising',
                        'order' => 0,
                        'core_block_id' => 0,
                        'core_block_title' => 'Campaign menu'
                    ),
                ),
                'center'=>array(
                    array(
                        'page_id' => $page_id,
                        'type' => 'widget',
                        'name' => 'invisiblecontent',
                        'params' => '{"title":"Campaign Detail","maincontent":"1","role_access":"all"}',
                        'plugin' => 'Fundraising',
                        'order' => 1,
                        'core_block_id' => 0,
                        'core_block_title' => 'Campaign Detail'
                    )
                )
            ));

            // update core content count
            $contentModel = MooCore::getInstance()->getModel('CoreContent');
            $contentModel->updateAll(
                array('CoreContent.core_content_count' => '1'),
                array('CoreContent.page_id' => $page_id,'CoreContent.type'=>'container','CoreContent.name'=>'west')
            );
        }

        //Mail template
        $mailModel = MooCore::getInstance()->getModel('Mail.Mailtemplate');
        $langs = $languageModel->find('all');
        $data['Mailtemplate'] = array(
            'type' => 'fundraising_invite_none_member',
            'plugin' => 'Fundraising',
            'vars' => '[email],[sender_title],[sender_link],[link],[campaign_title]'
        );
        $mailModel->save($data);
        $id = $mailModel->id;
        foreach ($langs as $lang)
        {
            $language = $lang['Language']['key'];
            $mailModel->locale = $language;
            $data_translate['subject'] = 'You have been invited to donate the campaign [campaign_title]';
            $content = <<<EOF
		    <p>[header]</p>
			<p>You have been invited to donate the campaign "[campaign_title]". Please click the following link to view it:</p>
            <p><a href="[link]">[topic_title]</a></p>
			<p>[footer]</p>
EOF;
            $data_translate['content'] = $content;
            $mailModel->save($data_translate);
        }
    }
    public function uninstall(){
        //Permission
        $roleModel = MooCore::getInstance()->getModel('Role');
        $roles = $roleModel->find('all');
        foreach ($roles as $role)
        {
            $params = explode(',',$role['Role']['params']);
            $params = array_diff($params,array('fundraising_create','fundraising_view'));
            $roleModel->id = $role['Role']['id'];
            $roleModel->save(array('params'=>implode(',', $params)));
        }

        //Mail
        $mailModel = MooCore::getInstance()->getModel('Mail.Mailtemplate');
        $mail = $mailModel->findByType('fundraising_invite_none_member');
        if ($mail)
        {
            $mailModel->delete($mail['Mailtemplate']['id']);
        }

        //Category
        $categoryModel = MooCore::getInstance()->getModel('Category');
        $categories = $categoryModel->findAllByType('Fundraising');
        foreach ($categories as $category)
        {
            $categoryModel->delete($category['Category']['id']);
        }

        //Menu
        $menuModel = MooCore::getInstance()->getModel('Menu.CoreMenuItem');
        $menu = $menuModel->findByUrl('/fundraisings');
        if ($menu)
        {
            $menuModel->delete($menu['CoreMenuItem']['id']);
        }

        //Delete S3
        $objectModel = MooCore::getInstance()->getModel("Storage.StorageAwsObjectMap");
        $types = array('campaigns');
        foreach ($types as $type)
            $objectModel->deleteAll(array('StorageAwsObjectMap.type' => $type), false,false);
    }
    public function settingGuide(){}
    public function menu()
    {
        return array(
            __('General') => array('plugin' => 'fundraising', 'controller' => 'fundraising_plugins', 'action' => 'admin_index'),
            __('Settings') => array('plugin' => 'fundraising', 'controller' => 'fundraising_settings', 'action' => 'admin_index'),
            __('Categories') => array('plugin' => 'fundraising', 'controller' => 'fundraising_categories', 'action' => 'admin_index'),
        );
    }

    protected $_languages = null;
    public function insertPostion($page_id,$items)
    {
        $contentModel = MooCore::getInstance()->getModel('CoreContent');
        $i18nModel = MooCore::getInstance()->getModel('I18nModel');
        $languageModel = MooCore::getInstance()->getModel('Language');

        if (!$this->_languages)
        {
            $languages = $languageModel->find('all');
            $tmp = array();
            foreach ($languages as $language)
            {
                if ($language['Language']['key'] == Configure::read('Config.language'))
                    continue;

                $tmp[$language['Language']['key']] = $language;
            }
            $languages = $tmp;
            $this->_languages = $languages;
        }
        else
            $languages = $this->_languages;

        foreach ($items as $type=>$datas)
        {
            $contentModel->clear();
            $contentModel->save(array(
                'page_id' => $page_id,
                'type' => 'container',
                'name' => $type,
            ));
            $type_id = $contentModel->id;
            foreach (array_keys($languages) as $key)
            {
                $i18nModel->clear();
                $i18nModel->save(array(
                    'locale' => $key,
                    'model' => 'CoreContent',
                    'foreign_key' => $type_id,
                    'field' => 'core_block_title',
                    'content' => ''
                ));
            }

            foreach ($datas as $data)
            {
                //insert menu to west
                $data['parent_id'] = $type_id;
                $contentModel->clear();
                $contentModel->save($data);
                $content_id = $contentModel->id;
                foreach (array_keys($languages) as $key)
                {
                    $i18nModel->clear();
                    $i18nModel->save(array(
                        'locale' => $key,
                        'model' => 'CoreContent',
                        'foreign_key' => $content_id,
                        'field' => 'core_block_title',
                        'content' => $data['core_block_title']
                    ));
                }
            }
        }
    }
}