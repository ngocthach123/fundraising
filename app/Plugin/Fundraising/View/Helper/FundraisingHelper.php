<?php
App::uses('AppHelper', 'View/Helper');

class FundraisingHelper extends AppHelper
{
    public $helpers = array('Storage.Storage');

    public function getTagUnionsFundraising($fundraisingids)
    {
        return "SELECT i.id, i.title, i.body, i.like_count, i.created, 'Fundraising_Fundraising' as moo_type, 0 as privacy, i.user_id
						 FROM " . Configure::read('core.prefix') . "fundraisings i
						 WHERE i.id IN (" . implode(',', $fundraisingids) . ")";
    }

    public function getEnable()
    {
        return Configure::read('Fundraising.fundraising_enabled');
    }

    public function getItemSitemMap($name, $limit, $offset)
    {
        if (!MooCore::getInstance()->checkPermission(null, 'fundraising_view'))
            return null;

        $fundraisingModel = MooCore::getInstance()->getModel("Fundraising.Fundraising");
        $fundraisings = $fundraisingModel->find('all', array(
            'conditions' => array('Fundraising.group_id' => 0),
            'limit' => $limit,
            'offset' => $offset
        ));

        $urls = array();
        foreach ($fundraisings as $fundraising) {
            $urls[] = FULL_BASE_URL . $fundraising['Fundraising']['moo_href'];
        }

        return $urls;
    }

    public function getImage($item, $options)
    {
        $prefix = (isset($options['prefix'])) ? $options['prefix'] . '_' : '';
        return $this->Storage->getUrl($item[key($item)]['id'], $prefix, $item[key($item)]['thumbnail'], "campaigns");

    }

    public function checkPostStatus($fundraising, $uid)
    {
        $cuser = MooCore::getInstance()->getViewer();

        if (isset($cuser) && $cuser['Role']['is_admin']) {
            return true;
        }

        return true;
    }

    public function checkSeeComment($fundraising, $uid)
    {
        return true;
    }

}
