<?php
namespace OpenCFP\Http\Controller\Api;

use OpenCFP\Application\Speakers;
use OpenCFP\Http\Form\TalkForm;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TalksController extends \OpenCFP\Http\Controller\BaseController
{
    public function addAction(Request $req)
    {
        /**
         * This action is only accessable via POST
         */
        if (!$req->get('token')) {
            return new Response(json_encode(['msg' => 'Missing API Token'], 400));
        }

        if (!$req->get('email')) {
            return new Response(json_encode(['msg' => 'Missing speaker email']), 400);
        }

        $speaker_mapper = $this->app['spot']->mapper('\OpenCFP\Domain\Entity\User');

        $speaker = $speaker_mapper->getByEmail($req->get('email'));

        if ($speaker === false) {
            return new Response(json_encode(['msg' => 'Missing speaker']), 400);
        }

        if ($speaker->getApiToken() !== $req->get('token')) {
            return new Response(json_encode(['msg' => 'Invalid token' . $speaker->getApiToken()]), 400);
        }

        $talk_data = [
            'title' => $req->get('title'),
            'description' => $req->get('description'),
            'type' => $req->get('type'),
            'level' => $req->get('level'),
            'category' => $req->get('category'),
            'desired' => $req->get('desired'),
            'slides' => $req->get('slides'),
            'other' => $req->get('other'),
            'sponsor' => $req->get('sponsor'),
            'user_id' => $speaker->getId()
        ];

        $form = new TalkForm($talk_data, $this->app['purifier']);
        $form->sanitize();
        $isValid = $form->validateAll();

        if (!$isValid) {
            return new Response(json_encode(['msg' => implode("<br>", $form->geterrorMessages())]), 400);
        }

        $sanitized_data = $form->getCleanData();
        $data = array(
            'title' => $sanitized_data['title'],
            'description' => $sanitized_data['description'],
            'type' => $sanitized_data['type'],
            'level' => $sanitized_data['level'],
            'category' => $sanitized_data['category'],
            'desired' => $sanitized_data['desired'],
            'slides' => $sanitized_data['slides'],
            'other' => $sanitized_data['other'],
            'sponsor' => $sanitized_data['sponsor'],
            'user_id' => $sanitized_data['user_id'],
        );

        $talk_mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $talk = $talk_mapper->create($data);

        return new Response(json_encode(['msg' => 'Talk created']), 201);
    }
}
