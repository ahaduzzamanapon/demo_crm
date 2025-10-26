<?php

namespace Whiteboards\Config;

use CodeIgniter\Events\Events;

Events::on('pre_system', function () {
    helper("whiteboards_general");
});