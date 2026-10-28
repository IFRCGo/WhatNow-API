<?php

namespace App\Swagger;

use OpenApi\Analysers\AttributeAnnotationFactory;
use OpenApi\Analysers\DocBlockAnnotationFactory;
use OpenApi\Analysers\ReflectionAnalyser;

class DocBlockReflectionAnalyser extends ReflectionAnalyser
{
    public function __construct()
    {
        parent::__construct([
            new AttributeAnnotationFactory(),
            new DocBlockAnnotationFactory(),
        ]);
    }

    public static function __set_state(array $state): self
    {
        return new self();
    }
}
