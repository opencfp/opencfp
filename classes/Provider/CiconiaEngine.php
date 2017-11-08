<?php

namespace OpenCFP\Provider;

use Aptoma\Twig\Extension\MarkdownEngineInterface;
use Ciconia\Ciconia;

class CiconiaEngine implements MarkdownEngineInterface
{
    /**
     * Ciconia Markdown Engine
     * @var Ciconia
     */
    protected $engine;

    /**
     * Set engine to internal property
     * @param Ciconia $engine Markdown Parser Engine
     */
    public function __construct(Ciconia $engine)
    {
        $this->engine = $engine;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($content)
    {
        return $this->engine->render($content);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Ciconia\Ciconia';
    }
}
