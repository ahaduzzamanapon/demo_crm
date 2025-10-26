<?php

namespace Google_Meet_Integration\Config;

use CodeIgniter\Events\Events;

Events::on('pre_system', function () {
    helper("google_meet_integration_general");
});