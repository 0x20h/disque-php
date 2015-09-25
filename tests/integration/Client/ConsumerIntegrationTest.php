<?php

namespace Phloppy\Client;

use Phloppy\Job;

class ConsumerIntegrationTest extends AbstractIntegrationTest {

    public function testGetJob()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 0, 6);
        $client= new Consumer($this->stream);
        $job = $client->getJob($queue, 1);
        $this->assertNull($job);
    }

    public function testAckUnknownJob()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 0, 6);
        $client= new Consumer($this->stream);
        // ack an unknown job
        $job = Job::create(['id' => 'DI37a52bb8dc160e3953111b6a9a7b10f56209320d0002SQ', 'body' => 'foo']);
        $this->assertEquals(0, $client->ack($job));
    }


    public function testAckNewJob()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 0, 6);

        $consumer= new Consumer($this->stream);
        $producer= new Producer($this->stream);
        $job = $producer->addJob($queue, Job::create(['body' => '42']));
        $consumer->getJob($queue);
        $this->assertEquals(1, $consumer->ack($job));
        $this->assertEquals(0, $consumer->ack($job));
    }


    public function testFastAck()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 0, 6);
        $consumer= new Consumer($this->stream);
        $producer= new Producer($this->stream);
        $job = $producer->addJob($queue, Job::create(['body' => '42']));
        $this->assertEquals(1, $consumer->fastAck($job));
        $this->assertEquals(0, $consumer->fastAck($job));
    }


    public function testFindJob()
    {
        $queue = 'test-'.substr(sha1(mt_rand()), 0, 6);
        $consumer = new Consumer($this->stream);
        $producer = new Producer($this->stream);
        $job      = $producer->addJob($queue, Job::create(['body' => __METHOD__]));
        $findJob  = $consumer->findJob($job->getId());
        $this->assertEquals($findJob->getBody(), $job->getBody());
        $findJob  = $consumer->findJob('DIf7198058ffab72d8692e5ece37fb0cfeecabd940023cSQ');
        $this->assertNull($findJob);
    }


    /**
     * @expectedException \Phloppy\Exception\CommandException
     * @expectedExceptionMessage BADID Invalid Job ID format.
     */
    public function testFindJobBadJobId()
    {

        $consumer = new Consumer($this->stream);
        $consumer->findJob('foo');
    }
}