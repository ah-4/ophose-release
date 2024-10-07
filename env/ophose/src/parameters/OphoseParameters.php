<?php

namespace Ophose\Parameters;

class OphoseParameters {

    /**
     * URL of official Ophose website for fetching and pushing data.
     */
    const URL = 'https://ophose.dev/';

    /**
     * Path to the directory where the external resources are stored.
     */
    const EXT_PATH_NAME = 'ext';

    /**
     * Path to the directory where the components are stored.
     */
    const EXT_CPN_PATH_NAME = ROOT . 'components/' . self::EXT_PATH_NAME . '/';

    /**
     * Path to the directory where the external environments are stored.
     */
    const EXT_ENV_PATH_NAME = ROOT . 'env/' . self::EXT_PATH_NAME . '/';

}