<?php
namespace OpenCFP\Http\Markdown;

use Ciconia\Ciconia;
use Aptoma\Twig\Extension\MarkdownEngineInterface;

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
