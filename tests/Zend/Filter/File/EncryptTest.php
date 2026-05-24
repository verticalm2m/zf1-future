<?php

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

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
 * @package    Zend_Filter
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Filter_Encrypt
 */
require_once 'Zend/Filter/File/Encrypt.php';
require_once 'Zend/Filter/File/Decrypt.php';

/**
 * @category   Zend
 * @package    Zend_Filter
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Filter
 */
class Zend_Filter_File_EncryptTest extends TestCase
{
    protected function set_up()
    {
        if (!extension_loaded('openssl')) {
            $this->markTestSkipped('This filter needs the openssl extension');
        }

        if (!in_array('rc4', openssl_get_cipher_methods(), true)) {
            $this->markTestSkipped('This filter needs a version of OpenSSL that supports the RC4 cipher');
        }

        if (empty(array_intersect(['md5', 'md5-rsa'], openssl_get_md_methods()))) {
            $this->markTestSkipped('The keys used by this test need a version of OpenSSL that supports MD5 and MD5-RSA as message digest');
        }

        if (file_exists(dirname(__FILE__) . '/../_files/newencryption.txt')) {
            unlink(dirname(__FILE__) . '/../_files/newencryption.txt');
        }
    }

    protected function tear_down()
    {
        if (file_exists(dirname(__FILE__) . '/../_files/newencryption.txt')) {
            unlink(dirname(__FILE__) . '/../_files/newencryption.txt');
        }
    }

    protected function getPassphrase()
    {
        return 'zPUp9mCzIrM7xQOEnPJZiDkBwPBV9UlITY0Xd3v4bfIwzJ12yPQCAkcR5BsePGVw
RK6GS5RwXSLrJu9Qj8+fk0wPj6IPY5HvA9Dgwh+dptPlXppeBm3JZJ+92l0DqR2M
ccL43V3Z4JN9OXRAfGWXyrBJNmwURkq7a2EyFElBBWK03OLYVMevQyRJcMKY0ai+
tmnFUSkH2zwnkXQfPUxg9aV7TmGQv/3TkK1SziyDyNm7GwtyIlfcigCCRz3uc77U
Izcez5wgmkpNElg/D7/VCd9E+grTfPYNmuTVccGOes+n8ISJJdW0vYX1xwWv5l
bK22CwD/l7SMBOz4M9XH0Jb0OhNxLza4XMDu0ANMIpnkn1KOcmQ4gB8fmAbBt';
    }

    /**
     * Ensures that the filter follows expected behavior
     *
     * @return void
     */
    public function testBasic()
    {
        $filter = new Zend_Filter_File_Encrypt();
        $filter->setFilename(dirname(__FILE__) . '/../_files/newencryption.txt');
        $filter->setPublicKey(dirname(__FILE__) . '/../_files/publickey.pem');

        $this->assertEquals(
            dirname(__FILE__) . '/../_files/newencryption.txt',
            $filter->getFilename()
        );

        $this->assertEquals(
            dirname(__FILE__) . '/../_files/newencryption.txt',
            $filter->filter(dirname(__FILE__) . '/../_files/encryption.txt')
        );

        $this->assertEquals(
            'Encryption',
            file_get_contents(dirname(__FILE__) . '/../_files/encryption.txt')
        );

        $this->assertNotEquals(
            'Encryption',
            file_get_contents(dirname(__FILE__) . '/../_files/newencryption.txt')
        );
    }

    public function testEncryptionWithDecryption()
    {
        $filter = new Zend_Filter_File_Encrypt();
        $filter->setFilename(dirname(__FILE__) . '/../_files/newencryption.txt');
        $filter->setPublicKey(dirname(__FILE__) . '/../_files/publickey.pem');
        $this->assertEquals(
            dirname(__FILE__) . '/../_files/newencryption.txt',
            $filter->filter(dirname(__FILE__) . '/../_files/encryption.txt')
        );

        $envelopeKeys = $filter->getEnvelopeKey();

        $this->assertNotEquals(
            'Encryption',
            file_get_contents(dirname(__FILE__) . '/../_files/newencryption.txt')
        );

        $filter = new Zend_Filter_File_Decrypt();
        $filter->setPassphrase($this->getPassphrase());
        $filter->setPrivateKey(dirname(__FILE__) . '/../_files/privatekey.pem');
        $filter->setEnvelopeKey($envelopeKeys);
        $input = $filter->filter(dirname(__FILE__) . '/../_files/newencryption.txt');
        $this->assertEquals(dirname(__FILE__) . '/../_files/newencryption.txt', $input);

        $this->assertEquals(
            'Encryption',
            trim(file_get_contents(dirname(__FILE__) . '/../_files/newencryption.txt'))
        );
    }

    /**
     * @return void
     */
    public function testNonExistingFile()
    {
        $filter = new Zend_Filter_File_Encrypt();

        try {
            $filter->filter(dirname(__FILE__) . '/../_files/nofile.txt');
            $this->fail();
        } catch (Zend_Filter_Exception $e) {
            $this->assertStringContainsString('not found', $e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function testEncryptionInSameFile()
    {
        $filter = new Zend_Filter_File_Encrypt();
        $filter->setPublicKey(dirname(__FILE__) . '/../_files/publickey.pem');

        copy(dirname(__FILE__) . '/../_files/encryption.txt', dirname(__FILE__) . '/../_files/newencryption.txt');
        $filter->filter(dirname(__FILE__) . '/../_files/newencryption.txt');

        $this->assertNotEquals(
            'Encryption',
            trim(file_get_contents(dirname(__FILE__) . '/../_files/newencryption.txt'))
        );
    }
}
