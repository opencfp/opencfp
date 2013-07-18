<?php

class SpeakerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Verify that creating a new Speaker record in the database
     * when given complete data
     *
     * @test
     */
    public function createNewSpeakerUsingCompleteData()
    {
        // Mock out PDO statments and PDO object
        $stmt = $this->getMockBuilder('StdClass')
            ->setMethods(array('execute'))
            ->getMock();
        $stmt->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(true));

        $db = $this->getMockBuilder('PDOMock')
            ->setMethods(array('prepare'))
            ->getMock();
        $db->expects($this->once())
            ->method('prepare')
            ->will($this->returnValue($stmt));

        $speaker = new \OpenCFP\Speaker($db);
        $data = array(
            'user_id' => 1,
            'info' => 'Test info'
        );
        $response = $speaker->create($data);

        $this->assertTrue(
            $response,
            "You didn't create a new Speaker as expected"
        );
    }

    /**
     * The create() method should reject any attempts to create records
     * in the database using incomplete info
     *
     * @test
     */
    public function createRejectsAttemptsToSaveIncompleteRecords()
    {
        $data = array();
        $db = null;

        $speaker = new \OpenCFP\Speaker($db);

        $this->assertFalse(
            $speaker->create($data),
            "Speaker::create() did not reject attempt to save incomplete record"
        );
    }

    /**
     * The findByUserId() method should return correct info when given a
     * user_id that exists in the database
     *
     * @test
     */
    public function findReturnsCorrectInfo()
    {
        $response = array('info' => 'Test speaker info');

        // Mock out PDO statments and PDO object
        $stmt = $this->getMockBuilder('StdClass')
            ->setMethods(array('execute', 'fetch'))
            ->getMock();
        $stmt->expects($this->once())
            ->method('execute');
        $stmt->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue($response));

        $db = $this->getMockBuilder('PDOMock')
            ->setMethods(array('prepare'))
            ->getMock();
        $db->expects($this->once())
            ->method('prepare')
            ->will($this->returnValue($stmt));

        $speaker = new \OpenCFP\Speaker($db);
        $testResponse = $speaker->findByUserId(1);

        $this->assertEquals(
            $response,
            $testResponse,
            "Did not get back expected response"
        );
    }

    /**
     * The findByUserId() method should return false if we send it
     * a non-integer value or pass it nothing
     *
     * @test
     * @dataProvider findDataProvider
     * @param mixed $user_id
     */
    public function findByUserIdCorrectlyHandlesBadUserId($user_id)
    {
        $stmt = $this->getMockBuilder('StdClass')
            ->setMethods(array('execute', 'fetch'))
            ->getMock();
        $stmt->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(true));
        $stmt->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(false));

        $db = $this->getMockBuilder('PDOMock')
            ->setMethods(array('prepare'))
            ->getMock();
        $db->expects($this->once())
            ->method('prepare')
            ->will($this->returnValue($stmt));

        $speaker = new \OpenCFP\Speaker($db);

        $this->assertFalse(
            $speaker->findByUserId($user_id),
            "Speaker::findByUserId() did not correctly handle bad user_id values"
        );
    }

    /**
     * Data provider for findByUserIdCorrectlyHandlesBadUserId
     *
     * @return array
     */
    public function findDataProvider()
    {
        return array(
            array('bad_user_id'),
            array(null),
            array(true),
            array(false)
        );
    }

    /**
     * Verify that updating speaker info works correctly
     *
     * @test
     * @dataProvider updateSpeakerProvider
     * @param boolean $expected_response
     * @param integer $row_count
     */
    public function updateSpeakerBehavesAsExpected($expected_response, $row_count)
    {
        $speaker_info = $speaker_info_old = array(
            'user_id' => 1,
            'email' => 'test@domain.com',
            'first_name' => 'Testy',
            'last_name' => 'McTesterton',
            'speaker_info' => 'Speaker info',
            'speaker_bio' => 'Speaker bio'
        );

        $speaker_info_old['last_name'] = 'MacTesterton';
        $speaker_info_old['info'] = 'my old info';
        $speaker_info_old['bio'] = 'my old bio';

        $stmt = $this->getMockBuilder('StdClass')
            ->setMethods(array('execute', 'rowCount', 'fetch'))
            ->getMock();
        $stmt->expects($this->any())
            ->method('rowCount')
            ->will($this->returnValue($row_count));
        $stmt->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue($speaker_info_old));

        $db = $this->getMockBuilder('PDOMock')
            ->setMethods(array('prepare'))
            ->getMock();
        $db->expects($this->any())
            ->method('prepare')
            ->will($this->returnValue($stmt));

        $speaker = new \OpenCFP\Speaker($db);
        $response = $speaker->update($speaker_info);

        $this->assertEquals(
            $expected_response,
            $response,
            "Speaker::upate() did return expected result"
        );
    }

    /**
     * Data provider for updateSpeakerBehavesAsExpected
     */
    public function updateSpeakerProvider()
    {
        return array(
            array(true, 1),
            array(false, 3),
            array(false, 0)
        );
    }
}

