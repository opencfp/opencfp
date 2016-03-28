<?php

namespace OpenCFP\Http\Controller\Admin;

use Cartalyst\Sentry\Sentry;
use OpenCFP\Http\Controller\BaseController;
use Spot\Locator;
use Symfony\Component\HttpFoundation\Request;

class ExportsController extends BaseController
{
    use AdminAccessTrait;

    public function anonymousTalksExportAction(Request $req)
    {
        return $this->talksExportAction(false);
    }

    public function attributedTalksExportAction(Request $req)
    {
        return $this->talksExportAction(true);
    }

    public function selectedTalksExportAction(Request $req)
    {
        return $this->talksExportAction(true, ['selected' => 1]);
    }

    public function emailExportAction(Request $req)
    {
        if (!$this->userHasAccess()) {
            return $this->redirectTo('login');
        }

        /* @var Locator $spot */
        $spot = $this->app['spot'];

        $mapper = $spot->mapper('OpenCFP\Domain\Entity\Talk');
        $talks = $mapper->all();

        foreach ($talks as $talk) {
            $formatted[] = [
                'title' => $talk->title,
                'selected' => $talk->selected,
                'first_name' => $talk->speaker->first_name,
                'last_name' => $talk->speaker->last_name,
                'email' => $talk->speaker->email,
            ];
        }

        return $this->csvReturn($formatted, 'emailExports');
    }

    private function talksExportAction($attributed, $where = null)
    {
        if (!$this->userHasAccess()) {
            return $this->redirectTo('login');
        }

        $sort = [ "created_at" => "DESC" ];

        /* @var Sentry $sentry */
        $sentry = $this->app['sentry'];

        $admin_user_id = $sentry->getUser()->getId();
        $mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $talks = $mapper->getAllPagerFormatted($admin_user_id, $sort, $attributed, $where);

        foreach ($talks as $talk => $info) {
            $talks[$talk]['created_at'] = $info['created_at']->format('Y-m-d H:i:s');
            unset($talks[$talk]['user'], $talks[$talk]['favourite']);

            if (!$attributed) {
                unset($talks[$talk]['slides'], $talks[$talk]['other'], $talks[$talk]['sponsor'], $talks[$talk]['desired']);
            }
        }

        $filename = $attributed ? ($where ? 'selectTalks' : 'talkList') : 'anonymousTalks';

        return $this->csvReturn($talks, $filename);
    }

    private function csvReturn($contents, $filename = 'data')
    {
        if (count($contents) === 0) {
            $this->app['session']->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'There were no talks that matched selected criteria.',
            ]);

            return $this->redirectTo('admin');
        }

        header('Content-Disposition: attachment; filename='.$filename.'.csv');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Transfer-Encoding: binary');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        fputcsv($output, (array_keys($contents[0])));

        foreach ($contents as $i => $content) {
            fputcsv($output, (array_values($content)));
        }

        fclose($output);
        exit();
    }
}
