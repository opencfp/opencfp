<?php
namespace OpenCFP\Markdown;

use \Aptoma\Twig\Extension\MarkdownEngineInterface;
use Ciconia\Ciconia;

class CiconiaEngine implements MarkdownEngineInterface
{
    protected $engine;

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