<?php
/**
 * Flixn
 *
 * @category    Flixn
 * @package     Services
 *
 * @author      Samuel J. Greear
 * @copyright   Copyright (c) 2008 Flixn, Inc.
 * @version     $Id $
 */

/**
 * @property string $id
 * @property string $protocol
 * @property string $status
 * @property FlixnServicesMediaLocation[] $locations:location
 * @property FlixnServicesMediaAsset[] $assets:asset
 */
class FlixnServicesMediaMedia extends FlixnServicesReturn {
    public $id;
    public $protocol;
    public $status;
    public $locations;
    public $assets;
}
