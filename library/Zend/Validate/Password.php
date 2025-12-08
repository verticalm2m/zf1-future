<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Password extends Zend_Validate_Abstract
{
    const LENGTH = 'length';
    const UPPER  = 'upper';
    const LOWER  = 'lower';
    const DIGIT  = 'digit';

    protected $_messageTemplates = array(
        self::LENGTH =>
            "'%value%' doit avoir une longueur d'au moins 8 caractères",
        self::UPPER  =>
            "'%value%' doit contenir au moins une lettre majuscule",
        self::LOWER  =>
            "'%value%' doit contenir au moins une lettre minuscule",
        self::DIGIT  =>
            "'%value%' doit contenir au moins un chiffre"
    );

    public function isValid($value)
    {
        $this->_setValue($value);

        $isValid = true;

        if (strlen($value) < 8 || !preg_match('/[A-Z]/', $value) || !preg_match('/[a-z]/', $value) || !preg_match('/\d/', $value)) {
            $this->_error(self::LENGTH);
            $isValid = false;
        }

        return $isValid;
    }
}