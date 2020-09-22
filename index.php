<?php

/**
 * @defgroup plugins_importexport_emailAddresses email addresses export plugin
 */
 
/**
 * @file plugins/importexport/emailAddresses /index.php
 *
 * Copyright (c) 2020 Freie Universität Berlin
 *
 * @ingroup plugins_importexport_emailAddresses 
 * @brief Wrapper for email addresses export plugin.
 *
 */

require_once('EmailAddressExportPlugin.inc.php');

return new EmailAddressExportPlugin();


