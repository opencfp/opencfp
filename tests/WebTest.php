<?php

use Silex\WebTestCase;

class WebTest extends WebTestCase
{
    function createApplication()
    {
        $bootstrap = new \OpenCFP\Bootstrap(array(
            'database.dsn'      => 'sqlite::memory:',
            'database.user'     => null,
            'database.password' => null,

            'twig.template_dir' => '/templates',
        ));
        $app = $bootstrap->getApp();

        // $app['debug'] = true;
        $app['exception_handler']->disable();
        $app['session.test'] = true;

        $migrator = new \OpenCFP\Migrator($app['db']);
        $migrator->migrate();
        $migrator->init();

        return $app;
    }

    function testSignUp()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/');
        $this->assertOk($client);

        $link = $crawler->filter('a:contains("Create my profile!")')->link();
        $crawler = $client->click($link);
        $this->assertOk($client);

        $form = $crawler->filter('form')->form();
        $form['email'] = 'igor@igor.io';
        $form['password'] = 'password123';
        $form['password2'] = 'password123';
        $form['first_name'] = 'Igor';
        $form['last_name'] = 'Wiedler';
        $form['company'] = 'Test Company';
        $form['twitter'] = '@name';
        $form['speaker_bio'] = 'Bla.';
        $form['speaker_info'] = 'Bleh.';

        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertOk($client);

        $this->assertContains('Account Created', $client->getResponse()->getContent());

        return $client;
    }

    /** @depends testSignUp */
    function testLogIn($client)
    {
        $crawler = $client->request('GET', '/login');
        $this->assertOk($client);

        $form = $crawler->filter('form')->form();
        $form['email'] = 'igor@igor.io';
        $form['passwd'] = 'password123';

        $crawler = $client->submit($form);

        $response = $client->getResponse();
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('/dashboard', $response->headers->get('location'));

        $crawler = $client->followRedirect();
        $this->assertOk($client);

        return $client;
    }

    /** @depends testLogIn */
    function testTalkCreate($client)
    {
        $crawler = $client->request('GET', '/talk/create');
        $this->assertOk($client);

        $form = $crawler->filter('form')->form();
        $form['title'] = 'foo talk';
        $form['description'] = 'talking about foos, bars and related concepts.';
        $form['type'] = 'regular';
        $form['category'] = 'framework';
        $form['level'] = 'entry';
        $form['slides'] = 'http://slideshare.net';
        $form['other'] = 'blah blah';
        $form['desired'] = '1';
        $form['sponsor'] = '1';

        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertOk($client);

        $response = $client->getResponse();
        $this->assertContains('foo talk', $response->getContent());
    }

    private function assertOk($client)
    {
        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
    }
}
