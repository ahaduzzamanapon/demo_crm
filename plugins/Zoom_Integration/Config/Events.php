<?php

namespace Zoom_Integration\Config;

use CodeIgniter\Events\Events;

Events::on('pre_system', function () {
    helper("zoom_integration_general");
});