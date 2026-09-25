<?php
declare(strict_types=1);

/**
 * Projektets rod sender videre til adminpanelet.
 *
 * Det offentlige site er den statiske eksport i /export/ — ikke PHP-filer
 * her. Den gamle prototype, der lå her, brugte ikke længere noget af
 * systemet.
 */
header('Location: admin/index.php');
exit;
