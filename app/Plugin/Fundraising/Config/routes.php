<?php
if (Configure::read('Fundraising.fundraising_enabled')) {
    Router::connect("/fundraisings/:action/*", array(
        'plugin' => 'Fundraising',
        'controller' => 'fundraisings'
    ));

    Router::connect("/fundraisings/*", array(
        'plugin' => 'Fundraising',
        'controller' => 'fundraisings',
        'action' => 'index'
    ));
}

