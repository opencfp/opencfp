<?php

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Http\Controller\BaseController;
use Spot\Locator;
use Symfony\Component\HttpFoundation\Request;

class ExportsController extends BaseController
{
    use AdminAccessTrait;

    public function anonymousTalksExportAction(Request $req)
    {
        if (!$this->userHasAccess()) {
            return $this->redirectTo('dashboard');
        }

        return $this->talksExportAction(false);
    }

    public function attributedTalksExportAction(Request $req)
    {
        if (!$this->userHasAccess()) {
            return $this->redirectTo('dashboard');
        }

        return $this->talksExportAction(true);
    }

    public function selectedTalksExportAction(Request $req)
    {
        if (!$this->userHasAccess()) {
            return $this->redirectTo('dashboard');
        }

        return $this->talksExportAction(true, ['selected' => 1]);
    }

    public function emailExportAction(Request $req)
    {
        if (!$this->userHasAccess()) {
            return $this->redirectTo('dashboard');
        }

        /* @var Locator $spot */
        $spot = $this->service('spot');

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
        $sort = [ 'created_at' => 'DESC' ];

        $admin_user_id = $this->service(Authentication::class)->userId();
        $mapper = $this->service('spot')->mapper('OpenCFP\Domain\Entity\Talk');
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

    /**
     * Adds a ' in front of items that start with =,+,- or @
     * This stops the cell from being executed as a formula in excel/google sheets etc.
     *
     * @param string $info
     * @return string
     */
    private function csvFormat($info)
    {
        if ($this->startsWith($info, '=')
                || $this->startsWith($info, '+')
                || $this->startsWith($info, '-')
                || $this->startsWith($info, '@')
            ) {
            $info = "'". $info;
        }
        return $info;
    }

    private function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    private function csvReturn($contents, $filename = 'data')
    {
        if (count($contents) === 0) {
            $this->service('session')->set('flash', [
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

        fputcsv($output, array_keys($contents[0]));

        foreach ($contents as $content) {
            $content = array_map([$this,'csvFormat'], $content);

            fputcsv($output, array_values($content));
        }

        fclose($output);
        exit();
    }
}
