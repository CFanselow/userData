<?php

/**
 * @defgroup plugins_importexport_emailAddresses email address export plugin
 */
 
/**
 * @file plugins/importexport/emailAddress/index.php
 *
 * Copyright (c) 2016-2019 Language Science Press
 * Copyright (c) 2020 Freie Universität Berlin
 *
 * @ingroup plugins_importexport_emailAddress 
 * @brief Wrapper for email address export plugin.
 *
 */

require_once('UserDataExportPlugin.inc.php');

return new UserDataExportPlugin();