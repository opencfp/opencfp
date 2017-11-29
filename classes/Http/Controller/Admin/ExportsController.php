<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Controller\Admin;

use OpenCFP\Domain\Model\Talk;
use OpenCFP\Http\Controller\BaseController;

class ExportsController extends BaseController
{
    public function anonymousTalksExportAction()
    {
        return $this->talksExportAction(false);
    }

    public function attributedTalksExportAction()
    {
        return $this->talksExportAction(true);
    }

    public function selectedTalksExportAction()
    {
        return $this->talksExportAction(true, ['selected' => 1]);
    }

    public function emailExportAction()
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

    private function talksExportAction(bool $attributed, $where = null)
    {
        $talks         = Talk::orderBy('created_at', 'DESC');
        $talks         = $where == null ? $talks : $talks->where($where);
        $talks         = $talks->get()->toArray();

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
            $this->service('session')->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'There were no talks that matched selected criteria.',
            ]);

            return $this->redirectTo('admin');
        }

        $keys   = \implode(',', \array_keys($contents[0]));
        $output = $keys . "\n";

        foreach ($contents as $content) {
            $content = \array_map([$this, 'csvFormat'], $content);
            $output  = $output . \implode(',', $content) . "\n";
        }

        return $this->export($output, $filename . '.csv');
    }
}
