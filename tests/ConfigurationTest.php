<?php
namespace OpenCFP;

class ConfigurationTest extends \PHPUnit_Framework_TestCase {

    public function environmentProvider()
    {
        return array(
            array( // PDO DSN Default
                'name' => Configuration::OPENCFP_PDO_DSN,
                'getter' => 'getPDODSN',
                'value' => null,
                'expected' => 'sqlite::memory:',
            ),
            array( // PDO DSN Environment
                'name' => Configuration::OPENCFP_PDO_DSN,
                'getter' => 'getPDODSN',
                'value' => 'mysql:dbname=cfp;host=localhost',
                'expected' => 'mysql:dbname=cfp;host=localhost',
            ),
            array( // PDO User Default
                'name' => Configuration::OPENCFP_PDO_USER,
                'getter' => 'getPDOUser',
                'value' => null,
                'expected' => 'root',
            ),
            array( // PDO User Environment
                'name' => Configuration::OPENCFP_PDO_USER,
                'getter' => 'getPDOUser',
                'value' => 'testUserName',
                'expected' => 'testUserName',
            ),
            array( // PDO Password Default
                'name' => Configuration::OPENCFP_PDO_PASSWORD,
                'getter' => 'getPDOPassword',
                'value' => null,
                'expected' => '',
            ),
            array( // PDO Password Environment
                'name' => Configuration::OPENCFP_PDO_PASSWORD,
                'getter' => 'getPDOPassword',
                'value' => 'testPassword',
                'expected' => 'testPassword',
            ),
            array( // SMTP Host Default
                'name' => Configuration::OPENCFP_SMTP_HOST,
                'getter' => 'getSMTPHost',
                'value' => null,
                'expected' => '127.0.0.1',
            ),
            array( // SMTP Host Environment
                'name' => Configuration::OPENCFP_SMTP_HOST,
                'getter' => 'getSMTPHost',
                'value' => 'mail.example.com',
                'expected' => 'mail.example.com',
            ),
            array( // SMTP Port Default
                'name' => Configuration::OPENCFP_SMTP_PORT,
                'getter' => 'getSMTPPort',
                'value' => null,
                'expected' => '25',
            ),
            array( // SMTP Port Environment
                'name' => Configuration::OPENCFP_SMTP_PORT,
                'getter' => 'getSMTPPort',
                'value' => '2525',
                'expected' => '2525',
            ),
            array( // SMTP User Default
                'name' => Configuration::OPENCFP_SMTP_USER,
                'getter' => 'getSMTPUser',
                'value' => null,
                'expected' => false,
            ),
            array( // SMTP User Environment
                'name' => Configuration::OPENCFP_SMTP_USER,
                'getter' => 'getSMTPUser',
                'value' => 'account@example.com',
                'expected' => 'account@example.com',
            ),
            array( // SMTP Password Default
                'name' => Configuration::OPENCFP_SMTP_PASSWORD,
                'getter' => 'getSMTPPassword',
                'value' => null,
                'expected' => false,
            ),
            array( // SMTP Password Environment
                'name' => Configuration::OPENCFP_SMTP_PASSWORD,
                'getter' => 'getSMTPPassword',
                'value' => 'secret',
                'expected' => 'secret',
            ),
        );
    }

    /**
     * @test
     * @param array $data
     * @dataProvider environmentProvider
     */
    public function getFromEnvironment($name, $getter, $value, $expected)
    {
        $setEnvironment = $name . (is_null($value) ? '' : '=' . $value);
        putenv($setEnvironment);
        $configuration = new Configuration();
        $actual = call_user_func(array($configuration, $getter));
        $this->assertEquals($expected, $actual);
    }

}
