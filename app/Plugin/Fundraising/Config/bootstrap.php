<?php
if (Configure::read('Fundraising.fundraising_enabled')) {
    App::uses('FundraisingListener', 'Fundraising.Lib');
    CakeEventManager::instance()->attach(new FundraisingListener());
    
}