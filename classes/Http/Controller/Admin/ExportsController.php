<?php

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Http\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class ExportsController extends BaseController
{
    use AdminAccessTrait;

    public function anonymousTalksExportAction(Request $req)
    {
        $this->talksExportAction(false);
    }

    public function attributedTalksExportAction(Request $req)
    {
        $this->talksExportAction(true);
    }

    public function selectedTalksExportAction(Request $req)
    {
        $this->talksExportAction(true, ['selected' => 1]);
    }

    public function emailExportAction(Request $req)
    {
        if (!$this->userHasAccess($this->app)) {
            return $this->redirectTo('login');
        }

        $mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $talks = $mapper->all();

        foreach ($talks as $talk) {
            $formatted[] = [
                'title' => $talk->title,
                'selected' => $talk->selected,
                'first_name' => $talk->speaker->first_name,
                'last_name' => $talk->speaker->last_name,
                'email' => $talk->speaker->email
            ];
        }

        $this->csvReturn($formatted, 'emailExports');
    }

    private function talksExportAction($attributed, $where = null)
    {
        if (!$this->userHasAccess($this->app)) {
            return $this->redirectTo('login');
        }

        $sort = [ "created_at" => "DESC" ];
        $admin_user_id = $this->app['sentry']->getUser()->getId();
        $mapper = $this->app['spot']->mapper('OpenCFP\Domain\Entity\Talk');
        $talks = $mapper->getAllPagerFormatted($admin_user_id, $sort, $attributed, $where);

        foreach ($talks as $talk => $info) {
            $talks[$talk]['created_at'] = $info['created_at']->format('Y-m-d H:i:s');
            unset($talks[$talk]['user'], $talks[$talk]['favourite']);

            if (!$attributed)
            {
                unset($talks[$talk]['slides'], $talks[$talk]['other'], $talks[$talk]['sponsor'], $talks[$talk]['desired']);
            }
        }

        $filename = $attributed ? ($where ? 'selectTalks' : 'attributedTalks') : 'anonymousTalks';

        $this->csvReturn($talks, $filename);
    }

    private function csvReturn($contents, $filename = 'data')
    {
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
