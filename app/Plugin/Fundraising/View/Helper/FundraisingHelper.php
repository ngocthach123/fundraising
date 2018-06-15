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

        $campaignModel = MooCore::getInstance()->getModel("Fundraising.Campaign");
        $campaigns = $campaignModel->find('all', array(
            'conditions' => array(),
            'limit' => $limit,
            'offset' => $offset
        ));

        $urls = array();
        foreach ($campaigns as $campaign) {
            $urls[] = FULL_BASE_URL . $campaign['Campaign']['moo_href'];
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

    public function getLngLatByAddress($address) {
        if($address != null)
        {
            $address = urlencode($address);
            $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $address . '&sensor=false&key='.Configure::read('core.google_dev_key'));
            $output = json_decode($geocode);
            $lat = !empty($output->results[0]->geometry->location->lat) ? $output->results[0]->geometry->location->lat : 0;
            $lng = !empty($output->results[0]->geometry->location->lng) ? $output->results[0]->geometry->location->lng : 0;
            $lat = str_replace(',', '.', $lat);
            $lng = str_replace(',', '.', $lng);
            return array(
                'lng' => $lng,
                'lat' => $lat
            );
        }
        return array('lng' => 0, 'lat' => 0);
    }

}
