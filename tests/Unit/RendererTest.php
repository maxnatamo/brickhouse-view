<?php

use Brickhouse\View\Engine\Renderer;
use Brickhouse\View\Engine\ViewResolver;

describe('Renderer', function () {
    it('throws exception given invalid file path', function () {
        new Renderer(new ViewResolver("/tmp"))->renderFile("invalid-file");
    })->throws(\RuntimeException::class);
});
