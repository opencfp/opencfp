<?php

/**
 * Set of tests for our Talk object
 */

class TalkTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that creates a new talk
     *
     * @test
     */
    public function properlyCreateANewTalk()
    {
        // Mock our database connection
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
            ->with($this->stringContains("INSERT INTO talks"))
            ->will($this->returnValue($stmt));

        $talk = new \OpenCFP\Talk($db);
        $data = array(
            'title' => "The Awesome Talk of Awesomeoneess",
            'description' => "This is where a description of the talk would go, how long should it be?",
            'type' => 'tutorial',
            'user_id' => 1
        );
        $response = $talk->create($data);

        $this->assertTrue(
            $response,
            "Did now properly create a talk"
        );
    }

    /**
     * Verify that findById() returns a record as expected
     *
     * @test
     */
    public function titleFieldIsValidatedCorrectly()
    {
        $info = array(
            'id' => 2,
            'title' => "Best talk ever",
            'description' => "This is out talk description",
            'type' => 'session',
            'user_id' => 4
        );

        $stmt = $this->getMockBuilder('StdClass')
            ->setMethods(array('execute', 'fetch'))
            ->getMock();
        $stmt->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(true));
        $stmt->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue($info));

        $db = $this->getMockBuilder('PDOMock')
            ->setMethods(array('prepare'))
            ->getMock();
        $db->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains("SELECT * FROM talks"))
            ->will($this->returnValue($stmt));	

        $talk = new \OpenCFP\Talk($db);
        $record = $talk->findById($info['user_id']);

        $this->assertEquals(
            $info,
            $record,
            "Talk::findById() did not return the expected record"
        );
    }

    /**
     * Verify that findByUserId finds one or more talks by a user
     * 
     * @test
     * @param integer $data 
     * @dataProvider findByUserIdProvider
     */
    public function findByUserIdReturnsCorrectRecords($data)
    {
        $stmt = $this->getMockBuilder('StdClass')
            ->setMethods(array('execute', 'fetchAll'))
            ->getMock();
        $stmt->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(true));
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->will($this->returnValue($data));

        $db = $this->getMockBuilder('PDOMock')
            ->setMethods(array('prepare'))
            ->getMock();
        $db->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains("SELECT * FROM talks"))
            ->will($this->returnValue($stmt));			

        $talk = new \OpenCFP\Talk($db);
        $talks = $talk->findByUserId(1);

        $this->assertEquals(
            $data,
            $talks,
            "Did not get the expected talks"
        );
    }

    /**
     * Data provider for findByUserIdReturnsCorrectRecords
     *
     * @return array
     */
    public function findByUserIdProvider()
    {
        return array(
            array(
                array(
                    'id' => 4,
                    'title' => 'Test talk',
                    'description' => 'Test description',
                    'type' => 'session',
                    'user_id' => 1
                )
            ),
            array(
                array(
                    'id' => 4,
                    'title' => 'Test talk',
                    'description' => 'Test description',
                    'type' => 'session',
                    'user_id' => 1
                ),
                array(
                    'id' => 5,
                    'title' => 'Test tutorial',
                    'description' => 'This is where the description of this tutorial goes',
                    'type' => 'tutorial',
                    'user_id' => 1
                )
            )
        );
    }

    /**
     * Test that updating an existing talk works correctly
     *
     * @test
     * @dataProvider updateProvider
     * @param boolean $updateResponse
     * @param integer $rowCount
     */
    public function updateTalkWorksCorrectly($updateResponse, $rowCount)
    {
        $stmt = $this->getMockBuilder('StdClass')
            ->setMethods(array('execute', 'rowCount'))
            ->getMock();
        $stmt->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue($rowCount));
        
        $db = $this->getMockBuilder('PDOMock')
            ->setMethods(array('prepare'))
            ->getMock();
        $db->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains("UPDATE talks"))
            ->will($this->returnValue($stmt));			

        $data = array(
            'id' => 1,
            'title' => 'Test Talk',
            'description' => 'Test description',
            'type' => 'session',
            'user_id' => 1
        );

        $talk = new \OpenCFP\Talk($db);

        $this->assertEquals(
            $updateResponse,
            $talk->update($data),
            '\OpenCFP\Talk::update() did not update valid data'
        );
    }

    /**
     * Data provider for verifyUpdateTalkWorksCorrectly
     *
     * @return array
     */
    public function updateProvider()
    {
        return array(
            array(true, 1),
            array(false, 3),
            array(false, 0)
        );
    }
}
