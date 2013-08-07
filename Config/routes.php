<?php

// Single route to connect the /postmark/inbound route in PostmarkAppController.php
Router::connect('/postmark/:action', array('controller' => 'postmark_app', 'plugin' => 'postmark'));
Router::mapResources('Postmark.Postmark');