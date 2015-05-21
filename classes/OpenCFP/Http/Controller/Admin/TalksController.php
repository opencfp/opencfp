<?php

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Http\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\View\TwitterBootstrap3View;

class TalksController extends BaseController
{
    use AdminAccessTrait;

    public function indexAction(Request $req)
    {
        if (!$this->userHasAccess($this->app)) {
            return $this->redirectTo('login');
        }

        $sort = [ "created_at" => "DESC" ];
        if ($req->get('sort') !== null) {
            switch ($req->get('sort')) {
                case "title": $sort = [ "title" => "ASC" ]; break;
                case "category": $sort = [ "category" => "ASC", "title" => "ASC" ]; break;
                case "type": $sort = [ "type" => "ASC", "category" => "ASC", "title" => "ASC" ]; break;
            }
        }

        $admin_user_id = $this->app['sentry']->getUser()->getId();
        $mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $pager_formatted_talks = $mapper->getAllPagerFormatted($admin_user_id, $sort);

        // Set up our page stuff
        $adapter = new \Pagerfanta\Adapter\ArrayAdapter($pager_formatted_talks);
        $pagerfanta = new \Pagerfanta\Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(20);
        $pagerfanta->getNbResults();

        if ($req->get('page') !== null) {
            $pagerfanta->setCurrentPage($req->get('page'));
        }

        // Create our default view for the navigation options
        $routeGenerator = function ($page) use ($req) {
            $uri = '/admin/talks?page=' . $page;
            if ($req->get('sort') !== null) {
                $uri .= '&sort=' . $req->get('sort');
            }

            return $uri;
        };
        $view = new TwitterBootstrap3View();
        $pagination = $view->render(
            $pagerfanta,
            $routeGenerator,
            array('proximity' => 3)
        );

        $templateData = array(
            'pagination' => $pagination,
            'talks' => $pagerfanta,
            'page' => $pagerfanta->getCurrentPage(),
            'current_page' => $req->getRequestUri(),
            'totalRecords' => count($pager_formatted_talks)
        );

        return $this->render('admin/talks/index.twig', $templateData);
    }

    public function viewAction(Request $req)
    {
        if (!$this->userHasAccess($this->app)) {
            return $this->redirectTo('login');
        }

        // Get info about the talks
        $talk_mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $talk_id = $req->get('id');
        $talk = $talk_mapper->get($talk_id);
        $all_talks = $talk_mapper->all()
            ->where(['user_id' => $talk->user_id])
            ->toArray();

        // Get info about our speaker
        $user_mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\User');
        $speaker = $user_mapper->get($talk->user_id)->toArray();;

        // Grab all the other talks and filter out the one we have
        $otherTalks = array_filter($all_talks, function ($talk) use ($talk_id) {
            if ((int) $talk['id'] == (int) $talk_id) {
                return false;
            }

            return true;
        });

        // Build and render the template
        $templateData = array(
            'talk' => $talk,
            'speaker' => $speaker,
            'otherTalks' => $otherTalks
        );

        return $this->render('admin/talks/view.twig', $templateData);
    }

    /**
     * Set Favorited Talk [POST]
     *
     * @param  Request $req Request Object
     * @return bool
     */
    private function favoriteAction(Request $req)
    {
        if (!$this->userHasAccess($this->app)) {
            return false;
        }

        $admin_user_id = (int) $this->app['sentry']->getUser()->getId();
        $status = true;

        if ($req->get('delete') !== null) {
            $status = false;
        }

        $mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Favorite');

        if ($status == false) {
            // Delete the record that matches
            $favorite = $mapper->first([
                'admin_user_id' => $admin_user_id,
                'talk_id' => (int) $req->get('id')
            ]);

            return $mapper->delete($favorite);
        }

        $previous_favorite = $mapper->where([
            'admin_user_id' => $admin_user_id,
            'talk_id' => (int) $req->get('id')
        ]);

        if ($previous_favorite->count() == 0) {
            $favorite = $mapper->get();
            $favorite->admin_user_id = $admin_user_id;
            $favorite->talk_id = (int) $req->get('id');

            return $mapper->insert($favorite);
        }

        return true;
    }

    /**
     * Set Selected Talk [POST]
     *
     * @param  Request $req Request Object
     * @return bool
     */
    private function selectAction(Request $req)
    {
        if (!$this->userHasAccess($this->app)) {
            return false;
        }

        $status = true;

        if ($req->get('delete') !== null) {
            $status = false;
        }

        $mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $talk = $mapper->get($req->get('id'));
        $talk->selected = $status;
        $mapper->save($talk);

        return true;
    }
}
