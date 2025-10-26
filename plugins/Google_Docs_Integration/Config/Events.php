<?php

namespace Google_Docs_Integration\Config;

use CodeIgniter\Events\Events;

Events::on('pre_system', function () {
    helper("google_docs_integration_general");
});