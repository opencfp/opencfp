<?php

/* layouts/default.twig */
class __TwigTemplate_0de039e496ea9545710e0b3564d8caef extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!DOCTYPE html>
<html>
<head>
<title>";
        // line 4
        $this->displayBlock('title', $context, $blocks);
        echo "</title>
</head>
<body>
";
        // line 7
        $this->displayBlock('content', $context, $blocks);
        // line 9
        echo "</body>
";
    }

    // line 4
    public function block_title($context, array $blocks = array())
    {
        echo "\$site.name";
    }

    // line 7
    public function block_content($context, array $blocks = array())
    {
    }

    public function getTemplateName()
    {
        return "layouts/default.twig";
    }

    public function getDebugInfo()
    {
        return array (  45 => 7,  39 => 4,  34 => 9,  32 => 7,  26 => 4,  21 => 1,);
    }
}
