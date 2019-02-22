<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Http\Controller\BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment;

class ExportsController extends BaseController
{
    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(Twig_Environment $twig, UrlGeneratorInterface $urlGenerator, SessionInterface $session)
    {
        $this->session = $session;

        parent::__construct($twig, $urlGenerator);
    }

    public function anonymousTalksExportAction(): Response
    {
        return $this->talksExportAction(false);
    }

    public function attributedTalksExportAction(): Response
    {
        return $this->talksExportAction(true);
    }

    public function selectedTalksExportAction(): Response
    {
        return $this->talksExportAction(true, ['selected' => 1]);
    }

    public function emailExportAction(): Response
    {
        $talks     = Talk::all();
        $formatted = [];

        foreach ($talks as $talk) {
            $formatted[] = [
                'title'      => $talk->title,
                'selected'   => $talk->selected,
                'first_name' => $talk->speaker->first_name,
                'last_name'  => $talk->speaker->last_name,
                'email'      => $talk->speaker->email,
            ];
        }

        return $this->csvReturn($formatted, 'emailExports');
    }

    private function talksExportAction(bool $attributed, $where = null): Response
    {
        $talks = Talk::orderBy('created_at', 'DESC');
        $talks = $where == null ? $talks : $talks->where($where);
        $talks = $talks->get()->toArray();

        foreach ($talks as $talk => $info) {
            $talks[$talk]['created_at'] = $info['created_at'];

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
     *
     * @return string
     */
    private function csvFormat($info)
    {
        if (!\is_string($info)) {
            return $info;
        }

        if ($this->startsWith($info, '=')
                || $this->startsWith($info, '+')
                || $this->startsWith($info, '-')
                || $this->startsWith($info, '@')
            ) {
            $info = "'" . $info;
        }

        return $info;
    }

    private function startsWith($haystack, string $needle): bool
    {
        $length = \strlen($needle);

        return \substr($haystack, 0, $length) === $needle;
    }

    private function csvReturn(array $contents, string $filename = 'data')
    {
        if (\count($contents) === 0) {
            $this->session->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'There were no talks that matched selected criteria.',
            ]);

            return $this->redirectTo('admin');
        }

        \ob_start();
        $output = \fopen('php://output', 'w');

        \fputcsv($output, \array_keys($contents[0]));

        foreach ($contents as $content) {
            \fputcsv($output, \array_map([$this, 'csvFormat'], $content));
        }

        \fclose($output);

        return $this->export(\ob_get_clean(), $filename . '.csv');
    }
}
